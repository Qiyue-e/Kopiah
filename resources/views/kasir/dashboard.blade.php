@extends('layouts.admin')
@section('title', 'Dashboard Kasir')
@section('content')
<div class="mb-5">
  <p class="text-sm text-coffee/60">halo, {{ auth()->user()->nama }}. semangat melayani hari ini ☕</p>
  <p class="text-xs text-coffee/50 mt-1">pesanan baru muncul otomatis; nyalakan suara dari tombol di pojok kanan atas.</p>
</div>
<div class="grid grid-cols-2 xl:grid-cols-2 gap-4">
  <div class="card p-5"><p class="text-xs text-coffee/55">Omzet hari ini</p><p class="font-serif text-2xl mt-2" id="todayOmzet">@rupiah($today)</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Pesanan perlu dipantau</p><p class="font-serif text-2xl mt-2" id="openCount">{{ $open }}</p></div>
</div>

<section class="card p-5 mt-5" id="peakSection" data-url="{{ route('kasir.peak-hours') }}">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <h2 class="font-serif text-2xl">Jam Ramai</h2>
      <p class="text-xs text-coffee/60 mt-1" id="peakSummary" aria-live="polite"><x-skeleton class="h-3 w-56 !block" /></p>
    </div>
    <div class="flex gap-1 rounded-xl border border-coffee/15 p-1" role="group" aria-label="Rentang waktu">
      <button type="button" class="peak-range" data-range="hari-ini" aria-pressed="true">Hari ini</button>
      <button type="button" class="peak-range" data-range="7" aria-pressed="false">7 hari</button>
      <button type="button" class="peak-range" data-range="30" aria-pressed="false">30 hari</button>
    </div>
  </div>
  <div class="relative mt-4 h-64 sm:h-72">
    <canvas id="peakChart" role="img" aria-label="Grafik batang jumlah pesanan per jam"></canvas>
    <div id="peakSkeleton" class="absolute inset-0 flex items-end gap-1" role="status" aria-label="Memuat grafik">
      @foreach ([40, 25, 15, 10, 8, 12, 30, 55, 70, 60, 80, 95, 100, 85, 65, 50, 60, 75, 90, 70, 55, 40, 30, 20] as $h)
        <div class="skeleton flex-1" style="height: {{ $h }}%"></div>
      @endforeach
    </div>
    <div id="peakEmpty" class="absolute inset-0 grid place-items-center text-sm text-coffee/60 text-center px-6" hidden>belum ada pesanan lunas pada rentang ini.</div>
    <div id="peakError" class="absolute inset-0 grid place-items-center text-center px-6" hidden>
      <div><p class="text-sm text-coffee/70" id="peakErrorText">grafik belum bisa dimuat.</p><button type="button" class="btn btn-light mt-3" id="peakRetry">Coba lagi</button></div>
    </div>
  </div>
  <p class="text-xs text-coffee/60 mt-3 peak-legend">
    <span><span class="peak-swatch" style="background:#B8651B"></span>jam tersibuk</span>
    <span><span class="peak-swatch" style="background:rgba(75,46,30,.35)"></span>jam lain</span>
    <span>hanya pesanan lunas · jam WIB</span>
  </p>
</section>

