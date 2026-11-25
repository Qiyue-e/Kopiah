<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

/**
 * Halaman pembayaran QRIS untuk pelanggan (tanpa akun). Semua URL memakai token acak pesanan,
 * bukan kode pesanan, supaya struk dan status tidak bisa ditebak orang lain.
 */
class PaymentController extends Controller
{
    public function __construct(private OrderPaymentService $payments) {}

    /** Halaman bayar: dipakai saat refresh atau membuka ulang link pesanan. */
    public function show(Order $order): View|RedirectResponse
    {
        $this->payments->expireIfDue($order);
        $order->refresh();

        if ($this->isCompleted($order)) {
            return redirect()->route('order.success', $order->token);
        }

        return view('payment.show', ['order' => $order]);
    }

    /** Buat QRIS untuk pesanan (idempoten). Dipanggil saat halaman terbuka dan oleh tombol "Coba lagi". */
    public function start(Order $order): JsonResponse
    {
        try {
            $order = $this->payments->ensureQris($order);
        } catch (RuntimeException $e) {
            $status = $order->fresh()->status_bayar;

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'state' => $this->stateOf($order->fresh()),
                'retryable' => $status === 'Menunggu QRIS',
            ], $status === 'Menunggu QRIS' ? 502 : 409);
        }

        return response()->json([
            'ok' => true,
            'qr_string' => $order->qr_string,
            'amount' => (int) round($order->total),
            'kode' => $order->kode_pesanan,
            'seconds_left' => $this->secondsLeft($order),
        ]);
    }

    /** Dipolling browser setiap 3 detik. Sekaligus menutup pesanan yang sudah lewat batas waktu. */
    public function status(Order $order): JsonResponse
    {
        $this->payments->expireIfDue($order);
        $order->refresh();

        $state = $this->stateOf($order);

        return response()->json([
            'ok' => true,
            'state' => $state,
            'seconds_left' => $this->secondsLeft($order),
            'redirect' => $state === 'paid' ? route('order.success', $order->token) : null,
        ]);
    }

    public function success(Order $order): View|RedirectResponse
    {
        if (! $this->isCompleted($order)) {
            return $order->isAwaitingPayment()
                ? redirect()->route('payment.show', $order->token)
                : redirect()->route('menu.index');
        }

        return view('order.success', ['order' => $order->load(['items', 'meja'])]);
    }

    public function receipt(Order $order): View|RedirectResponse
    {
        if (! $this->isCompleted($order)) {
            return redirect()->route('payment.show', $order->token);
        }

        return view('receipt.show', ['order' => $order->load(['items', 'meja', 'kasir'])]);
    }

    /** Pesanan sah masuk ke kasir: sudah lunas, atau memilih bayar di kasir. */
    private function isCompleted(Order $order): bool
    {
        return $order->status_bayar === 'Lunas'
            || ($order->metode_bayar === 'Kasir' && $order->status_pesanan !== 'Dibatalkan');
    }

    private function stateOf(Order $order): string
    {
        return match (true) {
            $order->status_bayar === 'Lunas' => 'paid',
            $order->isAwaitingPayment() => 'pending',
            default => 'expired',
        };
    }

    private function secondsLeft(Order $order): int
    {
        return $order->expires_at ? max(0, (int) now()->diffInSeconds($order->expires_at, false)) : 0;
    }
}
