<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PosController extends Controller
{
    public function create(): View
    {
        return view('kasir.pos', [
            'menus' => Menu::available()->orderBy('nama')->get(['id', 'nama', 'harga', 'stok']),
            'tables' => DiningTable::orderBy('nomor_meja')->get(),
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        // Baris item yang belum memilih menu diabaikan.
        $request->merge(['items' => collect($request->input('items', []))
            ->filter(fn ($row) => ! empty($row['menu_id']))->values()->all()]);

        $data = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:100'],
            'table_id' => ['nullable', 'integer', Rule::exists('tables', 'id')],
            'metode_bayar' => ['required', Rule::in(Order::METHODS)],
            'catatan' => ['nullable', 'string', 'max:800'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', Rule::exists('menus', 'id')],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:30'],
        ], ['items.required' => 'tambahkan minimal satu menu.', 'items.min' => 'tambahkan minimal satu menu.']);

        $menus = Menu::whereIn('id', collect($data['items'])->pluck('menu_id'))->get()->keyBy('id');

        // Menu yang sama dipilih dua kali digabung menjadi satu baris.
        $lines = collect($data['items'])->groupBy('menu_id')->map(function ($rows, $menuId) use ($menus) {
            $menu = $menus[(int) $menuId];

            return [
                'menu_id' => $menu->id,
                'name' => $menu->nama,
                'qty' => min(30, (int) $rows->sum('qty')),
                'price' => $menu->harga,
                'options' => [],
                'note' => '',
            ];
        })->values();

        try {
            $order = $orders->place($data, $lines, null, $request->boolean('paid'), $request->user()->id);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        ActivityLog::record('create_pos_order', 'Pesanan POS '.$order->kode_pesanan);

        return redirect()->route('kasir.receipt', $order->kode_pesanan)->with('success', 'pesanan POS berhasil dibuat.');
    }
}
