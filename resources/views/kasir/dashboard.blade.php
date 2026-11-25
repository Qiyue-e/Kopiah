@extends('layouts.admin')
@section('title', 'Dashboard Kasir')
@section('content')
<div class="flex flex-wrap gap-3 items-center justify-between mb-5">
  <div><p class="text-sm text-coffee/60">halo, {{ auth()->user()->nama }}. semangat melayani hari ini ☕</p><p class="text-xs text-coffee/50 mt-1">dashboard memeriksa pesanan baru setiap 5 detik.</p></div>
  <button id="soundToggle" class="btn btn-light" type="button">Aktifkan suara pesanan</button>
</div>
<div class="grid grid-cols-2 xl:grid-cols-2 gap-4">
  <div class="card p-5"><p class="text-xs text-coffee/55">Omzet hari ini</p><p class="font-serif text-2xl mt-2">@rupiah($today)</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Pesanan perlu dipantau</p><p class="font-serif text-2xl mt-2" id="openCount">{{ $open }}</p></div>
</div>

<section class="card p-5 mt-5">
  <div class="flex justify-between items-center gap-3">
    <div><h2 class="font-serif text-2xl">Pesanan terbaru</h2><p class="text-xs text-coffee/55 mt-1">klik detail untuk memproses status atau pembayaran.</p></div>
    <a class="btn btn-coffee !py-2" href="{{ route('kasir.orders.index') }}">Kelola pesanan →</a>
  </div>
  <div class="table-wrap mt-3"><table class="admin-table">
    <thead><tr><th>Antrean</th><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Bayar</th><th></th></tr></thead>
    <tbody>
      @forelse ($latest as $order)
        <tr>
          <td><span class="queue-number font-bold text-2xl">{{ $order->antrean_label ?? '–' }}</span></td>
          <td class="font-semibold">{{ $order->kode_pesanan }}<span class="block text-xs text-coffee/45">{{ $order->created_at->format('H:i') }}</span></td>
          <td>{{ $order->nama_pelanggan }}</td><td>@rupiah($order->total)</td><td>{{ $order->status_pesanan }}</td><td>{{ $order->status_bayar }}</td>
          <td><a class="underline text-xs" href="{{ route('kasir.receipt', $order->kode_pesanan) }}">Struk</a></td>
        </tr>
      @empty
        <tr><td colspan="7">belum ada pesanan hari ini.</td></tr>
      @endforelse
    </tbody>
  </table></div>
</section>

@include('kasir._availability', ['menus' => $menus])
<div id="alertNote" class="fixed bottom-4 right-4 max-w-xs bg-coffee text-white rounded-2xl shadow-xl p-4 hidden" role="status"></div>
@endsection
@push('scripts')
<script>
let known = new Set(@json($knownIds)), soundOn = false, audioCtx = null;
const soundToggle = document.getElementById('soundToggle');
soundToggle.addEventListener('click', async () => {
  try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); await audioCtx.resume(); soundOn = true; soundToggle.textContent = 'Suara pesanan aktif ✓'; }
  catch (e) { alert('browser tidak mendukung suara otomatis.'); }
});
function beep(){ if(!soundOn||!audioCtx) return; const o=audioCtx.createOscillator(), g=audioCtx.createGain(); o.connect(g); g.connect(audioCtx.destination); o.frequency.value=880; g.gain.value=.08; o.start(); o.stop(audioCtx.currentTime+.18); }
async function poll(){
  try{
    const r = await fetch(@json(route('kasir.orders.poll')), {cache:'no-store', headers:{'Accept':'application/json'}});
    const d = await r.json();
    document.getElementById('openCount').textContent = d.open;
    const fresh = d.ids.filter(id => !known.has(id));
    d.ids.forEach(id => known.add(id));
    if (fresh.length) {
      beep();
      const n = document.getElementById('alertNote');
      n.textContent = fresh.length + ' pesanan baru masuk!'; n.classList.remove('hidden');
      setTimeout(() => n.classList.add('hidden'), 6000);
    }
  }catch(e){}
}
setInterval(poll, 5000);
</script>
@endpush
