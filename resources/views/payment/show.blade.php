@extends('layouts.app')
@section('title', 'Pembayaran QRIS')
@section('content')
<section class="max-w-5xl mx-auto px-4 py-10">
  <div class="text-center mb-6">
    <p class="section-kicker">satu langkah lagi</p>
    <h1 class="font-serif text-4xl mt-2">bayar pesanan</h1>
    <p class="text-sm text-coffee/60 mt-2">atas nama {{ $order->nama_pelanggan }} · {{ $order->meja ? 'Meja '.$order->meja->nomor_meja : 'Take away' }}</p>
  </div>
  @include('payment._panel', ['amount' => (int) round($order->total)])
  <p class="text-xs text-center text-coffee/55 mt-5">pesanan masuk ke kasir setelah pembayaran terkonfirmasi.</p>
</section>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="{{ asset('js/payment.js') }}"></script>
<script>
KopiahPayment.mount(document.querySelector('[data-payment]'), {
  startUrl: @json(route('payment.start', $order->token)),
  statusUrl: @json(route('payment.status', $order->token)),
  amount: @json((int) round($order->total)),
});
</script>
@endpush
