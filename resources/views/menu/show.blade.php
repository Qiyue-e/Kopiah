@extends('layouts.app')
@section('title', $menu->nama)
@section('content')
<section class="max-w-6xl mx-auto px-4 py-8">
  <a class="text-sm text-coffee/60 hover:text-coffee" href="{{ route('menu.index') }}">← kembali ke menu</a>
  <div class="grid lg:grid-cols-2 gap-7 lg:gap-12 mt-5 items-start">
    <div class="rounded-[28px] overflow-hidden bg-white"><img src="{{ $menu->foto_url }}" alt="{{ $menu->nama }}" class="w-full aspect-[4/3] object-cover" onerror="this.onerror=null;this.src='/assets/img/coffee-placeholder.svg';"></div>
    <div class="card p-5 sm:p-8">
      <div class="flex gap-2 items-center"><span class="section-kicker">{{ $menu->category->nama }}</span>@if ($menu->is_best_seller)<span class="badge-soft">Best Seller</span>@endif</div>
      <h1 class="font-serif text-3xl sm:text-4xl mt-3">{{ $menu->nama }}</h1>
      <p class="text-coffee/70 leading-7 mt-4">{{ $menu->deskripsi }}</p>
      <p class="text-2xl font-bold mt-5">@rupiah($menu->harga)</p>
      <div class="mt-4"><x-flash /></div>

      @if (! $menu->isOrderable())
        <div class="flash flash-warn">maaf, menu ini sedang habis.</div>
      @else
        <form method="post" action="{{ route('cart.add', $menu) }}" class="mt-6 space-y-5">
          @csrf
          @foreach ($groups as $type => $options)
            @php($isTopping = strtolower($type) === 'topping')
            @php($groupIndex = $loop->index)
            <fieldset>
              <legend class="field-label mb-2">{{ $type }}</legend>
              <div class="grid grid-cols-2 gap-2">
                @foreach ($options as $option)
                  <label class="flex items-center gap-2 border border-coffee/10 bg-white rounded-xl p-3 text-sm">
                    <input type="{{ $isTopping ? 'checkbox' : 'radio' }}" name="{{ $isTopping ? 'toppings[]' : 'options['.$groupIndex.']' }}" value="{{ $option->id }}" class="accent-[#8A9A5B]" @checked($loop->first && ! $isTopping)>
                    <span>{{ $option->nama_opsi }}</span>
                    @if ($option->harga_tambahan > 0)<small class="ml-auto text-coffee/60">+@rupiah($option->harga_tambahan)</small>@endif
                  </label>
                @endforeach
              </div>
            </fieldset>
          @endforeach
          <div>
            <label class="field-label" for="catatan">Catatan untuk barista (opsional)</label>
            <textarea class="field" rows="2" name="catatan" id="catatan" maxlength="300" placeholder="contoh: es sedikit, jangan terlalu manis...">{{ old('catatan') }}</textarea>
          </div>
          <div class="flex items-center justify-between gap-4">
            <label class="field-label mb-0">Jumlah <input class="field mt-2 w-24" type="number" name="qty" value="{{ old('qty', 1) }}" min="1" max="20"></label>
            <button class="btn btn-coffee flex-1" type="submit">Tambah ke keranjang · @rupiah($menu->harga)</button>
          </div>
        </form>
      @endif
    </div>
  </div>
</section>
@endsection
