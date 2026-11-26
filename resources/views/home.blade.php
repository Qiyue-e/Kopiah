@extends('layouts.app')
@section('title', 'Pesan dari meja, bayar QRIS')
@section('content')
@php
  $sample = $featured->take(2);
  $sampleTotal = $sample->sum('harga');
@endphp
<section class="lp-hero">
  <div class="max-w-7xl mx-auto px-6 py-16 lg:py-24 grid lg:grid-cols-12 gap-12 items-center">
    <div class="lg:col-span-6">
      <h1 class="lp-display">Pesan dari meja, bayar lewat QRIS, tunggu nomor antreanmu</h1>
      <p class="lp-lead mt-6 max-w-lg" data-reveal>Pilih menu Kopiah dari ponsel, bayar dengan QRIS, lalu lihat status pesananmu tanpa antre di kasir. Foto bersama teman ada di Kopiah Moments.</p>
      <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3" data-reveal>
        <a class="btn btn-coffee" href="{{ route('menu.index') }}">Lihat menu <x-icon name="arrow-right" /></a>
        <a class="lp-link" href="{{ route('moments.index') }}">Buka Kopiah Moments</a>
      </div>
    </div>
    <div class="lg:col-span-6 lg:pl-8">
      <figure class="lp-receipt" data-reveal aria-label="Contoh tampilan struk pesanan">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="lp-meta">Nomor antrean</p>
            <p class="lp-queue mt-1">007</p>
          </div>
          <p class="lp-meta text-right">Pesanan diterima<br>Bayar QRIS</p>
        </div>
        <div class="lp-rule"></div>
        <ul class="space-y-3">
          @forelse ($sample as $item)
            <li class="flex items-baseline justify-between gap-4 text-sm"><span>1× {{ $item->nama }}</span><span class="lp-num">@rupiah($item->harga)</span></li>
          @empty
            <li class="text-sm text-coffee/60">Menu akan tampil di sini setelah ditambahkan.</li>
          @endforelse
        </ul>
        <div class="lp-rule"></div>
        <div class="flex items-baseline justify-between gap-4"><span class="text-sm">Total</span><span class="lp-num font-medium">@rupiah($sampleTotal)</span></div>
        <figcaption class="lp-meta mt-6">Contoh tampilan struk digital</figcaption>
      </figure>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 lg:py-24">
  <x-flash />
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div><h2 class="lp-h2">Menu untuk dicoba</h2><p class="text-sm text-coffee/70 mt-2">Empat menu untuk memulai. Daftar lengkap ada di halaman menu.</p></div>
    <a class="lp-link" href="{{ route('menu.index') }}">Lihat semua menu <x-icon name="arrow-right" /></a>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6" data-stagger>
    @foreach ($featured as $menu)<x-menu-card :menu="$menu" />@endforeach
  </div>
</section>

@if ($moments->isNotEmpty())
<section class="max-w-7xl mx-auto px-6 pb-16 lg:pb-24">
  <div class="flex items-end justify-between gap-4 mb-8">
    <div><h2 class="lp-h2">Moments terbaru</h2><p class="text-sm text-coffee/70 mt-2">Foto dari pengunjung Kopiah.</p></div>
    <a class="lp-link" href="{{ route('moments.index') }}"><x-icon name="camera" /> Buka kamera</a>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4" data-stagger>
    @foreach ($moments as $moment)
      <a href="{{ route('moments.index') }}#moment-{{ $moment->id }}" class="lk-card block">
        <div class="lk-photo-wrap"><img class="lk-photo" src="{{ $moment->url }}" alt="Momen Kopiah" loading="lazy">@if ($moment->caption)<span class="lk-caption">{{ $moment->caption }}</span>@endif</div>
      </a>
    @endforeach
  </div>
</section>
@endif

@if ($promos->isNotEmpty())
<section class="lp-band py-16"><div class="max-w-7xl mx-auto px-6">
  <h2 class="lp-h2 mb-8">Promo aktif</h2>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" data-stagger>
    @foreach ($promos as $promo)
      <div class="lp-promo">
        <div class="min-w-0">
          <p class="lp-meta">Kode promo</p>
          <p class="lp-code mt-1">{{ $promo->kode }}</p>
          <p class="text-sm text-coffee/70 mt-2">Diskon {{ $promo->tipe === 'persen' ? rtrim(rtrim(number_format($promo->nilai, 2, '.', ''), '0'), '.').'%' : rupiah($promo->nilai) }}, minimal belanja @rupiah($promo->min_belanja)</p>
        </div>
        <button type="button" class="btn btn-light !px-4 !py-2" data-copy="{{ $promo->kode }}"><x-icon name="copy" /> Salin</button>
      </div>
    @endforeach
  </div>
