@extends('layouts.app')
@section('title', 'Keranjang')
@section('content')
<section class="max-w-5xl mx-auto px-4 py-10">
  <p class="section-kicker">hampir jadi milikmu</p>
  <h1 class="font-serif text-4xl mt-2">keranjang kamu</h1>
  <div class="mt-4"><x-flash /></div>

  @if ($items->isEmpty())
    <div class="card p-8 sm:p-12 text-center mt-7">
      <div class="text-5xl">☕</div>
      <h2 class="font-serif text-2xl mt-4">keranjangmu masih kosong</h2>
      <p class="text-sm text-coffee/60 mt-2">pilih menu favoritmu dulu, yuk.</p>
      <a class="btn btn-coffee mt-5" href="{{ route('menu.index') }}">Lihat Menu</a>
    </div>
  @else
    <div class="grid lg:grid-cols-[1fr_320px] gap-6 mt-7">
      <div class="space-y-3">
        @foreach ($items as $item)
          <article class="card p-4 flex gap-4">
            <img class="w-24 h-24 sm:w-28 sm:h-28 object-cover rounded-2xl bg-white" src="{{ $item->foto_url }}" alt="{{ $item->name }}" onerror="this.onerror=null;this.src='/assets/img/coffee-placeholder.svg';">
            <div class="flex-1 min-w-0">
              <div class="flex justify-between gap-3"><h2 class="font-semibold">{{ $item->name }}</h2><b class="text-sm whitespace-nowrap">@rupiah($item->line_total)</b></div>
              @if ($item->options->isNotEmpty())<p class="text-xs text-coffee/60 mt-1">{{ $item->options->pluck('nama_opsi')->implode(', ') }}</p>@endif
              @if ($item->note)<p class="text-xs text-coffee/60 mt-1">Catatan: {{ $item->note }}</p>@endif
              <div class="flex items-center justify-between mt-3">
                <form method="post" action="{{ route('cart.update', $item->key) }}" class="flex items-center gap-2">
                  @csrf @method('PATCH')
                  <input class="field !w-20 !p-2" type="number" name="qty" min="1" max="20" value="{{ $item->qty }}" aria-label="Jumlah">
                  <button class="text-xs font-semibold underline" type="submit">Perbarui</button>
                </form>
                <form method="post" action="{{ route('cart.remove', $item->key) }}">
                  @csrf @method('DELETE')
                  <button class="text-xs text-red-700" type="submit">Hapus</button>
                </form>
              </div>
            </div>
          </article>
        @endforeach
      </div>
      <aside class="card p-5 h-fit">
        <h2 class="font-serif text-2xl">ringkasan</h2>
        <div class="flex justify-between mt-5 text-sm"><span>Subtotal</span><span>@rupiah($subtotal)</span></div>
        <p class="text-xs text-coffee/55 mt-3">pajak atau promo (jika tersedia) dihitung saat checkout.</p>
        <div class="border-t border-coffee/10 mt-5 pt-5 flex justify-between font-bold"><span>Subtotal</span><span>@rupiah($subtotal)</span></div>
        <a class="btn btn-coffee w-full mt-5" href="{{ route('checkout.create') }}">Lanjut ke Checkout →</a>
        <a class="btn btn-light w-full mt-3" href="{{ route('menu.index') }}">Tambah menu</a>
      </aside>
    </div>
  @endif
</section>
@endsection
