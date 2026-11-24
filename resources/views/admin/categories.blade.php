@extends('layouts.admin')
@section('title', 'Kategori')
@section('content')
<div class="grid lg:grid-cols-[350px_1fr] gap-5">
  <section class="card p-5 h-fit">
    <h2 class="font-serif text-2xl">{{ $edit ? 'Edit kategori' : 'Tambah kategori' }}</h2>
    <form method="post" action="{{ $edit ? route('admin.categories.update', $edit) : route('admin.categories.store') }}" class="space-y-4 mt-5">
      @csrf
      @if ($edit) @method('PUT') @endif
      <div><label class="field-label">Nama kategori</label><input class="field" name="nama" required maxlength="60" value="{{ old('nama', $edit?->nama) }}" placeholder="Contoh: Kopi"></div>
      <div><label class="field-label">Urutan tampilan</label><input class="field" name="urutan" type="number" value="{{ old('urutan', $edit?->urutan ?? 0) }}"></div>
      <button class="btn btn-coffee w-full">Simpan kategori</button>
      @if ($edit)<a href="{{ route('admin.categories.index') }}" class="block text-center text-sm underline">Batal edit</a>@endif
    </form>
  </section>
  <section class="card p-5">
    <h2 class="font-serif text-2xl">Daftar kategori</h2>
    <div class="table-wrap mt-4"><table class="admin-table">
      <thead><tr><th>Urutan</th><th>Nama</th><th>Jumlah menu</th><th>Aksi</th></tr></thead>
      <tbody>
        @foreach ($categories as $category)
          <tr>
            <td>{{ $category->urutan }}</td><td class="font-semibold">{{ $category->nama }}</td><td>{{ $category->menus_count }}</td>
            <td><div class="flex gap-3">
              <a class="underline text-xs" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
              <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini? kategori yang masih dipakai menu tidak bisa dihapus.')">@csrf @method('DELETE')<button class="underline text-xs text-red-700">Hapus</button></form>
            </div></td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
  </section>
</div>
@endsection
