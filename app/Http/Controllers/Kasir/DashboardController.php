<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(OrderPaymentService $payments): View
    {
        $payments->expireDue();

        // Hanya pesanan yang sah: lunas, bayar di kasir, atau POS. QRIS yang belum dibayar tidak muncul.
        $today = fn () => Order::visibleToKasir()->whereDate('created_at', today());

        $latest = $today()->notCancelled()->latest()->limit(10)->get();

        return view('kasir.dashboard', [
            'today' => (float) Order::paid()->whereDate('created_at', today())->sum('total'),
            'open' => $today()->whereIn('status_pesanan', ['Diterima', 'Diproses', 'Siap'])->count(),
            'latest' => $latest,
            'knownIds' => $today()->notCancelled()->pluck('id'),
            'menus' => Menu::orderBy('nama')->get(['id', 'nama', 'stok', 'is_available']),
        ]);
    }
}
