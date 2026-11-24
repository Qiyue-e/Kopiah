<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\DiningTable;
use App\Models\Promo;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function create(): View|RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
            'tables' => DiningTable::orderBy('nomor_meja')->get(),
            'selectedTable' => session('selected_table_id'),
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders): RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $data = $request->validated();
        $subtotal = (float) $items->sum('line_total');
        $promo = null;

        if (filled($data['kode_promo'] ?? null)) {
            $promo = Promo::available()->where('kode', $data['kode_promo'])->first();

            if (! $promo || $subtotal < $promo->min_belanja) {
                return back()->withInput()->withErrors([
                    'kode_promo' => 'kode promo tidak valid, sudah habis, atau belum memenuhi minimal belanja.',
                ]);
            }
        }

        try {
            $order = $orders->place($data, $this->toLines($items), $promo);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['checkout' => $e->getMessage()]);
        }

        $this->cart->clear();

        return redirect()->route('track.show', ['kode' => $order->kode_pesanan, 'baru' => 1]);
    }

    private function toLines(Collection $items): Collection
    {
        return $items->map(fn ($item) => [
            'menu_id' => $item->menu->id,
            'name' => $item->name,
            'qty' => $item->qty,
            'price' => $item->price,
            'note' => $item->note,
            'options' => $item->options->map(fn ($o) => [
                'id' => $o->id, 'nama' => $o->nama_opsi, 'tipe' => $o->tipe, 'harga' => $o->harga_tambahan,
            ])->all(),
        ]);
    }
}