<section class="card p-5 mt-5">
  <div class="flex justify-between items-center gap-3">
    <div><h2 class="font-serif text-2xl">Pesanan terbaru</h2><p class="text-xs text-coffee/55 mt-1">klik detail untuk memproses status atau pembayaran.</p></div>
    <a class="btn btn-coffee !py-2" href="{{ route('kasir.orders.index') }}">Kelola pesanan →</a>
  </div>
  <div class="table-wrap mt-3"><table class="admin-table">
    <thead><tr><th>Antrean</th><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Bayar</th><th></th></tr></thead>
    <tbody id="latestBody">
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
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const sec = document.getElementById('peakSection');
  const url = sec.dataset.url, canvas = document.getElementById('peakChart');
  const panels = { loading: 'peakSkeleton', empty: 'peakEmpty', error: 'peakError' };
  const summary = document.getElementById('peakSummary');
  const BASE = 'rgba(75,46,30,.35)', PEAK = '#B8651B';
  const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let chart = null, range = 'hari-ini', token = 0, current = null;

  function show(state) { // state: loading | empty | error | chart
    Object.entries(panels).forEach(([k, id]) => { document.getElementById(id).hidden = k !== state; });
    canvas.classList.toggle('invisible', state !== 'chart');
  }

  function render(d) {
    current = d;
    const peak = d.peak;
    const text = d.total_orders === 0
      ? 'belum ada pesanan lunas pada rentang ini.'
      : 'jam tersibuk ' + peak.label + ':00–' + peak.label + ':59 · ' + peak.orders + ' pesanan · ' + rupiah(peak.omzet) + '. total ' + d.total_orders + ' pesanan, ' + rupiah(d.total_omzet) + '.';
    summary.textContent = text;
    canvas.setAttribute('aria-label', 'Grafik batang pesanan per jam. ' + text);
    if (d.total_orders === 0) return show('empty');

    const values = d.hours.map(h => h.orders);
    const colors = d.hours.map(h => peak && h.hour === peak.hour ? PEAK : BASE);
    if (chart) {
      chart.data.datasets[0].data = values;
      chart.data.datasets[0].backgroundColor = colors;
      chart.update();
    } else {
      chart = new Chart(canvas, {
        type: 'bar',
        data: { labels: d.hours.map(h => h.label), datasets: [{ label: 'Pesanan', data: values, backgroundColor: colors, borderRadius: 4, maxBarThickness: 28 }] },
        options: {
          responsive: true, maintainAspectRatio: false,
          animation: reduceMotion ? false : { duration: 300 },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                title: items => { const h = current.hours[items[0].dataIndex]; return h.label + ':00–' + h.label + ':59'; },
                label: ctx => ctx.parsed.y + ' pesanan',
                afterLabel: ctx => 'Omzet ' + rupiah(current.hours[ctx.dataIndex].omzet),
              },
            },
          },
          scales: {
            x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 0, callback(v, i) { return window.innerWidth < 640 && i % 3 ? '' : this.getLabelForValue(v); } } },
            y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: false } },
          },
        },
      });
    }
    show('chart');
  }

  async function load(silent) {
    const mine = ++token;
    if (typeof Chart === 'undefined') {
      document.getElementById('peakErrorText').textContent = 'grafik belum bisa dimuat. periksa koneksi internet.';
      summary.textContent = '';
      return show('error');
    }
    if (!silent) show('loading');
    try {
      const r = await fetch(url + '?rentang=' + encodeURIComponent(range), { cache: 'no-store', headers: { Accept: 'application/json' } });
      if (!r.ok) throw new Error('HTTP ' + r.status);
      const d = await r.json();
      if (mine === token) render(d);
    } catch (e) {
      if (mine !== token || silent) return; // gagal di refresh otomatis: biarkan grafik lama tampil
      document.getElementById('peakErrorText').textContent = 'grafik belum bisa dimuat.';
      summary.textContent = '';
      show('error');
    }
  }

  sec.querySelectorAll('.peak-range').forEach(btn => btn.addEventListener('click', () => {
    if (btn.dataset.range === range) return;
    range = btn.dataset.range;
    sec.querySelectorAll('.peak-range').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
    load(false);
  }));
  document.getElementById('peakRetry').addEventListener('click', () => load(false));
  window.addEventListener('kasir:poll', e => { if (e.detail.fresh.length) load(true); }); // pesanan baru -> grafik ikut diperbarui

  load(false);
})();
</script>
<script>
// Notifikasi, suara, dan polling ada di public/js/kasir-notify.js (semua halaman kasir). Di sini hanya memperbarui angka & tabel.
window.addEventListener('kasir:poll', async e => {
  const { data, fresh } = e.detail;
  const open = document.getElementById('openCount');
  if (open) open.textContent = data.open;
  if (!fresh.length) return;
  try { // pesanan baru: segarkan omzet dan tabel "Pesanan terbaru" tanpa memuat ulang halaman
    const r = await fetch(location.href, { cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!r.ok) return;
    const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
    ['latestBody', 'todayOmzet'].forEach(id => {
      const next = doc.getElementById(id), cur = document.getElementById(id);
      if (next && cur) cur.innerHTML = next.innerHTML;
    });
  } catch (err) { /* polling berikutnya akan mencoba lagi */ }
});
</script>
@endpush
