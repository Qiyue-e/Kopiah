/*
 * Panel pembayaran QRIS (halaman checkout & halaman bayar).
 *   KopiahPayment.mount(rootEl, { startUrl, statusUrl, amount, secondsLeft })
 * Alur: minta server membuat QR (idempoten) -> gambar QR -> hitung mundur -> polling status tiap 3 detik
 * -> pindah otomatis ke halaman sukses saat server menyatakan lunas.
 */
(function () {
  const POLL_MS = 3000;
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');
  const mmss = s => String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');

  async function api(url, method) {
    const r = await fetch(url, {
      method,
      cache: 'no-store',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await r.json().catch(() => ({}));
    return { ok: r.ok, status: r.status, data };
  }

  function mount(root, cfg) {
    const states = [...root.querySelectorAll('[data-pay-state]')];
    const q = s => root.querySelector(s);
    const show = name => states.forEach(el => { el.hidden = el.dataset.payState !== name; });

    let deadline = 0, tickTimer = null, pollTimer = null, finished = false, qrDrawn = false;

    if (cfg.amount != null) q('[data-pay-amount]').textContent = rupiah(cfg.amount);

    function stop() {
      finished = true;
      clearInterval(tickTimer);
      clearInterval(pollTimer);
    }

    function finish(state, redirect) {
      stop();
      show(state);
      if (state === 'paid' && redirect) setTimeout(() => { location.href = redirect; }, 900);
    }

    function drawQr(text) {
      if (qrDrawn) return;
      const box = q('[data-pay-qr]');
      box.innerHTML = '';
      if (!window.QRCode) {
        box.textContent = 'QR tidak dapat ditampilkan. periksa koneksi internet lalu coba lagi.';
        return;
      }
      new QRCode(box, { text, width: 280, height: 280, colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
      qrDrawn = true;
    }

    function tick() {
      const left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
      q('[data-pay-countdown]').textContent = mmss(left);
      if (left === 0 && !finished) check(); // server yang memutuskan: bisa saja pembayaran masuk di detik terakhir
    }

    async function check() {
      if (finished) return;
      try {
        const { ok, data } = await api(cfg.statusUrl, 'GET');
        if (!ok || !data.ok) return;
        if (data.state === 'paid') finish('paid', data.redirect);
        else if (data.state === 'expired') finish('expired');
        else deadline = Date.now() + data.seconds_left * 1000; // sinkron dengan jam server
      } catch (e) { /* koneksi putus sesaat: coba lagi di putaran berikutnya */ }
    }

    async function start() {
      finished = false;
      show('loading');
      let res;
      try {
        res = await api(cfg.startUrl, 'POST');
      } catch (e) {
        q('[data-pay-error]').textContent = 'koneksi gagal. periksa internet lalu coba lagi.';
        return show('error');
      }
      const { ok, data } = res;
      if (!ok || !data.ok) {
        if (data.state === 'expired') return finish('expired');
        if (data.state === 'paid') return check();
        q('[data-pay-error]').textContent = data.message || 'QRIS belum bisa dibuat. coba lagi.';
        return show('error');
      }
      q('[data-pay-amount]').textContent = rupiah(data.amount);
      drawQr(data.qr_string);
      deadline = Date.now() + data.seconds_left * 1000;
      show('ready');
      tick();
      clearInterval(tickTimer); clearInterval(pollTimer);
      tickTimer = setInterval(tick, 1000);
      pollTimer = setInterval(check, POLL_MS);
    }

    // Kembali dari tab lain / layar HP menyala: cek status segera, jangan menunggu putaran berikutnya.
    document.addEventListener('visibilitychange', () => { if (!document.hidden) check(); });
    q('[data-pay-retry]').addEventListener('click', start);

    start();
  }

  window.KopiahPayment = { mount };
})();
