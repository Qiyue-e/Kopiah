@props(['menu'])
<article class="card menu-card flex flex-col">
  <a href="{{ route('menu.show', $menu) }}" class="overflow-hidden relative">
    <img class="menu-photo" src="{{ $menu->foto_url }}" alt="{{ $menu->nama }}" loading="lazy" onerror="this.onerror=null;this.src='/assets/img/coffee-placeholder.svg';">
    @if (! $menu->isOrderable())
      <span class="menu-tag menu-tag-dark">Habis</span>
    @elseif ($menu->isNew())
      <span class="menu-tag">Baru</span>
    @endif
  </a>
  <div class="p-4 flex flex-col flex-1">
    <span class="text-xs text-coffee/55">{{ $menu->category->nama }}</span>
    <a href="{{ route('menu.show', $menu) }}" class="menu-name leading-snug mt-1">{{ $menu->nama }}</a>
    <p class="text-xs leading-5 text-coffee/60 mt-2 line-clamp-2">{{ $menu->deskripsi }}</p>
    <div class="mt-auto pt-4 flex items-center justify-between gap-2">
      <b class="text-sm font-medium lp-num">@rupiah($menu->harga)</b>
      @if ($menu->isOrderable())
        <a class="menu-add" aria-label="Detail {{ $menu->nama }}" href="{{ route('menu.show', $menu) }}"><x-icon name="plus" class="w-4 h-4" /></a>
      @else
        <span class="text-xs text-red-800">Tidak tersedia</span>
      @endif
    </div>
  </div>
</article>
