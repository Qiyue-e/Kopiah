@extends('layouts.admin')
@section('title', 'Menu & Stok')
@section('content')
<div class="grid xl:grid-cols-[390px_1fr] gap-5 items-start">
  <section class="card p-5">
    <h2 class="font-serif text-2xl">{{ $edit ? 'Edit menu' : 'Tambah menu baru' }}</h2>
    <p class="text-xs text-coffee/55 mt-2">foto menu boleh diunggah oleh owner. format JPG/PNG/WEBP maksimal 2 MB.</p>
    <form method="post" enctype="multipart/form-data" action="{{ $edit ? route('admin.menus.update', $edit) : route('admin.menus.store') }}" class="space-y-4 mt-5">
      @csrf
      @if ($edit) @method('PUT') @endif
      <div><label class="field-label">Nama menu</label><input class="field" name="nama" required maxlength="120" value="{{ old('nama', $edit?->nama) }}"></div>
      <div><label class="field-label">Kategori</label>
        <select class="field" name="category_id" required>
          <option value="">Pilih kategori</option>
          @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) old('category_id', $edit?->category_id) === $category->id)>{{ $category->nama }}</option>@endforeach
        </select>
      </div>
      <div><label class="field-label">Deskripsi</label><textarea class="field" name="deskripsi" rows="3">{{ old('deskripsi', $edit?->deskripsi) }}</textarea></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="field-label">Harga (Rp)</label><input class="field" type="number" name="harga" min="0" step="500" required value="{{ old('harga', $edit?->harga ?? 0) }}"></div>
        <div><label class="field-label">Stok</label><input class="field" type="number" name="stok" min="0" required value="{{ old('stok', $edit?->stok ?? 0) }}"></div>
      </div>
      <div>
        <label class="field-label">Foto menu</label>
        <input class="field" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
        <p class="text-xs text-coffee/50 mt-1">kosongkan saat edit jika ingin tetap memakai foto sebelumnya.</p>
        @if ($edit && $edit->foto)<img src="{{ $edit->foto_url }}" class="w-24 h-20 object-cover rounded-xl mt-2" alt="Foto saat ini" onerror="this.onerror=null;this.src='/assets/img/coffee-placeholder.svg';">@endif
      </div>
      <div class="flex flex-wrap gap-4">
        <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_available" value="1" class="accent-[#8A9A5B]" @checked(old('is_available', $edit?->is_available ?? true))> Tersedia</label>
        <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_best_seller" value="1" class="accent-[#8A9A5B]" @checked(old('is_best_seller', $edit?->is_best_seller ?? false))> Best seller</label>
      </div>
      <div>
        <label class="field-label">Varian &amp; topping (opsional)</label>
        <textarea class="field font-mono text-xs" name="options_text" rows="5" placeholder="Ukuran | Regular | 0&#10;Ukuran | Large | 5000&#10;Suhu | Ice | 0&#10;Topping | Extra espresso | 6000">{{ old('options_text', $optionsText) }}</textarea>
        <p class="text-xs text-coffee/50 mt-1">satu baris per pilihan dengan format: Tipe | Nama opsi | Harga tambahan. menyimpan akan mengganti daftar pilihan lama.</p>
      </div>
      <button class="btn btn-coffee w-full" type="submit">{{ $edit ? 'Simpan perubahan' : 'Tambah menu' }}</button>
      @if ($edit)<a class="block text-center text-sm underline" href="{{ route('admin.menus.index') }}">Batal edit</a>@endif
    </form>
  </section>

  <section class="card p-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div><h2 class="font-serif text-2xl">Daftar menu</h2><p class="text-xs text-coffee/55 mt-1">{{ $menus->count() }} item ditampilkan</p></div>
      <form class="flex gap-2" method="get" action="{{ route('admin.menus.index') }}"><input class="field !w-48" name="q" value="{{ $q }}" placeholder="Cari menu"><button class="btn btn-light">Cari</button></form>
    </div>
    <div class="table-wrap mt-4"><table class="admin-table">
      <thead><tr><th>Menu</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($menus as $menu)
          <tr>
            <td><div class="flex items-center gap-3 min-w-[175px]"><img class="w-12 h-12 object-cover rounded-xl" src="{{ $menu->foto_url }}" alt="" onerror="this.onerror=null;this.src='/assets/img/coffee-placeholder.svg';"><div><b>{{ $menu->nama }}</b>@if ($menu->is_best_seller)<span class="block text-[10px] text-sage">Best seller</span>@endif</div></div></td>
            <td>{{ $menu->category->nama }}</td>
            <td>@rupiah($menu->harga)</td>
            <td><span class="status-pill {{ $menu->stok <= 5 ? 'status-orange' : '' }}">{{ $menu->stok }}</span></td>
            <td><span class="status-pill {{ $menu->is_available ? 'status-green' : 'status-red' }}">{{ $menu->is_available ? 'Tersedia' : 'Habis' }}</span></td>
            <td><div class="flex flex-wrap gap-2">
              <a class="text-xs underline" href="{{ route('admin.menus.edit', $menu) }}">Edit</a>
              <form method="post" action="{{ route('admin.menus.toggle', $menu) }}">@csrf @method('PATCH')<button class="text-xs underline" type="submit">Toggle</button></form>
              <form method="post" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Hapus menu ini?')">@csrf @method('DELETE')<button class="text-xs text-red-700 underline">Hapus</button></form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="6">menu belum tersedia.</td></tr>
        @endforelse
      </tbody>
    </table></div>
  </section>
</div>
@endsection
