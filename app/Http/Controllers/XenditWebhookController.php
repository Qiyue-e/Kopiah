<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Penerima notifikasi pembayaran dari Xendit.
 *
 * Xendit tidak memakai HMAC: setiap callback membawa token statis di header `x-callback-token`
 * yang harus sama dengan "Webhook verification token" di dashboard. Callback bisa terkirim lebih
 * dari sekali (retry), jadi pemrosesan dibuat idempoten di OrderPaymentService::markPaid.
 */
class XenditWebhookController extends Controller
{
    public function __invoke(Request $request, OrderPaymentService $payments): JsonResponse
    {
        $expected = (string) config('services.xendit.webhook_token');
        $given = (string) $request->header('x-callback-token');

        // Tanpa token terkonfigurasi, endpoint ditolak (fail closed) agar tidak bisa dipakai memalsukan pembayaran.
        if ($expected === '' || ! hash_equals($expected, $given)) {
            Log::warning('Webhook Xendit ditolak: token tidak valid.', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 401);
        }

        $payload = $request->json()->all();
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

        $status = strtoupper((string) ($data['status'] ?? ''));
        $reference = (string) ($data['reference_id'] ?? '');
        $qrId = (string) ($data['qr_id'] ?? '');
        $paymentId = (string) ($data['id'] ?? $data['payment_id'] ?? '');

        // Selain pembayaran berhasil, cukup dijawab 200 supaya Xendit tidak mengulang kiriman.
        if (! in_array($status, ['SUCCEEDED', 'COMPLETED', 'PAID'], true)) {
            return response()->json(['ok' => true, 'processed' => false]);
        }

        $order = Order::query()
            ->when($reference !== '', fn ($q) => $q->where('kode_pesanan', $reference))
            ->when($reference === '' && $qrId !== '', fn ($q) => $q->where('xendit_qr_id', $qrId))
            ->first();

        if (! $order || ($reference === '' && $qrId === '')) {
            Log::warning('Webhook Xendit: pesanan tidak ditemukan.', ['reference_id' => $reference, 'qr_id' => $qrId]);

            return response()->json(['ok' => true, 'processed' => false]);
        }

        // Nominal harus sama persis dengan total pesanan; kalau tidak, jangan tandai lunas.
        if (isset($data['amount']) && (int) round((float) $data['amount']) !== (int) round($order->total)) {
            Log::error('Webhook Xendit: nominal tidak cocok dengan pesanan.', [
                'order' => $order->kode_pesanan, 'expected' => $order->total, 'received' => $data['amount'],
            ]);

            return response()->json(['ok' => true, 'processed' => false]);
        }

        $processed = $payments->markPaid($order, $paymentId ?: null);

        return response()->json(['ok' => true, 'processed' => $processed]);
    }
}
