@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<section class="max-w-5xl mx-auto px-4 py-10">
  <p class="section-kicker">satu langkah lagi</p>
  <h1 class="font-serif text-4xl mt-2">checkout</h1>
  <p class="text-sm text-coffee/60 mt-2">isi detail pesananmu. kamu tidak perlu membuat akun.</p>
  <div class="mt-4"><x-flash /></div>

  <form method="post" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-[1fr_340px] gap-6 mt-7">
    @csrf
    <div class="card p-5 sm:p-7 space-y-5">
      <div>
        <label class="field-label" for="nama">nama pelanggan</label>
        <input class="field" id="nama" name="nama_pelanggan" maxlength="100" value="{{ old('nama_pelanggan') }}" required placeholder="nama kamu">
      </div>
      <div>
        <label class="field-label" for="meja">nomor meja</label>
        <select class="field" id="meja" name="table_id">
          <option value="">Take away / belum pilih meja</option>
          @foreach ($tables as $table)
            <option value="{{ $table->id }}" @selected((int) old('table_id', $selectedTable) === $table->id)>Meja {{ $table->nomor_meja }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="field-label">metode pembayaran</label>
        <div class="grid sm:grid-cols-2 gap-3">
          <label class="border border-coffee/15 rounded-2xl p-4 flex gap-3">
            <input type="radio" class="accent-[#8A9A5B] mt-1" name="metode_bayar" value="Kasir" @checked(old('metode_bayar', 'Kasir') === 'Kasir')>
            <span><b class="block text-sm">Bayar di kasir</b><small class="block text-xs mt-1 text-coffee/60">tunai atau QRIS di kasir</small></span>
          </label>
          <label class="border border-coffee/15 rounded-2xl p-4 flex gap-3">
            <input type="radio" class="accent-[#8A9A5B] mt-1" name="metode_bayar" value="QRIS" @checked(old('metode_bayar') === 'QRIS')>
            <span><b class="block text-sm">QRIS</b><small class="block text-xs mt-1 text-coffee/60">menunggu konfirmasi kasir</small></span>
          </label>
        </div>
        <p class="text-xs text-coffee/55 mt-2">{{ setting('qris_info', 'Pembayaran QRIS dikonfirmasi oleh kasir.') }}</p>
      </div>
      <div>
        <label class="field-label" for="promo">kode promo (opsional)</label>
        <input class="field uppercase" id="promo" name="kode_promo" value="{{ old('kode_promo') }}" placeholder="mis. NGOPI10">
      </div>
      <div>
        <label class="field-label" for="catatan">catatan pesanan (opsional)</label>
        <textarea class="field" id="catatan" name="catatan" rows="2" maxlength="800" placeholder="permintaan khusus...">{{ old('catatan') }}</textarea>
      </div>
    </div>
    <aside class="card p-5 h-fit">
      <h2 class="font-serif text-2xl">ringkasan</h2>
      <div class="mt-4 space-y-3">
        @foreach ($items as $item)
          <div class="flex justify-between gap-3 text-sm"><span>{{ $item->qty }}× {{ $item->name }}</span><span class="whitespace-nowrap">@rupiah($item->line_total)</span></div>
        @endforeach
      </div>
      <div class="border-t border-coffee/10 my-4"></div>
      <div class="flex justify-between text-sm"><span>Subtotal</span><span>@rupiah($subtotal)</span></div>
      <div class="flex justify-between text-sm mt-2"><span>Promo &amp; pajak/service</span><span>dihitung saat proses</span></div>
      <div class="flex justify-between font-bold border-t border-coffee/10 mt-4 pt-4"><span>Estimasi total</span><span>@rupiah($subtotal)+</span></div>
      <button class="btn btn-coffee w-full mt-5" type="submit">Buat Pesanan →</button>
      <p class="text-xs text-center text-coffee/55 mt-3">dengan melanjutkan, pesanan akan dikirim ke kasir.</p>
    </aside>
  </form>
</section>
@endsection
