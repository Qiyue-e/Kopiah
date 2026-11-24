@extends('layouts.app')
@section('title', 'Ngopi santai, momen jadi berarti')
@section('content')
<section class="hero-bg text-white">
  <div class="max-w-7xl mx-auto px-5 py-20 sm:py-28 lg:py-36">
    <div class="max-w-2xl">
      <span class="inline-flex items-center gap-2 bg-white/15 rounded-full px-4 py-2 text-xs mb-6"><span class="pulse-dot"></span> tempat kecil untuk jeda yang berarti</span>
      <h1 class="hero-title font-serif text-5xl sm:text-6xl lg:text-7xl">ngopi santai,<br><span class="text-[#E8B46B] italic">momen jadi berarti.</span></h1>
      <p class="mt-6 max-w-lg text-white/80 leading-7">temukan kopi favorit, cerita baru, dan ruang nyaman buat kamu berhenti sejenak dari ramainya hari.</p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a class="btn btn-caramel" href="{{ route('menu.index') }}">Jelajahi Menu <span>↗</span></a>
        <a class="btn border border-white/40 text-white bg-white/10" href="{{ route('moments.index') }}">Lihat Moments</a>
      </div>
      <div class="mt-10 text-sm text-white/70 flex flex-wrap gap-x-5 gap-y-2"><span>☕ dibuat dengan hati</span><span>✦ suasana hangat</span><span>♡ ruang untuk cerita</span></div>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-14 sm:py-20">
  <x-flash />
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
    <div><p class="section-kicker">favorit pelanggan</p><h2 class="font-serif text-3xl sm:text-4xl mt-2">yang sering dirindukan</h2><p class="text-sm text-coffee/65 mt-2">pilihan teman ngopi yang selalu punya cerita.</p></div>
    <a class="font-semibold text-sm hover:text-caramel" href="{{ route('menu.index') }}">lihat semua menu →</a>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    @foreach ($featured as $menu)<x-menu-card :menu="$menu" />@endforeach
  </div>
</section>

