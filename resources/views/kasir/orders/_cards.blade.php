@forelse ($orders as $order)
  <article class="card p-5">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs text-coffee/50">{{ $order->created_at->format('Y-m-d H:i:s') }}</p>
        <h2 class="font-semibold mt-1">{{ $order->kode_pesanan }}</h2>
        <p class="text-sm text-coffee/65 mt-1">{{ $order->nama_pelanggan }} · {{ $order->meja ? 'Meja '.$order->meja->nomor_meja : 'Take away' }}</p>
      </div>
      <span class="status-pill {{ $order->status_pesanan === 'Selesai' ? 'status-green' : ($order->status_pesanan === 'Siap' ? 'status-orange' : '') }}">{{ $order->status_pesanan }}</span>
    </div>
    <div class="border-t border-coffee/10 my-3"></div>
    <div>
      @foreach ($order->items as $item)
        <div class="flex justify-between gap-3 text-sm py-1">
          <span>{{ $item->qty }}× {{ $item->nama_menu }} @if ($item->opsi_nama)<small class="block text-xs text-coffee/50">{{ $item->opsi_nama }}</small>@endif @if ($item->catatan)<small class="block text-xs text-coffee/50">Catatan: {{ $item->catatan }}</small>@endif</span>
          <b class="whitespace-nowrap">@rupiah($item->line_total)</b>
        </div>
      @endforeach
    </div>
    @if ($order->catatan)<p class="text-xs bg-cream p-2 rounded-lg mt-2">Catatan: {{ $order->catatan }}</p>@endif
    <div class="flex justify-between items-center border-t border-coffee/10 mt-3 pt-3"><span class="text-sm">Total</span><b class="text-lg">@rupiah($order->total)</b></div>
    <div class="flex flex-wrap items-center justify-between gap-2 mt-2">
      <span class="status-pill {{ $order->status_bayar === 'Lunas' ? 'status-green' : ($order->status_bayar === 'Gagal' ? 'status-red' : 'status-orange') }}">{{ $order->status_bayar }}</span>
      <a class="text-xs underline" href="{{ route('kasir.receipt', $order->kode_pesanan) }}">Preview struk ↗</a>
    </div>

    <form data-ajax method="post" action="{{ route('kasir.orders.status', $order) }}" class="mt-4 grid grid-cols-[1fr_auto] gap-2">
      @csrf @method('PATCH')
      <select class="field !py-2" name="status_pesanan">@foreach (\App\Models\Order::STATUSES as $status)<option @selected($status === $order->status_pesanan)>{{ $status }}</option>@endforeach</select>
      <button class="btn btn-coffee !px-3 !py-2">Update</button>
    </form>
    <form data-ajax method="post" action="{{ route('kasir.orders.payment', $order) }}" class="mt-2 grid grid-cols-[1fr_auto] gap-2">
      @csrf @method('PATCH')
      <div class="grid grid-cols-2 gap-2">
        <select class="field !py-2" name="status_bayar">@foreach (\App\Models\Order::PAYMENT_STATUSES as $status)<option @selected($status === $order->status_bayar)>{{ $status }}</option>@endforeach</select>
        <select class="field !py-2" name="metode_bayar"><option value="Kasir" @selected($order->metode_bayar === 'Kasir')>Kasir/tunai</option><option value="QRIS" @selected($order->metode_bayar === 'QRIS')>QRIS</option></select>
      </div>
      <button class="btn btn-light !px-3 !py-2">Konfirmasi bayar</button>
    </form>
  </article>
@empty
  <div class="card p-8 text-center text-sm text-coffee/60 lg:col-span-2 2xl:col-span-3">belum ada pesanan aktif dalam 7 hari terakhir.</div>
@endforelse
