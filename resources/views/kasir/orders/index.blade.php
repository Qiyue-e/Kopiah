@extends('layouts.admin')
@section('title', 'Pesanan')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div><p class="text-sm text-coffee/60">pesanan diperbarui otomatis setiap 5 detik.</p><p class="text-xs text-coffee/45 mt-1">gunakan kontrol di bawah untuk memperbarui status dan konfirmasi pembayaran.</p></div>
  <a class="btn btn-coffee" href="{{ route('kasir.pos.create') }}">＋ Pesanan manual</a>
</div>
<div id="orderMessage" class="flash flash-ok hidden" role="status"></div>
<div id="ordersList" class="grid lg:grid-cols-2 2xl:grid-cols-3 gap-4"><div class="card p-8 text-center text-sm text-coffee/60 lg:col-span-2 2xl:col-span-3">memuat pesanan...</div></div>
@include('kasir._availability', ['menus' => $menus, 'class' => 'mt-7'])
@endsection
@push('scripts')
<script>
const listUrl = @json(route('kasir.orders.list')), list = document.getElementById('ordersList'), msg = document.getElementById('orderMessage');
let busy = false;
async function refresh(){
  const a = document.activeElement;
  if (busy || (list.contains(a) && ['SELECT','INPUT','TEXTAREA'].includes(a.tagName))) return; // jangan ganggu saat kasir memilih
  try{
    const r = await fetch(listUrl, {cache:'no-store', headers:{'X-Requested-With':'XMLHttpRequest'}});
    if (r.ok) list.innerHTML = await r.text();
  }catch(e){}
}
list.addEventListener('submit', async e => {
  const form = e.target.closest('form[data-ajax]'); if(!form) return;
  e.preventDefault(); busy = true;
  const btn = form.querySelector('button'); btn.disabled = true;
  try{
    const r = await fetch(form.action, {method:'POST', body:new FormData(form), headers:{'Accept':'application/json'}});
    const d = await r.json().catch(() => ({}));
    msg.className = 'flash ' + (r.ok ? 'flash-ok' : 'flash-error');
    msg.textContent = d.message || (r.ok ? 'selesai' : 'gagal memperbarui.');
  }catch(err){ msg.className = 'flash flash-error'; msg.textContent = 'koneksi gagal. coba lagi.'; }
  busy = false; btn.disabled = false; await refresh();
});
refresh(); setInterval(refresh, 5000);
window.addEventListener('kasir:poll', e => { if (e.detail.fresh.length) refresh(); }); // pesanan baru langsung tampil
</script>
@endpush
