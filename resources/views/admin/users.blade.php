@extends('layouts.admin')
@section('title', 'Akun Kasir')
@section('content')
<div class="grid xl:grid-cols-[380px_1fr] gap-5 items-start">
  <section class="card p-5">
    <h2 class="font-serif text-2xl">Buat akun kasir</h2>
    <p class="text-xs text-coffee/55 mt-2">akun owner tidak dapat dibuat dari halaman ini.</p>
    <form method="post" action="{{ route('admin.users.store') }}" class="space-y-4 mt-5">
      @csrf
      <div><label class="field-label">Nama lengkap</label><input class="field" name="nama" maxlength="100" required value="{{ old('nama') }}"></div>
      <div><label class="field-label">Username</label><input class="field" name="username" minlength="3" maxlength="50" required value="{{ old('username') }}"></div>
      <div><label class="field-label">Password awal</label><input class="field" name="password" type="password" minlength="10" required autocomplete="new-password"><p class="text-xs text-coffee/50 mt-1">minimal 10 karakter.</p></div>
      <button class="btn btn-coffee w-full">Buat akun kasir</button>
    </form>
  </section>
  <section class="card p-5">
    <h2 class="font-serif text-2xl">Akun kasir</h2>
    <div class="table-wrap mt-4"><table class="admin-table">
      <thead><tr><th>Nama / username</th><th>Status</th><th>Dibuat</th><th>Kelola</th></tr></thead>
      <tbody>
        @forelse ($users as $user)
          <tr>
            <td><b>{{ $user->nama }}</b><span class="block text-xs text-coffee/55">&#64;{{ $user->username }}</span></td>
            <td><span class="status-pill {{ $user->is_active ? 'status-green' : 'status-red' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>{{ $user->created_at->translatedFormat('d M Y') }}</td>
            <td><div class="flex flex-col gap-3">
              <form method="post" action="{{ route('admin.users.toggle', $user) }}">@csrf @method('PATCH')<button class="underline text-xs">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
              <form method="post" action="{{ route('admin.users.reset', $user) }}" class="flex flex-wrap gap-2">
                @csrf @method('PATCH')
                <input class="field !w-36 !p-2 !text-xs" type="password" name="password" minlength="10" placeholder="Password baru" required autocomplete="new-password">
                <button class="btn btn-light !p-2 !text-xs">Reset</button>
              </form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="4">belum ada akun kasir.</td></tr>
        @endforelse
      </tbody>
    </table></div>
  </section>
</div>
@endsection
