<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        $items = $this->cart->items();

        return view('cart', ['items' => $items, 'subtotal' => $items->sum('line_total')]);
    }

    public function store(Request $request, Menu $menu): RedirectResponse
    {
        if (! $menu->isOrderable()) {
            return back()->withErrors(['menu' => 'menu sedang habis.']);
        }

        $data = $request->validate([
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'integer'],
            'toppings' => ['nullable', 'array'],
            'toppings.*' => ['integer'],
            'catatan' => ['nullable', 'string', 'max:300'],
            'qty' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $optionIds = array_merge(array_values($data['options'] ?? []), $data['toppings'] ?? []);

        $this->cart->add($menu, $optionIds, (int) $data['qty'], Str::of($data['catatan'] ?? '')->squish()->toString());

        return redirect()->route('cart.index')->with('success', 'menu berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:1', 'max:20']]);
        $this->cart->update($key, (int) $data['qty']);

        return redirect()->route('cart.index');
    }

    public function destroy(string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return redirect()->route('cart.index');
    }
}
