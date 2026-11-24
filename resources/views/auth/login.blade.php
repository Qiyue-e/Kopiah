<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  @include('layouts.partials.head')
  <title>Login Staf | Kopiah</title>
</head>
<body class="min-h-screen bg-cream flex items-center justify-center p-4">
<div class="w-full max-w-4xl grid md:grid-cols-2 card overflow-hidden">
  <div class="hero-bg text-white p-8 sm:p-10 flex flex-col justify-between min-h-[250px]">
    <a href="{{ route('home') }}" class="flex items-center gap-2"><img src="{{ site_logo() }}" class="w-12 h-12 object-contain rounded-lg bg-[#E9D6B6]" alt="Logo Kopiah"><span class="font-serif text-2xl">Kopiah</span></a>
    <div>
      <p class="text-xs tracking-[.2em] uppercase text-[#E8B46B]">ruang staf</p>
      <h1 class="font-serif text-4xl mt-3">selamat datang kembali.</h1>
      <p class="text-sm leading-6 text-white/70 mt-4">kelola pesanan dan jaga setiap momen tetap berarti.</p>
    </div>
  </div>
  <div class="p-7 sm:p-10">
    <p class="section-kicker">login aman</p>
    <h2 class="font-serif text-3xl mt-2">masuk ke panel</h2>
    <p class="text-sm text-coffee/60 mt-2">khusus akun owner dan kasir Kopiah.</p>
    <div class="mt-4"><x-flash /></div>
    <form method="post" action="{{ route('login.store') }}" class="space-y-4 mt-6">
      @csrf
      <div><label class="field-label" for="username">username</label><input id="username" class="field" name="username" autocomplete="username" required value="{{ old('username') }}"></div>
      <div><label class="field-label" for="password">password</label><input id="password" class="field" name="password" type="password" autocomplete="current-password" required></div>
      <button class="btn btn-coffee w-full" type="submit">Masuk →</button>
    </form>
    <p class="text-xs text-coffee/50 mt-5 leading-5">pelanggan tidak perlu login untuk memesan. kembali ke <a class="underline" href="{{ route('home') }}">website Kopiah</a>.</p>
  </div>
</div>
</body>
</html>
