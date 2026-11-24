@extends('layouts.app')
@section('title', 'Kopiah Moments')
@section('content')
<section class="lk-shell px-4 pt-6 pb-20" id="lkApp"
  data-store-url="{{ route('moments.store') }}" data-base-url="{{ url('/moments') }}"
  data-count-url="{{ route('moments.count') }}" data-last-id="{{ $lastId }}">

  <div class="flex items-center justify-between gap-3">
    <div><p class="section-kicker">kopiah moments</p><h1 class="font-serif text-3xl leading-tight">momen kecil,<br>langsung dibagi.</h1></div>
    <button id="lkProfileBtn" class="lk-avatar" type="button" aria-label="Profil kamu">☕</button>
  </div>

  <div class="mt-4"><x-flash /></div>

  {{-- Viewfinder persegi 1:1 seperti Locket --}}
  <div class="lk-viewfinder mt-4" id="lkViewfinder">
    <video id="lkVideo" playsinline autoplay muted></video>
    <canvas id="lkCanvas" class="hidden"></canvas>

    <button type="button" id="lkIdle" class="lk-idle">
      <span class="text-5xl">📷</span>
      <b class="mt-3">ketuk untuk membuka kamera</b>
      <small class="mt-1 opacity-70">foto langsung dari kamera, tanpa galeri</small>
    </button>

    <button type="button" id="lkFilter" class="lk-chip hidden" aria-label="Ganti filter">Normal</button>

    <div id="lkCaptionWrap" class="lk-caption-wrap hidden">
      <input id="lkCaption" class="lk-caption-input" maxlength="140" placeholder="tambah caption…" autocomplete="off">
    </div>
  </div>

  {{-- Kontrol: riwayat / retake — shutter / kirim — balik kamera --}}
  <div class="lk-controls">
    <button type="button" id="lkLeft" class="lk-side" aria-label="Ganti tampilan feed / riwayat">▦</button>
    <button type="button" id="lkShutter" class="lk-shutter" aria-label="Ambil foto"><span></span></button>
    <button type="button" id="lkRight" class="lk-side" aria-label="Balik kamera">⟲</button>
  </div>
  <p id="lkMsg" class="text-center text-sm min-h-[1.5rem] mt-3 text-coffee/70" role="status"></p>

  <button type="button" id="lkNewPill" class="lk-pill hidden">↑ momen baru</button>

  <div class="flex items-center justify-between mt-6 mb-3">
    <h2 class="font-serif text-2xl">teman Kopiah</h2>
    <span class="text-xs text-coffee/55" id="lkModeLabel">feed</span>
  </div>

  <div id="lkList" class="lk-mode-feed">
    @forelse ($moments as $m)
      @include('moments._card', ['m' => $m, 'device' => $device])
    @empty
      <div class="card p-8 text-center" id="lkEmpty"><div class="text-4xl">☕</div><p class="font-serif text-xl mt-3">belum ada momen</p><p class="text-sm text-coffee/60 mt-1">jadi yang pertama berbagi momen di Kopiah.</p></div>
    @endforelse
  </div>
</section>

<dialog id="lkProfile" class="lk-dialog">
  <form method="dialog" class="space-y-4" id="lkProfileForm">
    <h3 class="font-serif text-2xl">profil Kopiah kamu</h3>
    <p class="text-xs text-coffee/60">tanpa akun — nama dan avatar disimpan di perangkat ini saja.</p>
    <div><label class="field-label" for="lkName">nama panggilan</label><input class="field" id="lkName" maxlength="40" placeholder="mis. Ahmad"></div>
    <div><span class="field-label">avatar</span><div class="flex flex-wrap gap-2 mt-1" id="lkAvatars">
      @foreach (['☕', '🍩', '🌿', '🐱', '🌙', '🎧', '🍪', '😎'] as $emoji)<button type="button" class="lk-avatar" data-avatar="{{ $emoji }}">{{ $emoji }}</button>@endforeach
    </div></div>
    <button class="btn btn-coffee w-full" value="save">Simpan</button>
  </form>
</dialog>
@endsection
@push('scripts')
<script defer src="{{ asset('js/moments.js') }}"></script>
@endpush
