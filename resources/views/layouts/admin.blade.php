@php($user = auth()->user())
@php($role = $user->role)
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  @include('layouts.partials.head')
  <title>@yield('title', 'Panel') | Kopiah Panel</title>
  @stack('head')
</head>
<body class="bg-[#F8F4EE] text-coffee font-sans min-h-screen">
<div class="min-h-screen md:flex">
  <aside class="bg-white border-r border-coffee/10 md:w-64 md:fixed md:inset-y-0 md:left-0 z-30">
    <div class="px-5 py-5 flex items-center justify-between">
      <a href="{{ $user->homeUrl() }}" class="flex items-center gap-2">
        <img src="{{ site_logo() }}" class="w-11 h-11 object-contain rounded-lg bg-[#E9D6B6]" alt="Logo Kopiah">
        <span><b class="font-serif text-xl">Kopiah</b><small class="block text-[10px] tracking-widest text-coffee/50">PANEL {{ strtoupper($role) }}</small></span>
      </a>
      <button class="md:hidden p-2" data-admin-toggle type="button">☰</button>
    </div>
    <nav id="adminNav" class="hidden md:block px-3 pb-5 space-y-1 text-sm">
      @if ($role === 'owner')
        <a class="admin-sidebar-link" href="{{ route('admin.dashboard') }}">▦ &nbsp; Dashboard</a>
        <a class="admin-sidebar-link" href="{{ route('admin.menus.index') }}">☕ &nbsp; Menu &amp; Stok</a>
        <a class="admin-sidebar-link" href="{{ route('admin.categories.index') }}">▤ &nbsp; Kategori</a>
        <a class="admin-sidebar-link" href="{{ route('admin.users.index') }}">♙ &nbsp; Akun Kasir</a>
        <a class="admin-sidebar-link" href="{{ route('admin.promos.index') }}">✦ &nbsp; Promo</a>
        <a class="admin-sidebar-link" href="{{ route('admin.tables.index') }}">▦ &nbsp; Meja &amp; QR</a>
        <a class="admin-sidebar-link" href="{{ route('admin.reports.index') }}">▥ &nbsp; Laporan</a>
        <a class="admin-sidebar-link" href="{{ route('admin.moments.index') }}">☕ &nbsp; Kopiah Moments</a>
        <a class="admin-sidebar-link" href="{{ route('admin.moderation.index') }}">♡ &nbsp; Moderasi Review</a>
        <a class="admin-sidebar-link" href="{{ route('admin.settings.edit') }}">⚙ &nbsp; Pengaturan</a>
      @else
        <a class="admin-sidebar-link" href="{{ route('kasir.dashboard') }}">▦ &nbsp; Dashboard</a>
        <a class="admin-sidebar-link" href="{{ route('kasir.orders.index') }}">☷ &nbsp; Pesanan <span id="kn-badge" class="kn-badge" hidden aria-live="polite">0</span></a>
        <a class="admin-sidebar-link" href="{{ route('kasir.pos.create') }}">＋ &nbsp; POS / Pesanan manual</a>
        <a class="admin-sidebar-link" href="{{ route('kasir.cash.index') }}">▣ &nbsp; Kas harian</a>
      @endif
      <div class="border-t border-coffee/10 my-3"></div>
      @if ($role === 'owner')
        <a class="admin-sidebar-link" href="{{ route('staff.password.edit') }}">♧ &nbsp; Ganti password</a>
      @endif
      <a class="admin-sidebar-link" href="{{ route('home') }}" target="_blank">↗ &nbsp; Lihat website</a>
    </nav>
  </aside>
  <div class="flex-1 md:ml-64 min-w-0">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-coffee/10 px-4 sm:px-7 py-4 flex items-center justify-between gap-3">
      <div><p class="text-xs text-coffee/50">Kopiah / {{ $role }}</p><h1 class="font-semibold text-lg">@yield('title', 'Panel')</h1></div>
      <div class="flex items-center gap-3">
        @if ($role === 'kasir')
          {{-- Notifikasi pesanan baru: status koneksi + suara. Logika di public/js/kasir-notify.js --}}
          <div id="kn-root" class="flex items-center gap-2" data-poll-url="{{ route('kasir.orders.poll') }}" data-orders-url="{{ route('kasir.orders.index') }}">
            <span class="flex items-center gap-2 text-[11px] text-coffee/60" role="status">
              <span id="kn-dot" class="kn-dot" data-state="ok" aria-hidden="true"></span>
              <span id="kn-status-text" class="sr-only lg:not-sr-only">Terhubung</span>
            </span>
            <button id="kn-sound" type="button" class="btn btn-coffee !px-3 !py-2 !text-xs" aria-pressed="false">
              <span data-full class="hidden sm:inline">Aktifkan suara</span><span data-short class="sm:hidden">Suara</span>
            </button>
          </div>
        @endif
        <div class="text-right hidden sm:block">
          <p class="text-sm font-semibold">{{ $user->nama }}</p>
          <p class="text-[11px] uppercase text-coffee/50">{{ $role }}</p>
          @if ($role === 'owner')
            <a class="text-[11px] underline text-coffee/60" href="{{ route('staff.password.edit') }}">Ganti password</a>
          @endif
        </div>
        <form action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-light !px-3 !py-2 !text-xs" type="submit">Keluar</button></form>
      </div>
    </header>
    <main class="px-4 sm:px-7 py-6 max-w-[1600px]">
      <x-flash />
      @yield('content')
    </main>
    <footer class="px-4 sm:px-7 pb-7 text-xs text-coffee/40">Kopiah Panel · {{ date('Y') }} · Kelola dengan hati.</footer>
  </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{const b=document.querySelector('[data-admin-toggle]'),n=document.getElementById('adminNav');if(b&&n)b.addEventListener('click',()=>n.classList.toggle('hidden'))});</script>
@if ($role === 'kasir')
  <div id="kn-toasts" aria-live="polite"></div>
  <script src="{{ asset('js/kasir-notify.js') }}" defer></script>
@endif
@stack('scripts')
</body>
</html>
