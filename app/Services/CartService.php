<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;

/**
 * Keranjang berbasis session. Yang disimpan hanya id menu, id opsi, qty, dan catatan;
 * nama & harga selalu dihitung ulang dari database sehingga tidak bisa dimanipulasi.
 */
class CartService
{
    private const KEY = 'cart';

    private const MAX_QTY = 20;

    /** @return array<string, array{key:string, menu_id:int, option_ids:int[], qty:int, note:string}> */
    private function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(Menu $menu, array $optionIds, int $qty, ?string $note = null): void
    {
        // Hanya opsi milik menu ini yang diterima.
        $valid = $menu->options()
            ->whereIn('id', array_map('intval', $optionIds))
            ->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();

        $key = $menu->id.'-'.substr(sha1(json_encode($valid)), 0, 10);
        $cart = $this->raw();

        $cart[$key] = [
            'key' => $key,
            'menu_id' => $menu->id,
            'option_ids' => $valid,
            'qty' => min(self::MAX_QTY, ($cart[$key]['qty'] ?? 0) + $qty),
            'note' => filled($note) ? $note : ($cart[$key]['note'] ?? ''),
        ];

        session([self::KEY => $cart]);
    }

    public function update(string $key, int $qty): void
    {
        $cart = $this->raw();

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = max(1, min(self::MAX_QTY, $qty));
            session([self::KEY => $cart]);
        }
    }

    public function remove(string $key): void
    {
        $cart = $this->raw();
        unset($cart[$key]);
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function count(): int
    {
        return (int) collect($this->raw())->sum('qty');
    }

    /** Isi keranjang lengkap dengan harga terbaru dari database. */
    public function items(): Collection
    {
        $raw = collect($this->raw());

        if ($raw->isEmpty()) {
            return collect();
        }

        $menus = Menu::with('options')->whereIn('id', $raw->pluck('menu_id')->unique())->get()->keyBy('id');

        $items = $raw->map(function (array $row) use ($menus) {
            $menu = $menus->get($row['menu_id']);

            if (! $menu) {
                return null; // menu sudah dihapus owner
            }

            $options = $menu->options->whereIn('id', $row['option_ids'])->values();
            $price = $menu->harga + $options->sum('harga_tambahan');

            return (object) [
                'key' => $row['key'],
                'menu' => $menu,
                'name' => $menu->nama,
                'foto_url' => $menu->foto_url,
                'qty' => (int) $row['qty'],
                'note' => $row['note'] ?? '',
                'options' => $options,
                'price' => (float) $price,
                'line_total' => (float) $price * (int) $row['qty'],
            ];
        })->filter();

        if ($items->count() !== $raw->count()) {
            session([self::KEY => $raw->only($items->keys()->all())->all()]);
        }

        return $items->values();
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }
}
