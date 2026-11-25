{{-- Bilah aksi (lebar kertas, unduh PDF, cetak) + struk. $backUrl/$backLabel opsional. --}}
<div class="no-print flex flex-wrap items-center justify-between gap-3 mb-5">
  <div><p class="text-sm text-coffee/60">struk digital</p><p class="text-xs text-coffee/45 mt-1">pilih lebar kertas lalu unduh PDF atau cetak.</p></div>
  <div class="flex flex-wrap gap-2">
    <select id="paperSize" class="field !w-44" aria-label="Lebar kertas"><option value="58">Thermal 58mm</option><option value="80" selected>Thermal 80mm</option></select>
    <button class="btn btn-light" id="receiptPdf" type="button" data-filename="struk-{{ $order->kode_pesanan }}.pdf">Unduh PDF</button>
    <button class="btn btn-coffee" id="receiptPrint" type="button">Cetak</button>
    @isset($backUrl)<a class="btn btn-light" href="{{ $backUrl }}">{{ $backLabel ?? 'Kembali' }}</a>@endisset
  </div>
</div>
<div class="receipt-page rounded-2xl bg-[#eee5d9] py-8">
  @include('receipt._body', ['order' => $order])
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="{{ asset('js/receipt.js') }}"></script>
@endpush
