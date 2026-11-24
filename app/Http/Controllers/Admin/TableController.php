<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        return view('admin.tables', ['tables' => DiningTable::withCount('orders')->orderBy('nomor_meja')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['nomor_meja' => ['required', 'string', 'max:20', 'unique:tables,nomor_meja']]);

        $table = DiningTable::create(['nomor_meja' => $data['nomor_meja'], 'qr_token' => Str::random(48)]);
        ActivityLog::record('create_table', 'Meja '.$table->nomor_meja);

        return back()->with('success', 'meja berhasil ditambahkan.');
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        $table->delete();
        ActivityLog::record('delete_table', 'ID '.$table->id);

        return back()->with('success', 'meja dihapus. pesanan lama akan tetap tersimpan.');
    }
}
