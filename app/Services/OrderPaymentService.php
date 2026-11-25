<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Siklus pembayaran QRIS: membuat QR, konfirmasi lunas (idempoten), dan kedaluwarsa.
 */
class OrderPaymentService
{
    public function __construct(private XenditService $xendit) {}

    /**
     * Pastikan pesanan punya QRIS aktif. Aman dipanggil berulang (tombol "Coba lagi", refresh halaman).
     *
     * @throws RuntimeException jika pesanan tidak lagi menunggu pembayaran atau gateway gagal
     */
    public function ensureQris(Order $order): Order
    {
        $this->expireIfDue($order);
        $order->refresh();

        if (! $order->isAwaitingPayment()) {
            throw new RuntimeException('pesanan ini sudah tidak menunggu pembayaran.');
        }

        if (filled($order->qr_string)) {
            return $order;
        }

        $qr = $this->xendit->createQris($order);

        // Simpan hanya jika belum ada yang menyimpan duluan (dua tab membuka halaman bersamaan).
        Order::query()->whereKey($order->id)->whereNull('qr_string')
            ->update(['xendit_qr_id' => $qr['id'], 'qr_string' => $qr['qr_string']]);

        return $order->refresh();
    }

    /**
     * Tandai pesanan lunas dan beri nomor antrean. Idempoten: webhook yang terkirim dua kali
     * atau polling yang bersamaan hanya diproses sekali.
     *
     * @return bool true jika pesanan baru saja diubah menjadi lunas pada panggilan ini
     */
    public function markPaid(Order $order, ?string $paymentId = null): bool
    {
        return DB::transaction(function () use ($order, $paymentId) {
            $locked = Order::query()->lockForUpdate()->find($order->id);

            if (! $locked || $locked->status_bayar === 'Lunas') {
                return false;
            }

            $wasExpired = $locked->status_bayar === 'Kedaluwarsa';

            $locked->forceFill([
                'status_bayar' => 'Lunas',
                'paid_at' => now(),
                'xendit_payment_id' => $paymentId ?: $locked->xendit_payment_id,
            ]);

            if ($wasExpired) {
                // Uang sudah masuk tepat di batas waktu: aktifkan lagi pesanannya dan ambil stok bila masih ada.
                Log::warning('Pembayaran QRIS masuk setelah pesanan kedaluwarsa; pesanan diaktifkan kembali.', ['order' => $locked->kode_pesanan]);
                $locked->status_pesanan = 'Diterima';
                $this->reserveStockLeniently($locked);
            }

            $locked->save();
            $locked->assignQueueNumber();

            $order->setRawAttributes($locked->fresh()->getAttributes(), true);

            return true;
        });
    }

    /**
     * Batalkan pesanan QRIS yang lewat batas waktu: stok dan kuota promo dikembalikan.
     *
     * @return bool true jika pesanan diubah menjadi kedaluwarsa pada panggilan ini
     */
    public function expireIfDue(Order $order): bool
    {
        if ($order->status_bayar !== 'Menunggu QRIS' || ! $order->expires_at || $order->expires_at->isFuture()) {
            return false;
        }

        return DB::transaction(function () use ($order) {
            $locked = Order::query()->lockForUpdate()->with('items')->find($order->id);

            if (! $locked || $locked->status_bayar !== 'Menunggu QRIS' || ! $locked->expires_at || $locked->expires_at->isFuture()) {
                return false;
            }

            foreach ($locked->items as $item) {
                if ($item->menu_id) {
                    Menu::query()->whereKey($item->menu_id)->increment('stok', $item->qty);
                }
            }

            $locked->promoUsage()->delete();
            $locked->update(['status_bayar' => 'Kedaluwarsa', 'status_pesanan' => 'Dibatalkan']);

            return true;
        });
    }

    /** Dipanggil scheduler dan panel kasir supaya pesanan terabaikan tetap dibersihkan. */
    public function expireDue(): int
    {
        $count = 0;

        Order::query()
            ->where('status_bayar', 'Menunggu QRIS')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->limit(200)
            ->get()
            ->each(function (Order $order) use (&$count) {
                $count += $this->expireIfDue($order) ? 1 : 0;
            });

        return $count;
    }

    private function reserveStockLeniently(Order $order): void
    {
        foreach ($order->items()->get() as $item) {
            if ($item->menu_id) {
                Menu::query()->whereKey($item->menu_id)->where('stok', '>=', $item->qty)->decrement('stok', $item->qty);
            }
        }
    }
}
