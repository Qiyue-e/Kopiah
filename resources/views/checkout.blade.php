@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<section class="max-w-5xl mx-auto px-4 py-10">
  <p class="section-kicker">satu langkah lagi</p>
  <h1 class="font-serif text-4xl mt-2">checkout</h1>
  <p class="text-sm text-coffee/60 mt-2">isi detail pesananmu. kamu tidak perlu membuat akun.</p>
  <div class="mt-4" id="checkout-flash"><x-flash /></div>

  {{-- Setelah menekan "Bayar" dengan QRIS, form disembunyikan dan QR tampil di sini (tanpa pindah halaman). --}}
  @if ($qrisEnabled)
    <div id="checkout-pay" class="mt-7 hidden scroll-mt-24">
      @include('payment._panel')
      <p class="text-xs text-center text-coffee/55 mt-5">pesanan masuk ke kasir setelah pembayaran terkonfirmasi.</p>
    </div>
  @endif

  <form id="checkout-form" method="post" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-[1fr_340px] gap-6 mt-7">
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
          <label class="border border-coffee/15 rounded-2xl p-4 flex gap-3 {{ $qrisEnabled ? '' : 'opacity-50' }}">
            <input type="radio" class="accent-[#8A9A5B] mt-1" name="metode_bayar" value="QRIS" @checked($qrisEnabled && old('metode_bayar') === 'QRIS') @disabled(! $qrisEnabled)>
            <span><b class="block text-sm">QRIS</b><small class="block text-xs mt-1 text-coffee/60">{{ $qrisEnabled ? 'scan di sini, otomatis terkonfirmasi' : 'belum tersedia' }}</small></span>
          </label>
        </div>
        @unless ($qrisEnabled)
          <p class="text-xs text-coffee/55 mt-2">{{ setting('qris_info', 'Pembayaran QRIS dikonfirmasi oleh kasir.') }}</p>
        @endunless
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
      <button class="btn btn-coffee w-full mt-5" type="submit" id="checkout-submit" data-label-kasir="Buat Pesanan →" data-label-qris="Bayar dengan QRIS →">Buat Pesanan →</button>
      <p class="text-xs text-center text-coffee/55 mt-3" id="checkout-note" data-note-kasir="dengan melanjutkan, pesanan akan dikirim ke kasir." data-note-qris="pesanan masuk ke kasir setelah pembayaran QRIS terkonfirmasi.">dengan melanjutkan, pesanan akan dikirim ke kasir.</p>
    </aside>
  </form>
</section>
@endsection
@if ($qrisEnabled)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="{{ asset('js/payment.js') }}"></script>
<script>
(function () {
  const form = document.getElementById('checkout-form'), btn = document.getElementById('checkout-submit');
  const note = document.getElementById('checkout-note'), flash = document.getElementById('checkout-flash');
  const payBox = document.getElementById('checkout-pay');
  const method = () => form.querySelector('input[name="metode_bayar"]:checked')?.value;

  function syncLabels() {
    const q = method() === 'QRIS';
    btn.textContent = q ? btn.dataset.labelQris : btn.dataset.labelKasir;
    note.textContent = q ? note.dataset.noteQris : note.dataset.noteKasir;
  }
  form.querySelectorAll('input[name="metode_bayar"]').forEach(el => el.addEventListener('change', syncLabels));
  syncLabels();

  function showError(messages) {
    flash.innerHTML = '';
    const box = document.createElement('div');
    box.className = 'flash flash-error mb-4'; box.setAttribute('role', 'alert');
    messages.forEach(m => { const d = document.createElement('div'); d.textContent = m; box.appendChild(d); });
    flash.appendChild(box);
    flash.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  form.addEventListener('submit', async e => {
    if (method() !== 'QRIS') return; // "bayar di kasir" tetap kirim form biasa
    e.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true; btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true');
    try {
      const r = await fetch(form.action, {
        method: 'POST', body: new FormData(form),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const d = await r.json().catch(() => ({}));
      if (!r.ok) {
        const msgs = d.errors ? Object.values(d.errors).flat() : [d.message || 'pesanan gagal dibuat. coba lagi.'];
        showError(msgs);
        btn.disabled = false; btn.classList.remove('is-loading'); btn.removeAttribute('aria-busy'); syncLabels();
        return;
      }
      flash.innerHTML = '';
      form.classList.add('hidden');
      payBox.classList.remove('hidden');
      history.replaceState(null, '', d.pay_url); // refresh membuka halaman bayar untuk pesanan yang sama
      payBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
      KopiahPayment.mount(payBox.querySelector('[data-payment]'), {
        startUrl: d.start_url, statusUrl: d.status_url, amount: d.amount, secondsLeft: d.seconds_left,
      });
    } catch (err) {
      showError(['koneksi gagal. periksa internet lalu coba lagi.']);
      btn.disabled = false; btn.classList.remove('is-loading'); btn.removeAttribute('aria-busy'); syncLabels();
    }
  });
})();
</script>
@endpush
@endif
