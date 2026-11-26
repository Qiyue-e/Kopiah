<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Kasir;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuImageController;
use App\Http\Controllers\MomentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pelanggan (tanpa akun)
|--------------------------------------------------------------------------
*/
Route::middleware('table.qr')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/{menu}', [MenuController::class, 'show'])->name('menu.show');
});
Route::get('/tentang', [HomeController::class, 'about'])->name('about');

// Foto menu lokal disajikan melalui Laravel, tanpa ketergantungan symbolic link public/storage.
Route::get('/menu-image/{path}', [MenuImageController::class, 'show'])
    ->where('path', '.*')
    ->name('menu.image');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/{menu}', [CartController::class, 'store'])->name('cart.add');
Route::patch('/keranjang/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{key}', [CartController::class, 'destroy'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Pembayaran QRIS & halaman pesanan pelanggan. Memakai token acak pesanan (bukan kode pesanan).
Route::prefix('bayar/{order:token}')->where(['order' => '[A-Za-z0-9]{32}'])->name('payment.')->group(function () {
    Route::get('/', [PaymentController::class, 'show'])->name('show');
    Route::post('/qris', [PaymentController::class, 'start'])->middleware('throttle:20,1')->name('start');
    Route::get('/status', [PaymentController::class, 'status'])->middleware('throttle:90,1')->name('status');
});
Route::get('/pesanan/{order:token}', [PaymentController::class, 'success'])
    ->where('order', '[A-Za-z0-9]{32}')->name('order.success');
Route::get('/struk/{order:token}', [PaymentController::class, 'receipt'])
    ->where('order', '[A-Za-z0-9]{32}')->name('order.receipt');

// Webhook Xendit (tanpa CSRF & sesi; diverifikasi lewat header x-callback-token).
Route::post('/webhooks/xendit', XenditWebhookController::class)->middleware('throttle:120,1')->name('webhooks.xendit');

Route::get('/lacak', [TrackController::class, 'show'])->name('track.show');
Route::post('/lacak/{order:kode_pesanan}/ulasan', [TrackController::class, 'review'])->name('track.review');
Route::get('/lacak/{order:kode_pesanan}/status', [TrackController::class, 'status'])
    ->where('order', 'CFY[0-9]{6}[A-F0-9]{6}')->middleware('throttle:60,1')->name('track.status');

// Foto Moments disajikan oleh Laravel agar tetap dapat diakses tanpa symbolic link public/storage.
Route::get('/moments-image/{filename}', [MomentController::class, 'image'])
    ->where('filename', '[A-Za-z0-9][A-Za-z0-9._-]{0,180}')
    ->name('moments.image');

// Kopiah Moments (gaya Locket)
Route::prefix('moments')->name('moments.')->middleware('device')->group(function () {
    Route::get('/', [MomentController::class, 'index'])->name('index');
    Route::get('/baru', [MomentController::class, 'newCount'])->name('count');
    Route::post('/', [MomentController::class, 'store'])->name('store');
    Route::post('/{moment}/reaksi', [MomentController::class, 'react'])->name('react');
    Route::delete('/{moment}', [MomentController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Login & akun staf
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/admin/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

// Ganti password mandiri hanya untuk owner. Kasir tidak boleh (403); password kasir direset owner lewat menu Akun Kasir.
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/admin/password', [PasswordController::class, 'edit'])->name('staff.password.edit');
    Route::put('/admin/password', [PasswordController::class, 'update'])->name('staff.password.update');
});

/*
|--------------------------------------------------------------------------
| Panel Owner
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:owner'])->group(function () {
    Route::redirect('/', '/admin/dashboard');
    Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::resource('menus', Admin\MenuController::class)->except(['create', 'show']);
    Route::patch('menus/{menu}/toggle', [Admin\MenuController::class, 'toggle'])->name('menus.toggle');
    Route::resource('categories', Admin\CategoryController::class)->except(['create', 'show']);
    Route::resource('promos', Admin\PromoController::class)->except(['create', 'show']);
    Route::resource('tables', Admin\TableController::class)->only(['index', 'store', 'destroy']);

    Route::get('moments', [Admin\MomentController::class, 'index'])->name('moments.index');
    Route::delete('moments/{moment}', [Admin\MomentController::class, 'destroy'])->name('moments.destroy');

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
    Route::patch('users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
    Route::patch('users/{user}/password', [Admin\UserController::class, 'resetPassword'])->name('users.reset');

    Route::get('laporan', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('laporan/export', [Admin\ReportController::class, 'export'])->name('reports.export');

    Route::get('moderasi', [Admin\ModerationController::class, 'index'])->name('moderation.index');
    Route::patch('ulasan/{review}', [Admin\ModerationController::class, 'review'])->name('reviews.moderate');

    Route::get('pengaturan', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Panel Kasir (owner juga boleh masuk)
|--------------------------------------------------------------------------
*/
Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'role:kasir,owner'])->group(function () {
    Route::get('dashboard', Kasir\DashboardController::class)->name('dashboard');
    Route::get('jam-ramai', Kasir\PeakHoursController::class)->name('peak-hours');

    Route::get('pesanan', [Kasir\OrderController::class, 'index'])->name('orders.index');
    Route::get('pesanan/daftar', [Kasir\OrderController::class, 'list'])->name('orders.list');
    Route::get('pesanan/poll', [Kasir\OrderController::class, 'poll'])->name('orders.poll');
    Route::patch('pesanan/{order}/status', [Kasir\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('pesanan/{order}/pembayaran', [Kasir\OrderController::class, 'updatePayment'])->name('orders.payment');
    Route::patch('menu/{menu}/ketersediaan', [Kasir\OrderController::class, 'toggleMenu'])->name('menus.availability');

    Route::get('pos', [Kasir\PosController::class, 'create'])->name('pos.create');
    Route::post('pos', [Kasir\PosController::class, 'store'])->name('pos.store');
    Route::get('struk/{order:kode_pesanan}', Kasir\ReceiptController::class)->name('receipt');

    Route::get('kas', [Kasir\CashSessionController::class, 'index'])->name('cash.index');
    Route::post('kas/buka', [Kasir\CashSessionController::class, 'open'])->name('cash.open');
    Route::post('kas/tutup', [Kasir\CashSessionController::class, 'close'])->name('cash.close');
});
