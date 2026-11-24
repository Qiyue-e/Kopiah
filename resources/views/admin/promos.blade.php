@extends('layouts.admin')
@section('title', 'Promo')
@section('content')
<div class="grid xl:grid-cols-[380px_1fr] gap-5 items-start">
  <section class="card p-5">
    <h2 class="font-serif text-2xl">{{ $edit ? 'Edit promo' : 'Buat promo' }}</h2>
    <form method="post" action="{{ $edit ? route('admin.promos.update', $edit) : route('admin.promos.store') }}" class="space-y-4 mt-5">
      @csrf
      @if ($edit) @method('PUT') @endif
      <div><label class="field-label">Kode promo</label><input class="field uppercase" name="kode" required maxlength="40" value="{{ old('kode', $edit?->kode) }}" placeholder="NGOPI10"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="field-label">Tipe diskon</label>
          <select class="field" name="tipe">
            <option value="persen" @selected(old('tipe', $edit?->tipe ?? 'persen') === 'persen')>Persen (%)</option>
            <option value="nominal" @selected(old('tipe', $edit?->tipe) === 'nominal')>Nominal (Rp)</option>
          </select>
        </div>
        <div><label class="field-label">Nilai diskon</label><input class="field" type="number" name="nilai" min="0" step="1" required value="{{ old('nilai', $edit?->nilai ?? 10) }}"></div>
      </div>
      <div><label class="field-label">Minimal belanja (Rp)</label><input class="field" type="number" name="min_belanja" min="0" step="1000" value="{{ old('min_belanja', $edit?->min_belanja ?? 0) }}"></div>
      <div><label class="field-label">Kuota penggunaan</label><input class="field" type="number" name="kuota" min="0" value="{{ old('kuota', $edit?->kuota ?? 100) }}"><p class="text-xs text-coffee/50 mt-1">0 = tanpa batas.</p></div>
      <div><label class="field-label">Mulai (opsional)</label><input class="field" type="datetime-local" name="mulai" value="{{ old('mulai', $edit?->mulai?->format('Y-m-d\TH:i')) }}"></div>
      <div><label class="field-label">Selesai (opsional)</label><input class="field" type="datetime-local" name="selesai" value="{{ old('selesai', $edit?->selesai?->format('Y-m-d\TH:i')) }}"></div>
      <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_active" value="1" class="accent-[#8A9A5B]" @checked(old('is_active', $edit?->is_active ?? true))> Aktif</label>
      <button class="btn btn-coffee w-full">Simpan promo</button>
      @if ($edit)<a class="block text-sm text-center underline" href="{{ route('admin.promos.index') }}">Batal edit</a>@endif
    </form>
  </section>
  <section class="card p-5">
    <h2 class="font-serif text-2xl">Daftar promo</h2>
    <div class="table-wrap mt-4"><table class="admin-table">
      <thead><tr><th>Kode</th><th>Diskon</th><th>Minimal</th><th>Terpakai</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($promos as $promo)
          @php($valid = $promo->isCurrentlyValid())
          <tr>
            <td><b>{{ $promo->kode }}</b></td>
            <td>{{ $promo->tipe === 'persen' ? $promo->nilai.'%' : rupiah($promo->nilai) }}</td>
            <td>@rupiah($promo->min_belanja)</td>
            <td>{{ $promo->usages_count }}/{{ $promo->kuota ?: '∞' }}</td>
            <td class="text-xs">{{ $promo->mulai?->format('Y-m-d H:i') ?? 'Tanpa awal' }}<br>— {{ $promo->selesai?->format('Y-m-d H:i') ?? 'Tanpa akhir' }}</td>
            <td><span class="status-pill {{ $valid ? 'status-green' : 'status-red' }}">{{ $valid ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td><div class="flex flex-col gap-2">
              <a class="underline text-xs" href="{{ route('admin.promos.edit', $promo) }}">Edit</a>
              <form method="post" action="{{ route('admin.promos.destroy', $promo) }}" onsubmit="return confirm('Hapus promo ini?')">@csrf @method('DELETE')<button class="text-xs text-red-700 underline">Hapus</button></form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="7">belum ada promo.</td></tr>
        @endforelse
      </tbody>
    </table></div>
  </section>
</div>
@endsection
