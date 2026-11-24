<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $daily = Order::query()->paid()
            ->where('created_at', '>=', today()->subDays(6))
            ->selectRaw('DATE(created_at) as d, SUM(total) as total')
            ->groupBy('d')->orderBy('d')->get()
            ->map(fn ($row) => [
                'label' => Carbon::parse($row->d)->translatedFormat('d M'),
                'total' => (float) $row->total,
            ]);

        $topMenus = OrderItem::query()
            ->selectRaw('nama_menu, SUM(qty) as qty')
            ->whereHas('order', fn ($q) => $q->notCancelled()->where('created_at', '>=', now()->subDays(30)))
            ->groupBy('nama_menu')->orderByDesc('qty')->limit(5)->get()
            ->map(fn ($row) => ['name' => $row->nama_menu, 'qty' => (int) $row->qty]);

        // Jam ramai dihitung di PHP agar tidak bergantung fungsi SQL khusus MySQL.
        $hours = Order::notCancelled()->where('created_at', '>=', now()->subDays(30))
            ->pluck('created_at')
            ->countBy(fn ($createdAt) => $createdAt->format('H'))
            ->sortDesc()->take(8);

        return view('admin.dashboard', [
            'today' => (float) Order::paid()->whereDate('created_at', today())->sum('total'),
            'week' => (float) Order::paid()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total'),
            'month' => (float) Order::paid()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            'ordersToday' => Order::whereDate('created_at', today())->count(),
            'lowStock' => Menu::where('stok', '<=', 5)->orderBy('stok')->limit(5)->get(['id', 'nama', 'stok']),
            'daily' => $daily,
            'topMenus' => $topMenus,
            'hours' => $hours,
            'logs' => ActivityLog::with('user')->latest()->limit(8)->get(),
        ]);
    }
}
