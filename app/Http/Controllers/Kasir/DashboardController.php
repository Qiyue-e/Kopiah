<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $latest = Order::notCancelled()->whereDate('created_at', today())->latest()->limit(10)->get();

        return view('kasir.dashboard', [
            'today' => (float) Order::paid()->whereDate('created_at', today())->sum('total'),
            'open' => Order::whereDate('created_at', today())->whereIn('status_pesanan', ['Diterima', 'Diproses', 'Siap'])->count(),
            'latest' => $latest,
            'knownIds' => Order::notCancelled()->whereDate('created_at', today())->pluck('id'),
            'menus' => Menu::orderBy('nama')->get(['id', 'nama', 'stok', 'is_available']),
        ]);
    }
}
