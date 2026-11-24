<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return $this->page(null);
    }

    public function edit(Category $category): View
    {
        return $this->page($category);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($this->validated($request));
        ActivityLog::record('create_category', $category->nama);

        return redirect()->route('admin.categories.index')->with('success', 'kategori ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));
        ActivityLog::record('update_category', $category->nama);

        return redirect()->route('admin.categories.index')->with('success', 'kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $category->delete();
        } catch (QueryException) {
            return back()->withErrors(['kategori' => 'kategori tidak bisa dihapus karena masih digunakan menu.']);
        }

        ActivityLog::record('delete_category', 'ID '.$category->id);

        return redirect()->route('admin.categories.index')->with('success', 'kategori dihapus.');
    }

    private function page(?Category $edit): View
    {
        return view('admin.categories', [
            'edit' => $edit,
            'categories' => Category::withCount('menus')->ordered()->get(),
        ]);
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:60', Rule::unique('categories', 'nama')->ignore($category?->id)],
            'urutan' => ['nullable', 'integer'],
        ]) + ['urutan' => 0];
    }
}
