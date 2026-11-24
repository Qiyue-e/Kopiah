@extends('layouts.admin')
@section('title', 'Laporan Penjualan')
@section('content')
@php($query = ['from' => $from, 'to' => $to, 'menu_id' => $menuId, 'kasir_id' => $cashierId])
<div class="no-print card p-5">
  <form method="get" action="{{ route('admin.reports.index') }}" class="grid sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
    <div><label class="field-label">Dari tanggal</label><input class="field" type="date" name="from" value="{{ $from }}"></div>
    <div><label class="field-label">Sampai tanggal</label><input class="field" type="date" name="to" value="{{ $to }}"></div>
    <div><label class="field-label">Filter menu</label>
      <select class="field" name="menu_id"><option value="0">Semua menu</option>@foreach ($menus as $menu)<option value="{{ $menu->id }}" @selected($menuId === $menu->id)>{{ $menu->nama }}</option>@endforeach</select>
    </div>
    <div><label class="field-label">Filter kasir</label>
      <select class="field" name="kasir_id"><option value="0">Semua kasir</option>@foreach ($cashiers as $cashier)<option value="{{ $cashier->id }}" @selected($cashierId === $cashier->id)>{{ $cashier->nama }}</option>@endforeach</select>
    </div>
    <button class="btn btn-coffee">Terapkan filter</button>
  </form>
  <div class="flex flex-wrap gap-3 mt-4">
    <a class="btn btn-light !py-2" href="{{ route('admin.reports.export', $query) }}">Export Excel (CSV)</a>
    <button class="btn btn-light !py-2" type="button" onclick="window.print()">Cetak / Simpan PDF</button>
  </div>
</div>
<div class="grid sm:grid-cols-3 gap-4 mt-5">
  <div class="card p-5"><p class="text-xs text-coffee/55">Jumlah pesanan</p><p class="font-serif text-2xl mt-2">{{ (int) $summary->orders_count }}</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Omzet sesuai filter</p><p class="font-serif text-2xl mt-2">@rupiah($summary->sum_total)</p><p class="text-xs text-coffee/45 mt-1">hanya transaksi lunas; pesanan batal tidak dihitung</p></div>
  <div class="card p-5"><p class="text-xs text-coffee/55">Baris item</p><p class="font-serif text-2xl mt-2">{{ $rows->count() }}</p></div>
</div>
<section class="card p-5 mt-5">
  <h2 class="font-serif text-2xl">Detail penjualan</h2>
  <p class="text-xs text-coffee/55 mt-1">{{ \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y') }}</p>
  <div class="table-wrap mt-4"><table class="admin-table">
    <thead><tr><th>Pesanan</th><th>Waktu / Pelanggan</th><th>Menu</th><th>Qty</th><th>Harga</th><th>Subtotal item</th><th>Total pesanan</th><th>Bayar / Kasir</th></tr></thead>
    <tbody>
      @forelse ($rows as $item)
        <tr>
          <td><b>{{ $item->order->kode_pesanan }}</b><span class="block text-xs text-coffee/55">{{ $item->order->status_pesanan }}</span></td>
          <td>{{ $item->order->created_at->format('d/m/Y H:i') }}<span class="block text-xs text-coffee/55">{{ $item->order->nama_pelanggan }}</span></td>
          <td>{{ $item->nama_menu }}</td>
          <td>{{ $item->qty }}</td>
          <td>@rupiah($item->harga)</td>
          <td>@rupiah($item->line_total)</td>
          <td>@rupiah($item->order->total)</td>
          <td><span class="block">{{ $item->order->status_bayar }}</span><span class="text-xs text-coffee/55">{{ $item->order->metode_bayar }} · {{ $item->order->kasir->nama ?? 'Online' }}</span></td>
        </tr>
      @empty
        <tr><td colspan="8">tidak ada transaksi pada filter ini.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  <p class="text-xs text-coffee/50 mt-4">PDF: gunakan tombol cetak lalu pilih “Save as PDF” pada dialog print browser. CSV dapat dibuka di Excel atau Google Sheets.</p>
</section>
@endsection
@push('head')
<style>@media print{body{background:white!important}.no-print,aside,header,footer{display:none!important}.md\:ml-64{margin:0!important}.card{box-shadow:none!important;break-inside:avoid}.admin-table{font-size:9px}.admin-table th,.admin-table td{padding:4px}}</style>
@endpush
