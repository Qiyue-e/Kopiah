<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Menu;
use App\Support\MenuImagePath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        return $this->page($request, null);
    }

    public function edit(Request $request, Menu $menu): View
    {
        return $this->page($request, $menu->load('options'));
    }

    public function store(MenuRequest $request): RedirectResponse
    {
        $menu = DB::transaction(function () use ($request) {
            $menu = Menu::create($this->payload($request));
            $this->syncOptions($menu, (string) $request->input('options_text'));

            return $menu;
        });

        ActivityLog::record('create_menu', $menu->nama);

        return redirect()->route('admin.menus.index')->with('success', 'menu baru berhasil ditambahkan.');
    }

    public function update(MenuRequest $request, Menu $menu): RedirectResponse
    {
        DB::transaction(function () use ($request, $menu) {
            $menu->update($this->payload($request, $menu));
            $this->syncOptions($menu, (string) $request->input('options_text'));
        });

        ActivityLog::record('update_menu', $menu->nama);

        return redirect()->route('admin.menus.index')->with('success', 'menu berhasil diperbarui.');
    }

    public function toggle(Menu $menu): RedirectResponse
    {
        $menu->update(['is_available' => ! $menu->is_available]);
        ActivityLog::record('toggle_menu', 'Mengubah ketersediaan menu ID '.$menu->id);

        return back()->with('success', 'status ketersediaan menu diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->deletePhoto($menu->foto);
        $menu->delete(); // opsi ikut terhapus (cascade); riwayat pesanan tetap aman (menu_id → null)
        ActivityLog::record('delete_menu', 'Menghapus menu ID '.$menu->id);

        return redirect()->route('admin.menus.index')->with('success', 'menu berhasil dihapus.');
    }

    private function page(Request $request, ?Menu $edit): View
    {
        $q = trim((string) $request->query('q', ''));

        return view('admin.menus', [
            'edit' => $edit,
            'q' => $q,
            'categories' => Category::ordered()->get(),
            'menus' => Menu::with('category')->when($q !== '', fn ($x) => $x->where('nama', 'like', "%{$q}%"))->latest()->get(),
            'optionsText' => $edit
                ? $edit->options->map(fn ($o) => "{$o->tipe} | {$o->nama_opsi} | ".(int) $o->harga_tambahan)->implode("\n")
                : '',
        ]);
    }

    private function payload(MenuRequest $request, ?Menu $menu = null): array
    {
        $data = $request->safe()->only(['category_id', 'nama', 'deskripsi', 'harga', 'stok']);
        $data['nama'] = Str::of($data['nama'])->squish()->toString();
        $data['is_available'] = $request->boolean('is_available');
        $data['is_best_seller'] = $request->boolean('is_best_seller');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('menus', 'public');

            if ($menu) {
                $this->deletePhoto($menu->foto);
            }
        }

        return $data;
    }

    /** Format baris: "Tipe | Nama opsi | Harga tambahan". Menyimpan menggantikan daftar lama. */
    private function syncOptions(Menu $menu, string $text): void
    {
        $menu->options()->delete();

        $rows = collect(preg_split('/\r\n|\r|\n/', trim($text)))
            ->map(fn ($line) => array_map('trim', explode('|', $line)))
            ->filter(fn ($p) => count($p) >= 2 && $p[0] !== '' && $p[1] !== '')
            ->map(fn ($p) => [
                'tipe' => Str::limit($p[0], 40, ''),
                'nama_opsi' => Str::limit($p[1], 100, ''),
                'harga_tambahan' => max(0, (float) ($p[2] ?? 0)),
            ])->values()->all();

        $menu->options()->createMany($rows);
    }

    private function deletePhoto(?string $path): void
    {
        $storagePath = MenuImagePath::storagePath($path);

        if ($storagePath !== null) {
            Storage::disk('public')->delete($storagePath);
        }
    }
}
