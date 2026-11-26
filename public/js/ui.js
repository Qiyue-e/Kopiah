/* Interaksi UI tanpa dependensi (halaman pelanggan): tombol loading, skeleton gambar, helper skeleton untuk JS.
   Tidak dimuat di panel kasir/owner. Semua animasi dimatikan lewat CSS bila prefers-reduced-motion. */
(function () {
  'use strict';
  const root = document.documentElement;
  root.classList.add('js-ui');

  /* Helper skeleton untuk markup yang dibuat JS: Kopiah.skeleton('card', 3) */
  const sk = {
    line: (w) => '<span class="skeleton sk-line"' + (w ? ' style="width:' + w + '"' : '') + '></span>',
    card: () => '<div class="card sk-card" role="status" aria-label="Memuat"><span class="skeleton sk-block" style="aspect-ratio:4/3"></span><div class="p-4"><span class="skeleton sk-line" style="width:40%"></span><span class="skeleton sk-line mt-3" style="height:16px;width:75%"></span><span class="skeleton sk-line mt-3"></span></div></div>',
  };
  window.Kopiah = Object.assign(window.Kopiah || {}, { skeleton: (kind, n) => Array.from({ length: n || 1 }, () => (sk[kind] || sk.line)()).join('') });

  /* Tombol loading: form biasa yang sedang dikirim menampilkan shimmer di tombol (bukan spinner) */
  const clearLoading = () => document.querySelectorAll('.btn.is-loading').forEach(b => { b.classList.remove('is-loading'); b.removeAttribute('aria-busy'); });
  document.addEventListener('submit', e => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading')) return;
    setTimeout(() => { // setelah handler lain berjalan: jangan tandai jika pengiriman dibatalkan
      if (e.defaultPrevented) return;
      const btn = e.submitter || form.querySelector('[type=submit]');
      if (btn && btn.classList.contains('btn')) { btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true'); }
    }, 0);
  });
  window.addEventListener('pageshow', clearLoading); // kembali lewat tombol back: jangan terjebak loading

  /* Skeleton gambar: shimmer di balik foto sampai gambar selesai dimuat */
  function watchImages() {
    document.querySelectorAll('.menu-card a > img, .lk-photo').forEach(img => {
      const host = img.parentElement;
      if (img.complete && img.naturalWidth) return;
      host.classList.add('img-wait');
      const done = () => host.classList.remove('img-wait');
      img.addEventListener('load', done, { once: true });
      img.addEventListener('error', done, { once: true });
    });
  }
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', watchImages) : watchImages();
})();
