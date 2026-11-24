<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Promo;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function index(): View
    {
        return $this->page(null);
    }

    public function edit(Promo $promo): View
    {
        return $this->page($promo);
    }

    public function store(Request $request): RedirectResponse
    {
        $promo = Promo::create($this->validated($request));
        ActivityLog::record('create_promo', $promo->kode);

        return redirect()->route('admin.promos.index')->with('success', 'promo dibuat.');
    }

    public function update(Request $request, Promo $promo): RedirectResponse
    {
        $promo->update($this->validated($request, $promo));
        ActivityLog::record('update_promo', $promo->kode);

        return redirect()->route('admin.promos.index')->with('success', 'promo diperbarui.');
    }

    public function destroy(Promo $promo): RedirectResponse
    {
        try {
            $promo->delete();
        } catch (QueryException) {
            return back()->withErrors(['promo' => 'promo sudah pernah dipakai pesanan. nonaktifkan saja, jangan dihapus.']);
        }

        ActivityLog::record('delete_promo', 'ID '.$promo->id);

        return redirect()->route('admin.promos.index')->with('success', 'promo dihapus.');
    }

    private function page(?Promo $edit): View
    {
        return view('admin.promos', [
            'edit' => $edit,
            'promos' => Promo::withCount('usages')->latest()->get(),
        ]);
    }

    private function validated(Request $request, ?Promo $promo = null): array
    {
        $request->merge(['kode' => strtoupper(preg_replace('/\s+/', '', trim((string) $request->input('kode'))))]);

        $selesai = ['nullable', 'date'];
        if ($request->filled('mulai')) {
            $selesai[] = 'after_or_equal:mulai';
        }

        $data = $request->validate([
            'kode' => ['required', 'regex:/^[A-Z0-9_-]{3,40}$/', Rule::unique('promos', 'kode')->ignore($promo?->id)],
            'tipe' => ['required', Rule::in(['persen', 'nominal'])],
            'nilai' => ['required', 'numeric', 'min:0', $request->input('tipe') === 'persen' ? 'max:100' : 'max:100000000'],
            'min_belanja' => ['nullable', 'numeric', 'min:0'],
            'kuota' => ['nullable', 'integer', 'min:0'],
            'mulai' => ['nullable', 'date'],
            'selesai' => $selesai,
        ], [
            'kode.regex' => 'kode promo harus 3–40 karakter, hanya huruf, angka, minus, atau underscore.',
            'nilai.max' => 'diskon persen maksimal 100%.',
        ]);

        $data['min_belanja'] ??= 0;
        $data['kuota'] ??= 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
