@extends('layouts.admin')
@section('title', 'Struk '.$order->kode_pesanan)
@section('content')
<div class="no-print flex flex-wrap items-center justify-between gap-3 mb-5">
  <div><p class="text-sm text-coffee/60">preview struk thermal</p><p class="text-xs text-coffee/45 mt-1">pilih lebar kertas lalu cetak atau simpan sebagai PDF.</p></div>
  <div class="flex gap-2">
    <select id="paperSize" class="field !w-32"><option value="58">Thermal 58mm</option><option value="80" selected>Thermal 80mm</option></select>
    <button class="btn btn-coffee" onclick="window.print()" type="button">Cetak struk</button>
    <a class="btn btn-light" href="{{ route('kasir.orders.index') }}">Kembali</a>
  </div>
</div>
<div class="receipt-page rounded-2xl bg-[#eee5d9] py-8">
  <article class="receipt" id="receipt">
    <div class="text-center">
      <img src="{{ site_logo() }}" alt="Logo Kopiah" class="mx-auto h-16 w-16 object-contain">
      <div class="font-serif text-2xl font-bold">{{ setting('cafe_name', 'Kopiah') }}</div>
      <p class="text-xs mt-1">{{ setting('tagline', 'Ngopi santai, momen jadi berarti') }}</p>
      <p class="text-[10px] mt-1">{{ setting('address', 'Depok, Jawa Barat') }}</p>
    </div>
    <hr>
    <div class="text-xs">
      <div class="receipt-row"><span>Kode</span><span>{{ $order->kode_pesanan }}</span></div>
      <div class="receipt-row"><span>Tanggal</span><span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
      <div class="receipt-row"><span>Pelanggan</span><span>{{ $order->nama_pelanggan }}</span></div>
      <div class="receipt-row"><span>Meja</span><span>{{ $order->meja->nomor_meja ?? 'Take away' }}</span></div>
      <div class="receipt-row"><span>Kasir</span><span>{{ $order->kasir->nama ?? 'Online' }}</span></div>
    </div>
    <hr>
    @foreach ($order->items as $item)
      <div class="receipt-item">
        <div class="text-xs font-bold">{{ $item->nama_menu }}</div>
        @if ($item->opsi_nama)<small>{{ $item->opsi_nama }}</small>@endif
        <div class="receipt-row text-xs"><span>{{ $item->qty }} × @rupiah($item->harga)</span><span>@rupiah($item->line_total)</span></div>
        @if ($item->catatan)<small>Catatan: {{ $item->catatan }}</small>@endif
      </div>
    @endforeach
    <hr>
    <div class="receipt-row text-xs"><span>Subtotal</span><span>@rupiah($order->subtotal)</span></div>
    @if ($order->diskon > 0)<div class="receipt-row text-xs"><span>Diskon</span><span>-@rupiah($order->diskon)</span></div>@endif
    @if ($order->pajak > 0)<div class="receipt-row text-xs"><span>Pajak/service</span><span>@rupiah($order->pajak)</span></div>@endif
    <div class="receipt-row font-bold text-base mt-2"><span>TOTAL</span><span>@rupiah($order->total)</span></div>
    <div class="receipt-row text-xs mt-2"><span>Metode</span><span>{{ $order->metode_bayar }}</span></div>
    <div class="receipt-row text-xs"><span>Pembayaran</span><span>{{ $order->status_bayar }}</span></div>
    <hr>
    <p class="text-center text-xs leading-5">terima kasih sudah mampir!<br>Ngopi santai, momen jadi berarti ☕</p>
    <p class="text-center text-[9px] mt-2">Simpan struk ini sebagai bukti pesanan.</p>
  </article>
</div>
@endsection
@push('scripts')
<script>
const paper = document.getElementById('paperSize'), receipt = document.getElementById('receipt');
function setPaper(){
  const mm = paper.value; receipt.style.width = mm+'mm'; receipt.style.maxWidth = mm+'mm';
  let s = document.getElementById('printPageStyle');
  if(!s){ s = document.createElement('style'); s.id = 'printPageStyle'; document.head.appendChild(s); }
  s.textContent = '@media print{@page{size:'+mm+'mm auto;margin:2mm}.receipt{width:'+mm+'mm!important;max-width:'+mm+'mm!important;margin:0 auto!important;padding:2mm!important}.receipt-page{padding:0!important;background:white!important}}';
}
paper.addEventListener('change', setPaper); setPaper();
</script>
@endpush
