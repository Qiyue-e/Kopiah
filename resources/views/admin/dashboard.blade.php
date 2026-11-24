@extends('layouts.admin')
@section('title', 'Dashboard Owner')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <div><p class="text-sm text-coffee/60">halo, {{ auth()->user()->nama }} 👋</p><p class="text-xs text-coffee/50 mt-1">ringkasan operasional Kopiah hari ini.</p></div>
  <div class="flex gap-2"><a class="btn btn-light" href="{{ route('admin.moments.index') }}">Kelola Moments</a><a class="btn btn-coffee" href="{{ route('admin.reports.index') }}">Lihat Laporan →</a></div>
</div>
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
  <div class="card p-5"><p class="text-xs text-coffee/55">Omzet hari ini</p><p class="font-serif text-2xl mt-2">@rupiah($today)</p><p class="text-xs text-coffee/45 mt-2">pesanan lunas</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Omzet minggu ini</p><p class="font-serif text-2xl mt-2">@rupiah($week)</p><p class="text-xs text-coffee/45 mt-2">Senin–hari ini</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Omzet bulan ini</p><p class="font-serif text-2xl mt-2">@rupiah($month)</p><p class="text-xs text-coffee/45 mt-2">{{ now()->translatedFormat('F Y') }}</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Pesanan hari ini</p><p class="font-serif text-2xl mt-2">{{ $ordersToday }}</p><p class="text-xs text-coffee/45 mt-2">transaksi tercatat hari ini</p></div>
</div>
<div class="grid xl:grid-cols-3 gap-5 mt-5">
  <div class="card p-5 xl:col-span-2">
    <h2 class="font-serif text-xl">tren omzet</h2><p class="text-xs text-coffee/55 mt-1 mb-4">7 hari terakhir, berdasarkan transaksi lunas</p>
    <canvas id="salesChart" height="110" aria-label="Grafik omzet tujuh hari" role="img"></canvas>
  </div>
  <div class="card p-5">
    <h2 class="font-serif text-xl">menu terlaris</h2><p class="text-xs text-coffee/55 mt-1">30 hari terakhir</p>
    <canvas id="topChart" height="210" class="mt-3"></canvas>
    @if ($topMenus->isEmpty())<p class="text-xs text-coffee/50 mt-3">belum ada data transaksi.</p>@endif
  </div>
</div>
<div class="grid xl:grid-cols-2 gap-5 mt-5">
  <div class="card p-5">
    <h2 class="font-serif text-xl">jam ramai</h2><p class="text-xs text-coffee/55 mt-1">jumlah pesanan per jam, 30 hari terakhir</p>
    <div class="mt-4 space-y-3">
      @php($maxHour = max(1, (int) $hours->max()))
      @forelse ($hours as $hour => $count)
        <div>
          <div class="flex justify-between text-xs mb-1"><span>{{ $hour }}.00 WIB</span><b>{{ $count }} pesanan</b></div>
          <div class="h-2 bg-coffee/10 rounded-full overflow-hidden"><div class="h-full bg-sage rounded-full" style="width:{{ round($count / $maxHour * 100) }}%"></div></div>
        </div>
      @empty
        <p class="text-sm text-coffee/50">belum ada data.</p>
      @endforelse
    </div>
  </div>
  <div class="card p-5">
    <h2 class="font-serif text-xl">stok menipis</h2><p class="text-xs text-coffee/55 mt-1">menu dengan stok 5 atau kurang</p>
    <div class="mt-3 space-y-2">
      @forelse ($lowStock as $menu)
        <div class="flex items-center justify-between py-2 border-b border-coffee/10"><span class="text-sm">{{ $menu->nama }}</span><span class="status-pill {{ $menu->stok === 0 ? 'status-red' : 'status-orange' }}">stok {{ $menu->stok }}</span></div>
      @empty
        <p class="text-sm text-coffee/50 mt-3">semua stok aman.</p>
      @endforelse
    </div>
    <a href="{{ route('admin.menus.index') }}" class="text-sm underline inline-block mt-4">Kelola menu &amp; stok</a>
  </div>
</div>
<div class="card p-5 mt-5">
  <div class="flex justify-between items-center gap-4"><h2 class="font-serif text-xl">log aktivitas terakhir</h2><a class="text-sm underline" href="{{ route('admin.settings.edit') }}#logs">lihat pengaturan/log</a></div>
  <div class="table-wrap mt-3"><table class="admin-table">
    <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Detail</th></tr></thead>
    <tbody>
      @forelse ($logs as $log)
        <tr><td class="whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td><td>{{ $log->user->nama ?? 'Sistem' }}</td><td>{{ $log->aksi }}</td><td>{{ $log->detail }}</td></tr>
      @empty
        <tr><td colspan="4">belum ada aktivitas.</td></tr>
      @endforelse
    </tbody>
  </table></div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const sales = @json($daily);
new Chart(document.getElementById('salesChart'),{type:'line',data:{labels:sales.map(x=>x.label),datasets:[{label:'Omzet',data:sales.map(x=>x.total),fill:true,tension:.35,borderWidth:2}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'Rp'+Number(v).toLocaleString('id-ID')}}}}});
const top = @json($topMenus);
new Chart(document.getElementById('topChart'),{type:'bar',data:{labels:top.map(x=>x.name),datasets:[{label:'Terjual',data:top.map(x=>x.qty),borderRadius:8}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}});
</script>
@endpush
