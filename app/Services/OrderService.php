<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Promo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Membuat pesanan secara atomik: kunci stok & kuota promo, hitung pajak/service,
 * simpan order + item, kurangi stok. Dipakai oleh checkout pelanggan dan POS kasir.
 */
class OrderService
{
    /**
     * @param  array{nama_pelanggan:string, table_id?:int|null, metode_bayar:string, catatan?:string|null}  $customer
     * @param  Collection<int, array{menu_id:int, name:string, qty:int, price:float, options:array, note:string}>  $lines
     *
     * @throws RuntimeException jika stok/kuota tidak mencukupi (pesan aman ditampilkan ke pengguna)
     */
    public function place(array $customer, Collection $lines, ?Promo $promo = null, bool $paid = false, ?int $kasirId = null): Order
    {
        return DB::transaction(function () use ($customer, $lines, $promo, $paid, $kasirId) {
            $subtotal = (float) $lines->sum(fn (array $l) => $l['price'] * $l['qty']);
            $discount = 0.0;

            if ($promo) {
                $live = Promo::query()->withCount('usages')->lockForUpdate()->find($promo->id);

                if (! $live || ! $live->is_active || ($live->kuota > 0 && $live->usages_count >= $live->kuota)) {
                    throw new RuntimeException('kuota promo baru saja habis.');
                }

                $promo = $live;
                $discount = $live->discountFor($subtotal);
            }

            foreach ($lines as $line) {
                $menu = Menu::query()->lockForUpdate()->find($line['menu_id']);

                if (! $menu || ! $menu->is_available || $menu->stok < $line['qty']) {
                    throw new RuntimeException('stok menu '.($menu->nama ?? $line['name']).' tidak mencukupi.');
                }

                $menu->decrement('stok', $line['qty']);
            }

            $base = max(0, $subtotal - $discount);
            $charges = round($base * (float) setting('tax_percent', '0') / 100)
                + round($base * (float) setting('service_percent', '0') / 100);

            $method = $customer['metode_bayar'];
            $note = trim((string) ($customer['catatan'] ?? ''));

            if ($promo) {
                $note = trim($note.' | Promo: '.$promo->kode, ' |');
            }

            // Pesanan QRIS dari website menunggu pembayaran dengan batas waktu; pesanan POS (kasir_id terisi)
            // dikonfirmasi langsung oleh kasir sehingga tidak ikut kedaluwarsa otomatis.
            $awaitingQris = ! $paid && $method === 'QRIS' && $kasirId === null;

            $order = Order::create([
                'kode_pesanan' => Order::generateCode(),
                'expires_at' => $awaitingQris ? now()->addMinutes((int) config('services.xendit.expiry_minutes', 10)) : null,
                'paid_at' => $paid ? now() : null,
                'nama_pelanggan' => $customer['nama_pelanggan'],
                'table_id' => ($customer['table_id'] ?? null) ?: null,
                'subtotal' => $subtotal,
                'diskon' => $discount,
                'pajak' => $charges,
                'total' => $base + $charges,
                'metode_bayar' => $method,
                'status_bayar' => $paid ? 'Lunas' : ($method === 'QRIS' ? 'Menunggu QRIS' : 'Belum Bayar'),
                'status_pesanan' => 'Diterima',
                'catatan' => $note,
                'kasir_id' => $kasirId,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'menu_id' => $line['menu_id'],
                    'nama_menu' => $line['name'],
                    'qty' => $line['qty'],
                    'harga' => $line['price'],
                    'opsi_json' => $line['options'],
                    'catatan' => $line['note'] ?? '',
                ]);
            }

            if ($promo) {
                $order->promoUsage()->create(['promo_id' => $promo->id]);
            }

            // Nomor antrean dibuat saat pesanan resmi masuk ke kasir: langsung untuk "bayar di kasir" dan POS,
            // sedangkan QRIS website baru bernomor setelah pembayaran terkonfirmasi (OrderPaymentService::markPaid).
            if (! $awaitingQris) {
                $order->assignQueueNumber();
            }

            return $order;
        });
    }
}
