<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Klien tipis untuk Xendit QRIS dinamis (QR Codes API, api-version 2022-07-31).
 * Kunci rahasia hanya dibaca dari config/env di server dan tidak pernah dikirim ke browser.
 */
class XenditService
{
    public function isConfigured(): bool
    {
        return filled(config('services.xendit.secret_key'));
    }

    /**
     * Buat QRIS dinamis dengan nominal sama persis dengan total pesanan.
     * reference_id = kode pesanan, idempotency-key tetap per pesanan sehingga percobaan ulang
     * (misalnya setelah timeout) tidak membuat QR ganda.
     *
     * @return array{id:string, qr_string:string}
     *
     * @throws RuntimeException pesan aman ditampilkan ke pelanggan
     */
    public function createQris(Order $order): array
    {
        $payload = [
            'reference_id' => $order->kode_pesanan,
            'type' => 'DYNAMIC',
            'currency' => 'IDR',
            'amount' => (int) round($order->total),
        ];

        if ($order->expires_at) {
            $payload['expires_at'] = $order->expires_at->clone()->utc()->format('Y-m-d\TH:i:s\Z');
        }

        try {
            $response = $this->client()
                ->withHeaders(['idempotency-key' => 'qris-'.$order->kode_pesanan])
                ->post('/qr_codes', $payload);
        } catch (ConnectionException $e) {
            Log::warning('Xendit: koneksi gagal saat membuat QRIS', ['order' => $order->kode_pesanan, 'error' => $e->getMessage()]);

            throw new RuntimeException('tidak dapat terhubung ke layanan pembayaran. coba lagi.');
        }

        if ($response->failed() || ! $response->json('qr_string')) {
            Log::error('Xendit: pembuatan QRIS ditolak', [
                'order' => $order->kode_pesanan,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new RuntimeException('QRIS belum bisa dibuat. coba lagi sebentar.');
        }

        return ['id' => (string) $response->json('id'), 'qr_string' => (string) $response->json('qr_string')];
    }

    /**
     * Khusus mode sandbox: simulasikan pelanggan membayar QR (memicu webhook ke aplikasi).
     * Endpoint ini hanya ada di test mode Xendit; di production akan ditolak.
     */
    public function simulatePayment(string $qrId): bool
    {
        return $this->client()->post('/qr_codes/'.$qrId.'/payments/simulate')->successful();
    }

    private function client(): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('pembayaran QRIS belum diaktifkan.');
        }

        return Http::baseUrl(rtrim((string) config('services.xendit.base_url'), '/'))
            ->withBasicAuth((string) config('services.xendit.secret_key'), '')
            ->withHeaders(['api-version' => (string) config('services.xendit.api_version')])
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }
}
