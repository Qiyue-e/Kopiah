<?php

use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Services\XenditService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pesanan QRIS yang tidak dibayar dalam batas waktu → "Kedaluwarsa", stok & kuota promo dikembalikan.
// Jalankan scheduler: `php artisan schedule:work` (lokal) atau cron `* * * * * php artisan schedule:run` (server).
Artisan::command('orders:expire', function (OrderPaymentService $payments) {
    $this->info($payments->expireDue().' pesanan kedaluwarsa.');
})->purpose('Kedaluwarsakan pesanan QRIS yang melewati batas waktu pembayaran');

Schedule::command('orders:expire')->everyMinute()->withoutOverlapping();

// Hanya untuk sandbox Xendit: pura-pura membayar QR sebuah pesanan (memicu webhook ke aplikasi).
Artisan::command('xendit:simulate {kode : kode pesanan, contoh CFY261125ABC123}', function (XenditService $xendit, string $kode) {
    $order = Order::where('kode_pesanan', strtoupper($kode))->first();

    if (! $order || ! $order->xendit_qr_id) {
        return $this->error('pesanan tidak ditemukan atau belum punya QR.') ?? 1;
    }

    $this->info($xendit->simulatePayment($order->xendit_qr_id)
        ? 'simulasi pembayaran dikirim; tunggu webhook masuk.'
        : 'simulasi ditolak Xendit (pastikan memakai kunci sandbox).');
})->purpose('Simulasikan pembayaran QRIS di mode sandbox Xendit');