@if ($moments->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 pb-6">
  <div class="flex items-end justify-between gap-4 mb-5">
    <div><p class="section-kicker">kopiah moments</p><h2 class="font-serif text-3xl mt-2">momen terbaru dari teman Kopiah</h2></div>
    <a class="text-sm font-semibold" href="{{ route('moments.index') }}">buka kamera →</a>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach ($moments as $moment)
      <a href="{{ route('moments.index') }}#moment-{{ $moment->id }}" class="lk-card block">
        <div class="lk-photo-wrap"><img class="lk-photo" src="{{ $moment->url }}" alt="Momen Kopiah" loading="lazy">@if ($moment->caption)<span class="lk-caption">{{ $moment->caption }}</span>@endif</div>
      </a>
    @endforeach
  </div>
</section>
@endif

@if ($promos->isNotEmpty())
<section class="bg-[#EDE0CE] py-12 mt-10"><div class="max-w-7xl mx-auto px-4">
  <div class="mb-6"><p class="section-kicker">sedikit kejutan</p><h2 class="font-serif text-3xl mt-2">promo buat hari kamu</h2></div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($promos as $promo)
      <div class="rounded-3xl p-6 bg-white/75 border border-white flex items-center justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-widest text-coffee/55">kode promo</p>
          <p class="text-2xl font-bold mt-1">{{ $promo->kode }}</p>
          <p class="text-sm mt-2">diskon {{ $promo->tipe === 'persen' ? rtrim(rtrim(number_format($promo->nilai, 2, '.', ''), '0'), '.').'%' : rupiah($promo->nilai) }} · min. belanja @rupiah($promo->min_belanja)</p>
        </div>
        <button type="button" class="btn btn-light !px-3" data-copy="{{ $promo->kode }}">Salin</button>
      </div>
    @endforeach
  </div>
</div></section>
@endif

<section class="max-w-7xl mx-auto px-4 py-14 sm:py-20 grid lg:grid-cols-2 gap-8 items-center">
  <div class="rounded-[28px] overflow-hidden min-h-[300px] bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1445116572660-236099ec97a0?auto=format&fit=crop&w=1200&q=85')"></div>
  <div class="lg:pl-5">
    <p class="section-kicker">lebih dari sekadar kopi</p>
    <h2 class="font-serif text-4xl mt-3 leading-tight">tempat cerita tumbuh<br>di antara cangkir.</h2>
    <p class="text-coffee/70 leading-7 mt-5">di Kopiah, setiap tegukan punya ruang. datang buat bekerja, bertemu teman, atau sekadar menemani diri sendiri. kami siapkan suasana hangat dan menu yang dibuat dengan sepenuh hati.</p>
    <div class="grid grid-cols-2 gap-4 my-7">
      <div class="border-l-2 border-sage pl-4"><p class="font-serif text-2xl">08.00–22.00</p><p class="text-xs text-coffee/60 mt-1">jam operasional</p></div>
      <div class="border-l-2 border-sage pl-4"><p class="font-serif text-2xl">made with care</p><p class="text-xs text-coffee/60 mt-1">setiap hari</p></div>
    </div>
    <a class="btn btn-coffee" href="{{ route('about') }}">kenalan dengan Kopiah →</a>
  </div>
</section>

<section class="bg-white/50 py-12"><div class="max-w-7xl mx-auto px-4">
  <div class="flex items-end justify-between gap-4 mb-6"><div><p class="section-kicker">cerita dari mereka</p><h2 class="font-serif text-3xl mt-2">kata pelanggan</h2></div><a class="text-sm font-semibold" href="{{ route('moments.index') }}">lihat moments →</a></div>
  <div class="grid md:grid-cols-3 gap-4">
    @forelse ($reviews as $review)
      <article class="card p-5">
        <div class="rating mb-3">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
        <p class="text-sm leading-6">“{{ $review->komentar ?: 'suasana nyaman dan kopinya enak!' }}”</p>
        <p class="font-semibold mt-4 text-sm">{{ $review->nama }}</p>
      </article>
    @empty
      <article class="card p-5 md:col-span-3"><p class="text-sm text-coffee/60">cerita pelanggan akan tampil di sini setelah ada ulasan yang disetujui.</p></article>
    @endforelse
  </div>
</div></section>

<section class="max-w-7xl mx-auto px-4 py-12"><div class="card overflow-hidden grid md:grid-cols-2">
  <div class="p-7 sm:p-10">
    <p class="section-kicker">mampir, ya</p><h2 class="font-serif text-3xl mt-2">temukan sudut nyamanmu</h2>
    <p class="text-sm text-coffee/70 leading-6 mt-4">{{ setting('address', 'Jl. Contoh No. 10, Depok, Jawa Barat') }}</p>
    <p class="text-sm text-coffee/70 mt-2">🕒 {{ setting('opening_hours', 'Setiap hari, 08.00 - 22.00 WIB') }}</p>
    <div class="flex flex-wrap gap-3 mt-6">
      <a class="btn btn-coffee" href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp', '6281234567890')) }}" target="_blank" rel="noopener">Chat WhatsApp</a>
      <a class="btn btn-light" href="{{ route('about') }}">Info lengkap</a>
    </div>
  </div>
  <div class="min-h-[260px]"><iframe title="Lokasi Kopiah" class="w-full h-full min-h-[260px] border-0" loading="lazy" src="{{ setting('google_maps_embed', 'https://www.google.com/maps?q=Depok%20Jawa%20Barat&output=embed') }}" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
</div></section>
@endsection
@push('scripts')
<script>document.querySelectorAll('[data-copy]').forEach(b=>b.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(b.dataset.copy);b.textContent='Tersalin!'}catch(e){prompt('Salin kode promo ini:',b.dataset.copy)}}));</script>
@endpush
