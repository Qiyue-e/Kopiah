<section class="card p-5 {{ $class ?? 'mt-5' }}">
  <h2 class="font-serif text-2xl">Ketersediaan menu</h2>
  <p class="text-xs text-coffee/55 mt-1">kasir bisa menandai menu habis tanpa membuka panel owner.</p>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mt-4">
    @foreach ($menus as $menu)
      <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white border border-coffee/10">
        <div><b class="text-sm">{{ $menu->nama }}</b><span class="block text-xs text-coffee/50">stok {{ $menu->stok }}</span></div>
        <form method="post" action="{{ route('kasir.menus.availability', $menu) }}">@csrf @method('PATCH')<button class="text-xs underline">{{ $menu->is_available ? 'Tandai habis' : 'Tersedia' }}</button></form>
      </div>
    @endforeach
  </div>
</section>
