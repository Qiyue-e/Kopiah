@extends('layouts.admin')
@section('title', 'Meja & QR')
@section('content')
<div class="no-print card p-5 mb-5">
  <div class="flex flex-col sm:flex-row gap-5 sm:items-center sm:justify-between">
    <div><h2 class="font-serif text-2xl">Meja &amp; QR pesanan</h2><p class="text-sm text-coffee/60 mt-1">QR membawa pelanggan ke website dan mengingat meja yang dipindai. untuk PDF, klik “Cetak QR / Simpan PDF”.</p></div>
    <form method="post" action="{{ route('admin.tables.store') }}" class="flex gap-2">@csrf<input class="field !w-36" name="nomor_meja" placeholder="Meja 11" required maxlength="20"><button class="btn btn-coffee">Tambah meja</button></form>
  </div>
  <button class="btn btn-light mt-4" onclick="window.print()" type="button">Cetak QR / Simpan PDF</button>
</div>
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" id="qrGrid">
  @foreach ($tables as $table)
    @php($url = route('home', ['token' => $table->qr_token]))
    <article class="card qr-card p-5 text-center">
      <p class="text-xs text-coffee/55 uppercase tracking-widest">Kopiah</p>
      <h2 class="font-serif text-2xl mt-1">Meja {{ $table->nomor_meja }}</h2>
      <div class="qr-code flex justify-center py-3" data-url="{{ $url }}"></div>
      <p class="text-xs text-coffee/55">scan untuk memesan</p>
      <p class="text-[10px] break-all text-coffee/40 mt-2 no-print">{{ $url }}</p>
      <form method="post" action="{{ route('admin.tables.destroy', $table) }}" class="no-print mt-3" onsubmit="return confirm('Hapus meja ini?')">@csrf @method('DELETE')<button class="text-xs text-red-700 underline">Hapus meja</button></form>
    </article>
  @endforeach
</div>
@endsection
@push('head')
<style>@media print{body{background:#fff!important}.qr-card{break-inside:avoid;box-shadow:none;border:1px solid #ddd}.qr-card .qr-code canvas,.qr-card .qr-code img{margin:auto}.qr-card{padding:8mm}.qr-card .no-print{display:none!important}#qrGrid{grid-template-columns:repeat(3,minmax(0,1fr));gap:8mm}.card{border-radius:0}}</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>document.querySelectorAll('.qr-code').forEach(el=>{if(window.QRCode)new QRCode(el,{text:el.dataset.url,width:150,height:150,colorDark:'#4B2E1E',colorLight:'#fffaf3'});else el.textContent='QR membutuhkan koneksi internet untuk dimuat.'});</script>
@endpush
