@extends('layouts.app')
@section('title', 'Menu')
@section('content')
<section class="max-w-7xl mx-auto px-4 pt-10 pb-6">
  <p class="section-kicker">dibuat untuk mood kamu</p>
  <h1 class="font-serif text-4xl sm:text-5xl mt-2">menu Kopiah</h1>
  <p class="text-sm text-coffee/65 mt-3 max-w-xl">dari kopi klasik sampai camilan manis, pilih teman untuk momenmu hari ini.</p>
  <form method="get" action="{{ route('menu.index') }}" class="mt-7 flex flex-col sm:flex-row gap-3">
    <input class="field flex-1" type="search" name="q" value="{{ $q }}" placeholder="cari kopi, matcha, camilan...">
    <select class="field sm:max-w-xs" name="kategori" aria-label="Filter kategori">
      <option value="0">Semua kategori</option>
      @foreach ($categories as $category)<option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->nama }}</option>@endforeach
    </select>
    <button class="btn btn-coffee" type="submit">Cari menu</button>
  </form>
  <div class="flex gap-2 overflow-x-auto py-5">
    <a href="{{ route('menu.index') }}" class="px-4 py-2 rounded-full text-sm whitespace-nowrap {{ ! $categoryId ? 'bg-coffee text-white' : 'bg-white/80' }}">Semua</a>
    @foreach ($categories as $category)
      <a href="{{ route('menu.index', array_filter(['kategori' => $category->id, 'q' => $q])) }}" class="px-4 py-2 rounded-full text-sm whitespace-nowrap {{ $categoryId === $category->id ? 'bg-coffee text-white' : 'bg-white/80' }}">{{ $category->nama }}</a>
    @endforeach
  </div>
</section>
<section class="max-w-7xl mx-auto px-4 pb-12">
  <x-flash />
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6" data-stagger>
    @foreach ($menus as $menu)<x-menu-card :menu="$menu" />@endforeach
  </div>
  @if ($menus->isEmpty())
    <div class="text-center py-20"><p class="font-serif text-2xl">menu belum ditemukan</p><p class="text-sm text-coffee/60 mt-2">coba kata kunci atau kategori lain, ya.</p><a class="btn btn-coffee mt-5" href="{{ route('menu.index') }}">Reset filter</a></div>
  @endif
</section>
@endsection
