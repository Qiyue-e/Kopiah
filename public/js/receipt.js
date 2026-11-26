/*
 * Struk: pilih lebar kertas 58/80mm, cetak (CSS @page mengikuti lebar), dan unduh PDF.
 * Elemen yang dipakai: #paperSize, #receipt, #receiptPrint, #receiptPdf (semua opsional).
 */
(function () {
  const paper = document.getElementById('paperSize');
  const receipt = document.getElementById('receipt');
  if (!paper || !receipt) return;

  const MM_PER_PX = 25.4 / 96;

  function setPaper() {
    const mm = paper.value;
    receipt.style.width = mm + 'mm';
    receipt.style.maxWidth = mm + 'mm';
    let s = document.getElementById('printPageStyle');
    if (!s) { s = document.createElement('style'); s.id = 'printPageStyle'; document.head.appendChild(s); }
    s.textContent = '@media print{@page{size:' + mm + 'mm auto;margin:2mm}.receipt{width:' + mm + 'mm!important;max-width:' + mm + 'mm!important;margin:0 auto!important;padding:2mm!important}.receipt-page{padding:0!important;background:white!important}}';
  }
  paper.addEventListener('change', setPaper);
  setPaper();

  document.getElementById('receiptPrint')?.addEventListener('click', () => window.print());

  const pdfBtn = document.getElementById('receiptPdf');
  pdfBtn?.addEventListener('click', async () => {
    if (!window.html2pdf) { alert('PDF membutuhkan koneksi internet. gunakan tombol Cetak lalu pilih "Simpan sebagai PDF".'); return; }
    const mm = Number(paper.value);
    const heightMm = Math.ceil(receipt.offsetHeight * MM_PER_PX) + 4;
    const label = pdfBtn.textContent;
    pdfBtn.disabled = true; pdfBtn.classList.add('is-loading'); pdfBtn.setAttribute('aria-busy', 'true');
    try {
      await html2pdf().set({
        margin: 0,
        filename: pdfBtn.dataset.filename || 'struk.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF: { unit: 'mm', format: [mm, heightMm], orientation: 'portrait' },
      }).from(receipt).save();
    } catch (e) {
      alert('PDF gagal dibuat. coba tombol Cetak lalu pilih "Simpan sebagai PDF".');
    } finally {
      pdfBtn.disabled = false; pdfBtn.classList.remove('is-loading'); pdfBtn.removeAttribute('aria-busy'); pdfBtn.textContent = label;
    }
  });
})();
