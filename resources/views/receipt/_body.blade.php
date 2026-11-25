{{-- Isi struk (lebar mengikuti pilihan 58/80mm lewat public/js/receipt.js). Dipakai kasir & pelanggan. --}}
<article class="receipt" id="receipt">
  <div class="text-center">
    <img src="{{ site_logo() }}" alt="Logo {{ setting('cafe_name', 'Kopiah') }}" class="mx-auto h-16 w-16 object-contain">
    <div class="font-serif text-2xl font-bold">{{ setting('cafe_name', 'Kopiah') }}</div>
    <p class="text-xs mt-1">{{ setting('tagline', 'Ngopi santai, momen jadi berarti') }}</p>
    <p class="text-[10px] mt-1">{{ setting('address', 'Depok, Jawa Barat') }}</p>
  </div>
  <hr>
  @if ($order->antrean_label)
    <div class="text-center">
      <p class="text-[10px]">NOMOR ANTREAN</p>
      <p class="queue-number text-5xl font-bold mt-1">{{ $order->antrean_label }}</p>
    </div>
    <hr>
  @endif
  <div class="text-xs">
    <div class="receipt-row"><span>No. transaksi</span><span>{{ $order->kode_pesanan }}</span></div>
    <div class="receipt-row"><span>Tanggal</span><span>{{ ($order->paid_at ?? $order->created_at)->format('d/m/Y H:i') }}</span></div>
    <div class="receipt-row"><span>Pelanggan</span><span>{{ $order->nama_pelanggan }}</span></div>
    <div class="receipt-row"><span>Meja</span><span>{{ $order->meja->nomor_meja ?? 'Take away' }}</span></div>
    <div class="receipt-row"><span>Kasir</span><span>{{ $order->kasir->nama ?? 'Online' }}</span></div>
  </div>
  <hr>
  @foreach ($order->items as $item)
    <div class="receipt-item">
      <div class="text-xs font-bold">{{ $item->nama_menu }}</div>
      @if ($item->opsi_nama)<small>{{ $item->opsi_nama }}</small>@endif
      <div class="receipt-row text-xs"><span>{{ $item->qty }} × @rupiah($item->harga)</span><span>@rupiah($item->line_total)</span></div>
      @if ($item->catatan)<small>Catatan: {{ $item->catatan }}</small>@endif
    </div>
  @endforeach
  <hr>
  <div class="receipt-row text-xs"><span>Subtotal</span><span>@rupiah($order->subtotal)</span></div>
  @if ($order->diskon > 0)<div class="receipt-row text-xs"><span>Diskon</span><span>-@rupiah($order->diskon)</span></div>@endif
  @if ($order->pajak > 0)<div class="receipt-row text-xs"><span>Pajak/biaya</span><span>@rupiah($order->pajak)</span></div>@endif
  <div class="receipt-row font-bold text-base mt-2"><span>TOTAL</span><span>@rupiah($order->total)</span></div>
  <div class="receipt-row text-xs mt-2"><span>Metode</span><span>{{ $order->metode_bayar === 'Kasir' ? 'Kasir/tunai' : $order->metode_bayar }}</span></div>
  <div class="receipt-row text-xs"><span>Pembayaran</span><span>{{ $order->status_bayar }}</span></div>
  @if ($order->xendit_payment_id)
    <div class="receipt-row text-xs"><span>Ref. QRIS</span><span class="break-all text-right">{{ $order->xendit_payment_id }}</span></div>
  @endif
  <hr>
  <p class="text-center text-xs leading-5">terima kasih sudah mampir!<br>{{ setting('tagline', 'Ngopi santai, momen jadi berarti') }}</p>
  <p class="text-center text-[9px] mt-2">Simpan struk ini sebagai bukti pesanan.</p>
</article>
