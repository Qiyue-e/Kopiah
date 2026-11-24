@extends('layouts.app')
@section('title', 'Lacak Pesanan')
@section('content')
<section class="max-w-3xl mx-auto px-4 py-10">
  <p class="section-kicker">kami siapkan dengan hati</p>
  <h1 class="font-serif text-4xl mt-2">lacak pesanan</h1>
  <form method="get" action="{{ route('track.show') }}" class="card p-4 mt-6 flex flex-col sm:flex-row gap-3">
    <input class="field flex-1 uppercase" name="kode" value="{{ $code }}" placeholder="masukkan kode pesanan, mis. CFY261026ABC123" required>
    <button class="btn btn-coffee">Lacak</button>
  </form>
  <div class="mt-4">
    @if (request()->has('baru'))<div class="flash flash-ok mb-4">pesanan kamu berhasil dibuat! simpan kode pesanan untuk mengecek status.</div>@endif
    @if (request()->has('review'))<div class="flash flash-ok mb-4">terima kasih! ulasan kamu sudah dikirim dan menunggu moderasi.</div>@endif
    <x-flash />
  </div>

  @if ($order)
    @php($steps = ['Diterima', 'Diproses', 'Siap', 'Selesai'])
    @php($idx = array_search($order->status_pesanan, $steps, true))
    <div class="card p-5 sm:p-8 mt-6">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-xs text-coffee/55">kode pesanan</p>
          <h2 class="font-bold text-2xl mt-1">{{ $order->kode_pesanan }}</h2>
          <p class="text-sm text-coffee/60 mt-1">atas nama {{ $order->nama_pelanggan }} · {{ $order->meja ? 'Meja '.$order->meja->nomor_meja : 'Take away' }}</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-coffee/55">total tagihan</p>
          <p class="font-bold text-xl mt-1">@rupiah($order->total)</p>
          <span class="status-pill" id="payment-status">{{ $order->status_bayar }}</span>
        </div>
      </div>
      <div class="my-7 border-t border-coffee/10"></div>
      <p class="section-kicker">status pesanan</p>
      <div class="grid grid-cols-4 gap-2 mt-4" id="status-steps">
        @foreach ($steps as $i => $step)
          <div class="text-center">
            <div class="rounded-full w-10 h-10 mx-auto flex items-center justify-center {{ $idx !== false && $i <= $idx ? 'bg-sage text-white' : 'bg-coffee/10 text-coffee/40' }}">{{ $i + 1 }}</div>
            <p class="text-[10px] sm:text-xs mt-2 {{ $idx !== false && $i <= $idx ? 'font-semibold' : 'text-coffee/45' }}">{{ $step }}</p>
          </div>
        @endforeach
      </div>
      <p class="text-sm text-coffee/60 mt-5" id="status-message">{{ $order->status_pesanan === 'Selesai' ? 'pesanan selesai. terima kasih sudah mampir ke Kopiah!' : 'status diperbarui otomatis setiap 5 detik.' }}</p>
      <p class="text-xs text-coffee/50 mt-2">dibuat pada {{ $order->created_at->translatedFormat('d M Y H:i') }} WIB</p>

      @if ($order->status_pesanan === 'Selesai')
        @if (! $order->review)
          <div class="border-t border-coffee/10 mt-6 pt-6">
            <h3 class="font-serif text-2xl">ceritakan pengalamanmu</h3>
            <p class="text-sm text-coffee/60 mt-2">ulasan kamu membantu kami jadi lebih baik.</p>
            <form method="post" action="{{ route('track.review', $order->kode_pesanan) }}" class="mt-4 space-y-4">
              @csrf
              <div><label class="field-label">nama</label><input class="field" name="nama" required maxlength="100" value="{{ old('nama', $order->nama_pelanggan) }}"></div>
              <div><label class="field-label">rating</label>
                <select class="field" name="rating">
                  <option value="5">★★★★★ — mantap banget</option><option value="4">★★★★☆ — puas</option><option value="3">★★★☆☆ — cukup</option>
                  <option value="2">★★☆☆☆ — kurang</option><option value="1">★☆☆☆☆ — perlu perbaikan</option>
                </select>
              </div>
              <div><label class="field-label">komentar</label><textarea class="field" name="komentar" rows="3" maxlength="1000" placeholder="apa yang paling kamu suka?">{{ old('komentar') }}</textarea></div>
              <button class="btn btn-coffee">Kirim ulasan</button>
            </form>
          </div>
        @else
          <p class="text-sm mt-5 text-sage font-semibold">ulasan untuk pesanan ini sudah tercatat.</p>
        @endif
      @endif
    </div>
  @elseif ($code !== '')
    <div class="flash flash-warn mt-6">kode pesanan belum ditemukan. pastikan kode yang dimasukkan benar.</div>
  @endif
</section>
@endsection
@if ($order)
@push('scripts')
<script>
const initialStatus = @json($order->status_pesanan);
async function refreshOrder(){
  try{
    const r = await fetch(@json(route('track.status', $order->kode_pesanan)), {cache:'no-store', headers:{'Accept':'application/json'}});
    const d = await r.json();
    if(!d.ok) return;
    document.getElementById('payment-status').textContent = d.order.status_bayar;
    const steps = ['Diterima','Diproses','Siap','Selesai'];
    const i = steps.indexOf(d.order.status_pesanan);
    document.querySelectorAll('#status-steps>div').forEach((el,n)=>{
      el.firstElementChild.className = 'rounded-full w-10 h-10 mx-auto flex items-center justify-center '+(i>=n?'bg-sage text-white':'bg-coffee/10 text-coffee/40');
      el.lastElementChild.className = 'text-[10px] sm:text-xs mt-2 '+(i>=n?'font-semibold':'text-coffee/45');
    });
    document.getElementById('status-message').textContent = d.order.status_pesanan==='Selesai' ? 'pesanan selesai. terima kasih sudah mampir ke Kopiah!' : 'status diperbarui otomatis setiap 5 detik.';
    if(initialStatus!=='Selesai' && d.order.status_pesanan==='Selesai') location.reload();
  }catch(e){}
}
setInterval(refreshOrder, 5000);
</script>
@endpush
@endif
