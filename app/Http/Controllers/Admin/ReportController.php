<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to, $menuId, $cashierId] = $this->filters($request);

        $summary = $this->orderFilters(Order::query(), $from, $to, $cashierId)
            ->when($menuId, fn (Builder $q) => $q->whereHas('items', fn ($i) => $i->where('menu_id', $menuId)))
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as sum_total')
            ->first();

        return view('admin.reports', [
            'from' => $from, 'to' => $to, 'menuId' => $menuId, 'cashierId' => $cashierId,
            'rows' => $this->rows($from, $to, $menuId, $cashierId),
            'summary' => $summary,
            'menus' => Menu::orderBy('nama')->get(['id', 'nama']),
            'cashiers' => User::kasir()->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to, $menuId, $cashierId] = $this->filters($request);
        $rows = $this->rows($from, $to, $menuId, $cashierId);

        // Cegah CSV injection saat dibuka di Excel/Sheets.
        $safe = fn ($v) => preg_match('/^[=+@\-\t\r]/', (string) $v) ? "'".$v : $v;

        return response()->streamDownload(function () use ($rows, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Pesanan', 'Tanggal', 'Pelanggan', 'Menu', 'Qty', 'Harga Satuan', 'Subtotal Item', 'Status Pembayaran', 'Status Pesanan', 'Metode Bayar', 'Kasir'], ',', '"', '\\');

            foreach ($rows as $item) {
                $o = $item->order;
                fputcsv($out, array_map($safe, [
                    $o->kode_pesanan, $o->created_at->format('Y-m-d H:i:s'), $o->nama_pelanggan, $item->nama_menu,
                    $item->qty, $item->harga, $item->line_total, $o->status_bayar, $o->status_pesanan,
                    $o->metode_bayar, $o->kasir->nama ?? 'Online',
                ]), ',', '"', '\\');
            }

            fclose($out);
        }, "kopiah-laporan-{$from}-{$to}.csv", ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /** @return array{0:string,1:string,2:int,3:int} */
    private function filters(Request $request): array
    {
        $v = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'menu_id' => ['nullable', 'integer'],
            'kasir_id' => ['nullable', 'integer'],
        ]);

        return [
            $v['from'] ?? now()->startOfMonth()->toDateString(),
            $v['to'] ?? now()->toDateString(),
            (int) ($v['menu_id'] ?? 0),
            (int) ($v['kasir_id'] ?? 0),
        ];
    }

    private function orderFilters(Builder $query, string $from, string $to, int $cashierId): Builder
    {
        return $query->paid()
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when($cashierId, fn (Builder $q) => $q->where('kasir_id', $cashierId));
    }

    private function rows(string $from, string $to, int $menuId, int $cashierId): Collection
    {
        return OrderItem::query()
            ->with('order.kasir')
            ->whereHas('order', fn ($q) => $this->orderFilters($q, $from, $to, $cashierId))
            ->when($menuId, fn ($q) => $q->where('menu_id', $menuId))
            ->orderByDesc(Order::select('created_at')->whereColumn('orders.id', 'order_items.order_id'))
            ->orderBy('id')
            ->get();
    }
}
