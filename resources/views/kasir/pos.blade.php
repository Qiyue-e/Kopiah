@extends('layouts.admin')
@section('title', 'POS / Pesanan Manual')
@section('content')
<div class="max-w-4xl mx-auto"><div class="card p-5 sm:p-7">
  <p class="section-kicker">point of sale</p>
  <h2 class="font-serif text-3xl mt-2">pesanan langsung</h2>
  <p class="text-sm text-coffee/60 mt-2">buat pesanan untuk pelanggan di kasir. stok akan berkurang otomatis setelah pesanan berhasil dibuat.</p>

  <form method="post" action="{{ route('kasir.pos.store') }}" class="mt-6 space-y-5">
    @csrf
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label class="field-label">Nama pelanggan</label><input class="field" name="nama_pelanggan" maxlength="100" value="{{ old('nama_pelanggan', 'Pelanggan Langsung') }}" required></div>
      <div><label class="field-label">Nomor meja</label>
        <select class="field" name="table_id"><option value="">Take away / tidak ada meja</option>@foreach ($tables as $table)<option value="{{ $table->id }}" @selected((int) old('table_id') === $table->id)>Meja {{ $table->nomor_meja }}</option>@endforeach</select>
      </div>
    </div>

    <div>
      <div class="flex items-center justify-between gap-3 mb-3"><h3 class="font-semibold">Item pesanan</h3><button class="btn btn-light !py-2 !px-3 !text-xs" type="button" id="addItem">＋ Tambah item</button></div>
      <div id="itemRows" class="space-y-3">
        @foreach (old('items', [['menu_id' => '', 'qty' => 1]]) as $i => $row)
          <div class="pos-row grid grid-cols-[1fr_84px_38px] sm:grid-cols-[1fr_110px_42px] gap-2 items-end">
            <div><label class="field-label">Menu</label>
              <select class="field pos-menu" name="items[{{ $i }}][menu_id]">
                <option value="">Pilih menu</option>
                @foreach ($menus as $menu)<option value="{{ $menu->id }}" @selected((int) ($row['menu_id'] ?? 0) === $menu->id)>{{ $menu->nama }} — {{ rupiah($menu->harga) }} (stok {{ $menu->stok }})</option>@endforeach
              </select>
            </div>
            <div><label class="field-label">Qty</label><input class="field" type="number" name="items[{{ $i }}][qty]" value="{{ $row['qty'] ?? 1 }}" min="1" max="30"></div>
            <button class="btn btn-light !p-2 !h-11" type="button" data-remove-row aria-label="Hapus item">×</button>
          </div>
        @endforeach
      </div>
      @if ($menus->isEmpty())<p class="text-sm text-red-700 mt-2">tidak ada menu tersedia dengan stok. tambahkan stok di panel owner dulu.</p>@endif
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div><label class="field-label">Metode pembayaran</label>
        <select class="field" name="metode_bayar"><option value="Kasir" @selected(old('metode_bayar') === 'Kasir')>Bayar di kasir</option><option value="QRIS" @selected(old('metode_bayar') === 'QRIS')>QRIS</option></select>
      </div>
      <div><label class="field-label">Status pembayaran</label>
        <label class="flex items-center gap-3 border border-coffee/10 bg-white rounded-xl p-3"><input type="checkbox" name="paid" value="1" class="accent-[#8A9A5B]" @checked(old('paid'))><span><b class="text-sm">Sudah dibayar</b><small class="block text-xs text-coffee/55">centang jika pembayaran sudah diterima.</small></span></label>
      </div>
    </div>
    <div><label class="field-label">Catatan pesanan (opsional)</label><textarea class="field" name="catatan" rows="2" maxlength="800">{{ old('catatan') }}</textarea></div>
    <div class="flex flex-wrap justify-between items-center gap-3 border-t border-coffee/10 pt-4">
      <p class="text-xs text-coffee/55">pajak/service dihitung otomatis sesuai pengaturan.</p>
      <button class="btn btn-coffee" type="submit" @disabled($menus->isEmpty())>Buat Pesanan &amp; Struk →</button>
    </div>
  </form>
</div></div>
<template id="itemTemplate">
  <div class="pos-row grid grid-cols-[1fr_84px_38px] sm:grid-cols-[1fr_110px_42px] gap-2 items-end">
    <div><label class="field-label">Menu</label>
      <select class="field pos-menu" name="items[__INDEX__][menu_id]"><option value="">Pilih menu</option>@foreach ($menus as $menu)<option value="{{ $menu->id }}">{{ $menu->nama }} — {{ rupiah($menu->harga) }} (stok {{ $menu->stok }})</option>@endforeach</select>
    </div>
    <div><label class="field-label">Qty</label><input class="field" type="number" name="items[__INDEX__][qty]" value="1" min="1" max="30"></div>
    <button class="btn btn-light !p-2 !h-11" type="button" data-remove-row aria-label="Hapus item">×</button>
  </div>
</template>
@endsection
@push('scripts')
<script>
const rows = document.getElementById('itemRows'), tpl = document.getElementById('itemTemplate').innerHTML;
let idx = rows.children.length;
function bindRemove(row){
  row.querySelector('[data-remove-row]').addEventListener('click', () => {
    if (rows.children.length > 1) row.remove(); else { row.querySelector('select').value = ''; row.querySelector('input').value = 1; }
  });
}
[...rows.children].forEach(bindRemove);
document.getElementById('addItem').addEventListener('click', () => {
  const wrap = document.createElement('div'); wrap.innerHTML = tpl.replaceAll('__INDEX__', String(idx++));
  const row = wrap.firstElementChild; bindRemove(row); rows.appendChild(row);
});
</script>
@endpush