</div></section>
@endif

<section class="max-w-7xl mx-auto px-6 py-16 lg:py-24 grid lg:grid-cols-12 gap-12 items-start">
  <div class="lg:col-span-6">
    <h2 class="lp-h2">Tentang Kopiah</h2>
    <p class="text-coffee/70 leading-7 mt-5 max-w-lg">Kopiah adalah kafe untuk bekerja, bertemu teman, atau duduk sendirian. Pesan dari meja lewat ponsel, atau bayar langsung di kasir.</p>
    <a class="lp-link mt-6 inline-flex" href="{{ route('about') }}">Baca selengkapnya <x-icon name="arrow-right" /></a>
  </div>
  <dl class="lg:col-span-6 lp-facts">
    <div><dt><x-icon name="clock" /> Jam buka</dt><dd>{{ setting('opening_hours', 'Setiap hari, 08.00 - 22.00 WIB') }}</dd></div>
    <div><dt><x-icon name="map-pin" /> Alamat</dt><dd>{{ setting('address', 'Jl. Contoh No. 10, Depok, Jawa Barat') }}</dd></div>
    <div><dt><x-icon name="qr-code" /> Pembayaran</dt><dd>QRIS dari ponsel, atau bayar di kasir</dd></div>
  </dl>
</section>

<section class="lp-band py-16"><div class="max-w-7xl mx-auto px-6">
  <div class="flex items-end justify-between gap-4 mb-8"><h2 class="lp-h2">Ulasan pelanggan</h2></div>
  <div class="grid md:grid-cols-3 gap-4" data-stagger>
    @forelse ($reviews as $review)
      <article class="card p-6">
        <div class="rating mb-3 flex gap-0.5" role="img" aria-label="Nilai {{ $review->rating }} dari 5">@for ($i = 1; $i <= 5; $i++)<x-icon name="star" :filled="$i <= $review->rating" class="w-4 h-4 {{ $i <= $review->rating ? '' : 'opacity-30' }}" />@endfor</div>
        @if ($review->komentar)<p class="text-sm leading-6">“{{ $review->komentar }}”</p>@endif
        <p class="font-medium mt-4 text-sm">{{ $review->nama }}</p>
      </article>
    @empty
      <article class="card p-6 md:col-span-3"><p class="text-sm text-coffee/70">Ulasan akan tampil di sini setelah disetujui.</p></article>
    @endforelse
  </div>
</div></section>

<section class="max-w-7xl mx-auto px-6 py-16 lg:py-24"><div class="card overflow-hidden grid md:grid-cols-2">
  <div class="p-8 sm:p-12">
    <h2 class="lp-h2">Lokasi dan kontak</h2>
    <p class="text-sm text-coffee/70 leading-6 mt-4">{{ setting('address', 'Jl. Contoh No. 10, Depok, Jawa Barat') }}</p>
    <p class="text-sm text-coffee/70 mt-2 flex items-center gap-2"><x-icon name="clock" /> {{ setting('opening_hours', 'Setiap hari, 08.00 - 22.00 WIB') }}</p>
    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mt-8">
      <a class="btn btn-coffee" href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp', '6281234567890')) }}" target="_blank" rel="noopener"><x-icon name="message-circle" /> Chat WhatsApp</a>
      <a class="lp-link" href="{{ route('about') }}">Info lengkap</a>
    </div>
  </div>
  <div class="min-h-[280px]"><iframe title="Lokasi Kopiah" class="w-full h-full min-h-[280px] border-0" loading="lazy" src="{{ setting('google_maps_embed', 'https://www.google.com/maps?q=Depok%20Jawa%20Barat&output=embed') }}" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
</div></section>
@endsection
@push('scripts')
<script>document.querySelectorAll('[data-copy]').forEach(b=>b.addEventListener('click',async()=>{const html=b.innerHTML;try{await navigator.clipboard.writeText(b.dataset.copy);b.textContent='Tersalin';setTimeout(()=>{b.innerHTML=html},1800)}catch(e){prompt('Salin kode promo ini:',b.dataset.copy)}}));</script>
@endpush
