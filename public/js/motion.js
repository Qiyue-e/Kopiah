/* Animasi scroll halaman pelanggan: Lenis (scroll halus) + GSAP ScrollTrigger (reveal per kata) + Motion (kartu, cover).
   - Diinisialisasi sekali di layout pelanggan; dibersihkan saat halaman ditinggalkan. Tidak dimuat di panel kasir/owner.
   - Hanya transform dan opacity. prefers-reduced-motion, atau CDN gagal dimuat: konten tampil apa adanya.
   - API ulang pakai: Kopiah.splitReveal(selector), atau atribut data-stagger / data-reveal di markup. */
(function () {
  'use strict';
  const html = document.documentElement;
  const reduce = matchMedia('(prefers-reduced-motion: reduce)');
  const ready = () => window.gsap && window.ScrollTrigger && window.Lenis && window.Motion;
  const done = () => html.classList.remove('motion-pending'); // tampilkan konten yang tadinya disembunyikan menunggu skrip

  let ctx = null, lenis = null, tick = null, stops = [], active = false;

  const esc = s => s.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const visible = el => el.getClientRects().length > 0;

  /* Pecah teks heading jadi kata; tiap kata dibungkus masker agar naik dari bawah garis */
  function splitWords(el) {
    if (el.dataset.split) return el.querySelectorAll('.sw-i');
    if (el.children.length) return null; // berisi elemen lain: biarkan utuh
    const text = el.textContent.trim();
    if (!text) return null;
    el.setAttribute('aria-label', text);
    el.dataset.split = '1';
    el.innerHTML = text.split(/\s+/).map(w => '<span class="sw" aria-hidden="true"><span class="sw-i">' + esc(w) + '</span></span>').join(' ');
    return el.querySelectorAll('.sw-i');
  }

  /* Hook yang bisa dipakai ulang: reveal per kata, sekali, saat heading masuk 85% viewport */
  function splitReveal(selector, scope) {
    (scope || document).querySelectorAll(selector).forEach(el => {
      if (el.hasAttribute('data-no-split') || !visible(el)) return;
      const words = splitWords(el);
      if (!words) return;
      gsap.from(words, {
        yPercent: 110, opacity: 0, duration: 0.7, ease: 'power3.out', stagger: 0.04,
        scrollTrigger: { trigger: el, start: 'top 85%', once: true },
      });
    });
  }

  /* Kartu: fade + naik bertahap saat grup masuk layar; cover gambar fade + skala. Sekali saja. */
  function revealGroups() {
    const { animate, inView, stagger } = Motion;
    const ease = [0.2, 0.7, 0.2, 1];
    const hide = (els) => animate(els, { opacity: 0, transform: 'translateY(16px)' }, { duration: 0 });
    const covers = (host) => host.querySelectorAll('.menu-photo, .lk-photo');

    document.querySelectorAll('[data-stagger]').forEach(group => {
      const items = Array.from(group.children);
      if (!items.length) return;
      hide(items);
      covers(group).forEach(c => animate(c, { opacity: 0, transform: 'scale(1.06)' }, { duration: 0 }));
      const stop = inView(group, () => {
        animate(items, { opacity: 1, transform: 'translateY(0px)' }, { duration: 0.5, ease, delay: stagger(0.06) });
        covers(group).forEach((c, i) => animate(c, { opacity: 1, transform: 'scale(1)' }, { duration: 0.7, ease, delay: 0.1 + i * 0.06 }));
        stop();
      }, { margin: '0px 0px -12% 0px' });
      stops.push(stop);
    });

    document.querySelectorAll('[data-reveal]').forEach(el => {
      hide([el]);
      const stop = inView(el, () => { animate(el, { opacity: 1, transform: 'translateY(0px)' }, { duration: 0.6, ease }); stop(); }, { margin: '0px 0px -10% 0px' });
      stops.push(stop);
    });
  }

  function init() {
    if (active) return;
    if (reduce.matches || !ready()) return done();
    active = true;
    gsap.registerPlugin(ScrollTrigger);

    lenis = new Lenis({ duration: 1.1, easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)), smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    tick = time => lenis.raf(time * 1000);
    gsap.ticker.add(tick);
    gsap.ticker.lagSmoothing(0);

    ctx = gsap.context(() => { splitReveal('main h1, main h2'); });
    revealGroups();
    done();
    requestAnimationFrame(() => ScrollTrigger.refresh());
  }

  function destroy() {
    if (!active) return;
    active = false;
    stops.forEach(s => { try { s(); } catch (e) {} }); stops = [];
    if (ctx) { ctx.revert(); ctx = null; }
    if (tick) { gsap.ticker.remove(tick); tick = null; }
    if (lenis) { lenis.destroy(); lenis = null; }
    document.querySelectorAll('[data-stagger] > *, [data-reveal], .menu-photo, .lk-photo').forEach(el => { el.style.opacity = ''; el.style.transform = ''; });
  }

  window.Kopiah = Object.assign(window.Kopiah || {}, { splitReveal });

  const start = () => (document.fonts && document.fonts.ready ? document.fonts.ready.then(init) : init());
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', start) : start();
  window.addEventListener('pagehide', destroy);
  window.addEventListener('pageshow', e => { if (e.persisted) init(); });
  reduce.addEventListener('change', () => (reduce.matches ? (destroy(), done()) : init()));
})();
