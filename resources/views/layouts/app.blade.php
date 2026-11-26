@inject('cartService', 'App\Services\CartService')
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  @include('layouts.partials.head')
  <title>@yield('title', setting('cafe_name', 'Kopiah')) | {{ setting('cafe_name', 'Kopiah') }}</title>
  <meta name="description" content="Kopiah — Ngopi santai, momen jadi berarti.">
  <script>/* sembunyikan elemen yang akan dianimasikan sampai skrip siap; jaga-jaga bila CDN gagal */(function(){var d=document.documentElement;if(!matchMedia('(prefers-reduced-motion: reduce)').matches){d.classList.add('motion-pending');setTimeout(function(){d.classList.remove('motion-pending')},2500)}})();</script>
  @stack('head')
</head>
<body class="bg-paper text-coffee font-sans min-h-screen flex flex-col">
<header class="site-header sticky top-0 z-40 bg-paper/95 backdrop-blur border-b border-coffee/10">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between gap-4">
    <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
      <img src="{{ site_logo() }}" class="w-10 h-10 object-contain rounded-lg bg-[#E9D6B6]" alt="Logo Kopiah">
      <b class="font-serif text-xl font-medium leading-none">{{ setting('cafe_name', 'Kopiah') }}</b>
    </a>
    <button class="md:hidden rounded-lg border border-coffee/15 w-10 h-10 grid place-items-center transition-colors hover:bg-coffee/5" type="button" aria-label="Buka navigasi" data-menu-toggle><x-icon name="menu" class="w-5 h-5" /></button>
    <nav class="hidden md:flex items-center gap-6 text-sm" data-main-nav aria-label="Navigasi utama">
      <a class="nav-link" href="{{ route('home') }}">Beranda</a>
      <a class="nav-link" href="{{ route('menu.index') }}">Menu</a>
      <a class="nav-link" href="{{ route('moments.index') }}">Kopiah Moments</a>
      <a class="nav-link" href="{{ route('about') }}">Tentang</a>
      <a href="{{ route('cart.index') }}" class="btn btn-coffee !py-2 !px-4"><x-icon name="shopping-bag" /> Keranjang <span class="cart-count">{{ $cartService->count() }}</span></a>
      @auth
        <a class="nav-link nav-link-quiet" href="{{ auth()->user()->homeUrl() }}">Panel {{ ucfirst(auth()->user()->role) }}</a>
      @else
        <a class="nav-link nav-link-quiet" href="{{ route('login') }}">Login staf</a>
      @endauth
    </nav>
  </div>
  <nav class="hidden md:hidden flex-col gap-4 px-6 pb-4 text-sm" data-mobile-nav aria-label="Navigasi seluler">
    <a href="{{ route('home') }}">Beranda</a><a href="{{ route('menu.index') }}">Menu</a><a href="{{ route('moments.index') }}">Kopiah Moments</a><a href="{{ route('about') }}">Tentang</a>
    <a href="{{ route('cart.index') }}">Keranjang ({{ $cartService->count() }})</a><a class="text-coffee/60" href="{{ route('login') }}">Login staf</a>
  </nav>
</header>
<main class="flex-1">
  @yield('content')
</main>
<footer class="site-footer bg-coffee text-cream mt-16">
  <div class="max-w-7xl mx-auto px-6 py-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
    <div>
      <div class="flex items-center gap-3 mb-3"><img src="{{ site_logo() }}" class="w-10 h-10 object-contain rounded-lg bg-[#E9D6B6]" alt="Logo Kopiah"><b class="font-serif text-2xl font-medium">{{ setting('cafe_name', 'Kopiah') }}</b></div>
      <p class="text-sm text-cream/70">{{ setting('tagline', 'Ngopi santai, momen jadi berarti') }}</p>
    </div>
    <div><h3 class="font-sans text-sm font-medium mb-3">Jelajahi</h3><div class="footer-links"><a href="{{ route('menu.index') }}">Menu kami</a><a href="{{ route('moments.index') }}">Kopiah Moments</a><a href="{{ route('about') }}">Tentang kami</a></div></div>
    <div><h3 class="font-sans text-sm font-medium mb-3">Jam buka</h3><p class="text-sm text-cream/70">{{ setting('opening_hours', 'Setiap hari, 08.00 - 22.00 WIB') }}</p><p class="text-sm text-cream/70 mt-2">{{ setting('address', 'Depok, Jawa Barat') }}</p></div>
    <div><h3 class="font-sans text-sm font-medium mb-3">Terhubung</h3><div class="footer-links">
      <a href="https://instagram.com/{{ ltrim(setting('instagram', 'kopiah.id'), '@') }}" target="_blank" rel="noopener">Instagram {{ setting('instagram', '@kopiah.id') }}</a>
      <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp', '6281234567890')) }}" target="_blank" rel="noopener">Chat WhatsApp</a>
    </div></div>
  </div>
  <div class="border-t border-white/10 text-center py-4 text-xs text-cream/60">© {{ date('Y') }} Kopiah</div>
</footer>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/motion@11.11.13/dist/motion.js"></script>
<script defer src="{{ asset('js/ui.js') }}"></script>
<script defer src="{{ asset('js/motion.js') }}"></script>
@stack('scripts')
</body>
</html>
