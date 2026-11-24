@inject('cartService', 'App\Services\CartService')
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  @include('layouts.partials.head')
  <title>@yield('title', setting('cafe_name', 'Kopiah')) | {{ setting('cafe_name', 'Kopiah') }}</title>
  <meta name="description" content="Kopiah — Ngopi santai, momen jadi berarti.">
  @stack('head')
</head>
<body class="bg-cream text-coffee font-sans min-h-screen flex flex-col">
<header class="sticky top-0 z-40 bg-cream/95 backdrop-blur border-b border-coffee/10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
    <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
      <img src="{{ site_logo() }}" class="w-12 h-12 object-contain rounded-xl bg-[#E9D6B6]" alt="Logo Kopiah">
      <span><b class="font-serif text-xl leading-none">{{ setting('cafe_name', 'Kopiah') }}</b><small class="block text-[10px] tracking-wide text-coffee/60">NGOPI &amp; MOMEN</small></span>
    </a>
    <button class="md:hidden rounded-xl border border-coffee/15 p-2" type="button" aria-label="Buka navigasi" data-menu-toggle><span class="text-xl">☰</span></button>
    <nav class="hidden md:flex items-center gap-5 text-sm font-medium" data-main-nav>
      <a class="nav-link" href="{{ route('home') }}">Beranda</a>
      <a class="nav-link" href="{{ route('menu.index') }}">Menu</a>
      <a class="nav-link" href="{{ route('moments.index') }}">Kopiah Moments</a>
      <a class="nav-link" href="{{ route('about') }}">Tentang</a>
      <a href="{{ route('cart.index') }}" class="btn btn-coffee !py-2 !px-4">Keranjang <span class="cart-count">{{ $cartService->count() }}</span></a>
      @auth
        <a class="nav-link" href="{{ auth()->user()->homeUrl() }}">Panel {{ ucfirst(auth()->user()->role) }}</a>
      @else
        <a class="nav-link" href="{{ route('login') }}">Login staf</a>
      @endauth
    </nav>
  </div>
  <nav class="hidden md:hidden flex-col gap-4 px-4 pb-4 text-sm" data-mobile-nav>
    <a href="{{ route('home') }}">Beranda</a><a href="{{ route('menu.index') }}">Menu</a><a href="{{ route('moments.index') }}">Kopiah Moments</a><a href="{{ route('about') }}">Tentang</a>
    <a href="{{ route('cart.index') }}">Keranjang ({{ $cartService->count() }})</a><a href="{{ route('login') }}">Login staf</a>
  </nav>
</header>
<main class="flex-1">
  @yield('content')
</main>
<footer class="bg-coffee text-cream mt-16">
  <div class="max-w-7xl mx-auto px-4 py-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
    <div>
      <div class="flex items-center gap-2 mb-3"><img src="{{ site_logo() }}" class="w-10 h-10 object-contain rounded-lg bg-[#E9D6B6]" alt="Logo Kopiah"><b class="font-serif text-2xl">{{ setting('cafe_name', 'Kopiah') }}</b></div>
      <p class="text-sm text-cream/70">{{ setting('tagline', 'Ngopi santai, momen jadi berarti') }}.</p>
    </div>
    <div><h3 class="font-semibold mb-3">Jelajahi</h3><div class="footer-links"><a href="{{ route('menu.index') }}">Menu kami</a><a href="{{ route('moments.index') }}">Kopiah Moments</a><a href="{{ route('about') }}">Tentang kami</a></div></div>
    <div><h3 class="font-semibold mb-3">Jam buka</h3><p class="text-sm text-cream/70">{{ setting('opening_hours', 'Setiap hari, 08.00 - 22.00 WIB') }}</p><p class="text-sm text-cream/70 mt-2">{{ setting('address', 'Depok, Jawa Barat') }}</p></div>
    <div><h3 class="font-semibold mb-3">Terhubung</h3><div class="footer-links">
      <a href="https://instagram.com/{{ ltrim(setting('instagram', 'kopiah.id'), '@') }}" target="_blank" rel="noopener">Instagram {{ setting('instagram', '@kopiah.id') }}</a>
      <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp', '6281234567890')) }}" target="_blank" rel="noopener">Chat WhatsApp</a>
    </div></div>
  </div>
  <div class="border-t border-white/10 text-center py-4 text-xs text-cream/50">© {{ date('Y') }} Kopiah. Dibuat untuk momen yang berarti.</div>
</footer>
@stack('scripts')
</body>
</html>
