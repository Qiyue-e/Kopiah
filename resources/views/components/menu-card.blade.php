@props(['menu'])
<article class="card menu-card flex flex-col">
  <a href="{{ route('menu.show', $menu) }}" class="overflow-hidden relative">
    <img class="menu-photo" src="{{ $menu->foto_url }}" alt="{{ $menu->nama }}" loading="lazy" onerror="this.onerror=null;this.src='/assets/img/coffee-placeholder.svg';">
    @if (! $menu->isOrderable())
      <span class="absolute top-3 left-3 bg-black/75 text-white text-xs rounded-full px-3 py-1">Habis</span>
    @elseif ($menu->is_best_seller)
      <span class="absolute top-3 left-3 badge-soft">Best Seller</span>
    @elseif ($menu->isNew())
      <span class="absolute top-3 left-3 badge-soft badge-caramel">Baru</span>
    @endif
  </a>
  <div class="p-4 flex flex-col flex-1">
    <span class="text-xs text-coffee/55">{{ $menu->category->nama }}</span>
    <a href="{{ route('menu.show', $menu) }}" class="font-semibold leading-snug mt-1">{{ $menu->nama }}</a>
    <p class="text-xs leading-5 text-coffee/60 mt-2 line-clamp-2">{{ $menu->deskripsi }}</p>
    <div class="mt-auto pt-4 flex items-center justify-between gap-2">
      <b class="text-sm">@rupiah($menu->harga)</b>
      @if ($menu->isOrderable())
        <a class="rounded-full bg-coffee text-white w-9 h-9 flex items-center justify-center" aria-label="Detail {{ $menu->nama }}" href="{{ route('menu.show', $menu) }}">+</a>
      @else
        <span class="text-xs text-red-700">tidak tersedia</span>
      @endif
    </div>
  </div>
</article>
