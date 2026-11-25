@extends('layouts.app')
@section('title', 'Pesanan diterima')
@section('content')
<section class="max-w-3xl mx-auto px-4 py-10">
  <div class="no-print card p-6 sm:p-10 text-center">
    <p class="section-kicker">{{ $order->status_bayar === 'Lunas' ? 'pembayaran diterima' : 'pesanan diterima' }}</p>
    @if ($order->antrean_label)
      <p class="text-sm text-coffee/60 mt-4">nomor antrean kamu</p>
      <p class="queue-number font-bold text-8xl sm:text-9xl mt-2" aria-label="Nomor antrean {{ $order->antrean_label }}">{{ $order->antrean_label }}</p>
    @endif
    <h1 class="font-serif text-2xl sm:text-3xl mt-6">terima kasih, {{ $order->nama_pelanggan }}</h1>
    <p class="text-sm text-coffee/60 mt-2">
      {{ $order->meja ? 'Meja '.$order->meja->nomor_meja : 'Take away' }} · @rupiah($order->total)
      @if ($order->metode_bayar === 'Kasir') · bayar di kasir @endif
    </p>
    <div class="flex flex-wrap justify-center gap-3 mt-6">
      <a class="btn btn-coffee" href="{{ route('track.show', ['kode' => $order->kode_pesanan]) }}">Lacak pesanan</a>
      <a class="btn btn-light" href="{{ route('menu.index') }}">Pesan lagi</a>
    </div>
    <p class="text-xs text-coffee/50 mt-5">simpan halaman ini: link struk bisa dibuka lagi kapan saja.</p>
  </div>

  <div class="mt-8">
    @include('receipt._viewer', ['order' => $order])
  </div>
</section>
@endsection
