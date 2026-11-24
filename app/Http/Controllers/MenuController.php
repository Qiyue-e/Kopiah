<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = (int) $request->query('kategori', 0);
        $q = trim((string) $request->query('q', ''));

        $menus = Menu::with('category')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->search($q)
            ->orderByDesc('is_best_seller')
            ->latest()
            ->get();

        return view('menu.index', [
            'categories' => Category::ordered()->get(),
            'menus' => $menus,
            'categoryId' => $categoryId,
            'q' => $q,
        ]);
    }

    public function show(Menu $menu): View
    {
        $menu->load('category', 'options');

        return view('menu.show', ['menu' => $menu, 'groups' => $menu->options->groupBy('tipe')]);
    }
}
