@extends('layouts.admin')
@section('title', 'Kas Harian')
@section('content')
<div class="grid xl:grid-cols-2 gap-5 items-start">
  <section class="card p-5 sm:p-7">
    <p class="section-kicker">operasional harian</p>
    <h2 class="font-serif text-2xl mt-2">status kasir</h2>
    @if (! $active)
      <p class="text-sm text-coffee/60 mt-2">belum ada sesi kasir terbuka. isi modal awal untuk memulai shift.</p>
      <form method="post" action="{{ route('kasir.cash.open') }}" class="space-y-4 mt-5">
        @csrf
        <div><label class="field-label">Modal awal kas (Rp)</label><input class="field" type="number" name="modal_awal" min="0" step="1000" value="{{ old('modal_awal', 0) }}" required></div>
        <button class="btn btn-coffee">Buka kasir</button>
      </form>
    @else
      <span class="status-pill status-green mt-3">Sesi sedang berjalan</span>
      <div class="grid grid-cols-2 gap-3 mt-5">
        <div class="rounded-2xl bg-white p-4"><p class="text-xs text-coffee/55">Modal awal</p><b class="text-xl block mt-2">@rupiah($active->modal_awal)</b></div>
        <div class="rounded-2xl bg-white p-4"><p class="text-xs text-coffee/55">Penjualan tunai lunas</p><b class="text-xl block mt-2">@rupiah($sales)</b><small class="text-xs text-coffee/50">{{ $count }} transaksi</small></div>
        <div class="rounded-2xl bg-white p-4 col-span-2"><p class="text-xs text-coffee/55">Perkiraan uang kas</p><b class="font-serif text-3xl block mt-2">@rupiah($expected)</b></div>
      </div>
      <form method="post" action="{{ route('kasir.cash.close') }}" class="space-y-4 mt-6" onsubmit="return confirm('Yakin ingin menutup sesi kasir?')">
        @csrf
        <div><label class="field-label">Uang kas aktual saat tutup (Rp)</label><input class="field" type="number" min="0" step="1000" name="kas_akhir" required></div>
        <div><label class="field-label">Catatan shift (opsional)</label><textarea class="field" name="catatan" rows="2" maxlength="500"></textarea></div>
        <button class="btn btn-caramel">Tutup kasir</button>
      </form>
    @endif
  </section>
  <section class="card p-5">
    <h2 class="font-serif text-2xl">Riwayat sesi kas</h2>
    <div class="table-wrap mt-4"><table class="admin-table">
      <thead><tr><th>Buka</th><th>Modal</th><th>Kas akhir</th><th>Status</th></tr></thead>
      <tbody>
        @forelse ($history as $session)
          <tr>
            <td>{{ $session->dibuka_pada->format('d/m H:i') }}@if ($session->ditutup_pada)<span class="block text-xs text-coffee/50">tutup {{ $session->ditutup_pada->format('d/m H:i') }}</span>@endif</td>
            <td>@rupiah($session->modal_awal)</td>
            <td>{{ $session->kas_akhir !== null ? rupiah($session->kas_akhir) : '—' }}</td>
            <td><span class="status-pill {{ $session->ditutup_pada ? 'status-green' : 'status-orange' }}">{{ $session->ditutup_pada ? 'Tutup' : 'Terbuka' }}</span></td>
          </tr>
        @empty
          <tr><td colspan="4">belum ada riwayat kas.</td></tr>
        @endforelse
      </tbody>
    </table></div>
    <p class="text-xs text-coffee/50 mt-4">perkiraan kas menghitung modal awal + transaksi berstatus lunas dengan metode “Bayar di kasir”. QRIS di luar hitungan uang tunai.</p>
  </section>
</div>
@endsection
