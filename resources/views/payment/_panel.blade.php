{{-- Panel pembayaran QRIS. Diisi oleh public/js/payment.js (QR, hitung mundur, pemantauan status). --}}
<div data-payment class="card p-5 sm:p-8 max-w-md mx-auto text-center" aria-live="polite">
  <p class="section-kicker">bayar dengan QRIS</p>
  <p class="text-xs text-coffee/55 mt-4">total pembayaran</p>
  <p class="font-bold text-3xl mt-1 tabular-nums" data-pay-amount>@isset($amount)@rupiah($amount)@else&nbsp;@endisset</p>

  <div data-pay-state="loading" class="mt-6" role="status" aria-label="Membuat QRIS">
    <div class="skeleton mx-auto w-full max-w-[280px] aspect-square rounded-2xl"></div>
    <div class="skeleton h-4 w-40 mx-auto mt-5"></div>
    <p class="text-xs text-coffee/55 mt-3">membuat QRIS...</p>
  </div>

  <div data-pay-state="ready" class="mt-6" hidden>
    <div class="pay-qr bg-white p-4 rounded-2xl border border-coffee/10 mx-auto w-full max-w-[312px]" data-pay-qr></div>
    <p class="text-sm mt-5">sisa waktu bayar <b class="tabular-nums" data-pay-countdown>--:--</b></p>
    <p class="text-xs text-coffee/60 mt-3 leading-5">scan dengan aplikasi bank atau e-wallet apa pun. halaman ini pindah sendiri setelah pembayaran diterima.</p>
  </div>

  <div data-pay-state="error" class="mt-6" hidden>
    <div class="flash flash-error" data-pay-error role="alert">QRIS belum bisa dibuat.</div>
    <button type="button" class="btn btn-coffee w-full mt-2" data-pay-retry>Coba lagi</button>
  </div>

  <div data-pay-state="expired" class="mt-6" hidden>
    <div class="flash flash-warn">waktu pembayaran habis dan pesanan dibatalkan. keranjang bisa diisi lagi dari menu.</div>
    <a class="btn btn-coffee w-full mt-2" href="{{ route('menu.index') }}">Kembali ke menu</a>
  </div>

  <div data-pay-state="paid" class="mt-6" hidden>
    <div class="flash flash-ok">pembayaran diterima. membuka struk...</div>
  </div>
</div>
