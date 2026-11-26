/*
 * Notifikasi pesanan baru di panel kasir (semua halaman kasir).
 * - Polling tiap 3 detik (10 detik saat tab tersembunyi); bila gagal, jeda bertahap 6-30 detik
 *   dan langsung mencoba lagi saat internet kembali / tab dibuka.
 *   Polling dipilih ketimbang SSE karena server PHP (artisan serve / XAMPP) memegang satu proses per koneksi terbuka.
 * - Toast berisi nomor antrean, item, dan total; badge jumlah pesanan berstatus "Diterima".
 * - Suara: browser memblokir autoplay, jadi kasir menekan "Aktifkan suara" sekali. Di halaman berikutnya
 *   suara menyala lagi pada klik/tombol pertama mana pun. Bisa dibisukan kapan saja.
 * Event untuk halaman lain: window 'kasir:poll' dengan detail { data, fresh }.
 */
(function () {
  const root = document.getElementById('kn-root');
  if (!root) return;

  const POLL_URL = root.dataset.pollUrl;
  const ORDERS_URL = root.dataset.ordersUrl;
  const $ = id => document.getElementById(id);
  const rupiah = n => 'Rp' + Number(n).toLocaleString('id-ID');

  const ls = {
    get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* mode privat: abaikan */ } },
  };
  const ss = {
    get(k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { sessionStorage.setItem(k, v); } catch (e) { /* abaikan */ } },
  };

  /* ---------- Suara ---------- */
  const soundBtn = $('kn-sound');
  let pref = ls.get('kopiah_sound'); // null | 'on' | 'muted'
  let ctx = null;

  function ensureCtx() {
    if (!ctx) {
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return null;
      ctx = new AC();
    }
    return ctx;
  }
  const unlocked = () => !!ctx && ctx.state === 'running';

  function chime() {
    if (pref !== 'on' || !unlocked()) return;
    const t0 = ctx.currentTime;
    [[880, 0], [1318.5, 0.18]].forEach(([freq, delay]) => {
      const o = ctx.createOscillator(), g = ctx.createGain();
      o.type = 'sine'; o.frequency.value = freq;
      g.gain.setValueAtTime(0.0001, t0 + delay);
      g.gain.exponentialRampToValueAtTime(0.2, t0 + delay + 0.02);
      g.gain.exponentialRampToValueAtTime(0.0001, t0 + delay + 0.5);
      o.connect(g); g.connect(ctx.destination);
      o.start(t0 + delay); o.stop(t0 + delay + 0.55);
    });
  }

  function renderSound() {
    let label, short, needsAction = true;
    if (pref === null) { label = 'Aktifkan suara'; short = 'Suara'; }
    else if (pref === 'muted') { label = 'Suara dibisukan · aktifkan'; short = 'Bisu'; }
    else if (!unlocked()) { label = 'Ketuk untuk menyalakan suara'; short = 'Ketuk'; }
    else { label = 'Suara aktif · bisukan'; short = 'Aktif'; needsAction = false; }
    soundBtn.querySelector('[data-full]').textContent = label;
    soundBtn.querySelector('[data-short]').textContent = short;
    soundBtn.classList.toggle('btn-coffee', needsAction);
    soundBtn.classList.toggle('btn-light', !needsAction);
    soundBtn.setAttribute('aria-pressed', String(pref === 'on' && unlocked()));
    soundBtn.title = label;
  }

  function tryUnlock() {
    if (pref !== 'on' || unlocked()) return;
    const c = ensureCtx();
    if (c) c.resume().then(() => { renderSound(); if (unlocked()) detachUnlock(); }).catch(() => {});
  }
  const unlockEvents = ['pointerdown', 'keydown'];
  const detachUnlock = () => unlockEvents.forEach(ev => document.removeEventListener(ev, tryUnlock, true));
  if (pref === 'on') unlockEvents.forEach(ev => document.addEventListener(ev, tryUnlock, true));

  soundBtn.addEventListener('click', async () => {
    if (pref === 'on' && unlocked()) { pref = 'muted'; ls.set('kopiah_sound', pref); renderSound(); return; }
    const c = ensureCtx();
    if (!c) { alert('browser ini tidak mendukung suara otomatis.'); return; }
    try { await c.resume(); } catch (e) { /* ditangani di bawah */ }
    pref = 'on'; ls.set('kopiah_sound', pref);
    renderSound();
    chime(); // contoh bunyi supaya kasir tahu suaranya sudah menyala
  });
  renderSound();

  /* ---------- Status koneksi, badge, judul tab ---------- */
  const dot = $('kn-dot'), statusText = $('kn-status-text'), badge = $('kn-badge');
  const baseTitle = document.title;
  const STATUS = {
    ok: 'Terhubung',
    down: 'Terputus, menyambung ulang...',
    auth: 'Sesi berakhir, masuk lagi',
  };
  function setStatus(state) {
    if (dot.dataset.state === state) return;
    dot.dataset.state = state;
    statusText.textContent = STATUS[state];
  }
  function setBadge(n) {
    if (badge) {
      badge.hidden = n <= 0;
      badge.textContent = n > 99 ? '99+' : String(n);
      badge.setAttribute('aria-label', n + ' pesanan baru');
    }
    document.title = (n > 0 ? '(' + n + ') ' : '') + baseTitle;
  }

  /* ---------- Toast ---------- */
  const stack = $('kn-toasts');
  function dismiss(el) {
    if (!el.isConnected) return;
    el.classList.remove('kn-in'); el.classList.add('kn-out');
    setTimeout(() => el.remove(), 260);
  }
  function el(tag, cls, text) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text; // textContent: nama pelanggan/menu tidak pernah dianggap HTML
    return n;
  }
  function toast(order) {
    const t = el('div', 'kn-toast'); t.setAttribute('role', 'status');

    const num = el('div', 'kn-toast-num');
    num.appendChild(el('span', 'text-[10px] text-coffee/55', 'antrean'));
    num.appendChild(el('span', 'queue-number font-bold text-4xl', order.antrean || '–'));

    const body = el('div', 'min-w-0 flex-1');
    body.appendChild(el('p', 'font-semibold text-sm', 'Pesanan baru masuk'));
    body.appendChild(el('p', 'text-xs text-coffee/65 mt-1', order.nama + ' · ' + order.meja));
    const list = el('ul', 'text-xs mt-2 space-y-1');
    order.items.slice(0, 3).forEach(i => list.appendChild(el('li', 'truncate', i.qty + '× ' + i.nama)));
    if (order.items.length > 3) list.appendChild(el('li', 'text-coffee/55', '+' + (order.items.length - 3) + ' item lagi'));
    body.appendChild(list);
    const foot = el('div', 'flex items-center justify-between gap-3 mt-3');
    foot.appendChild(el('span', 'font-semibold text-sm tabular-nums', rupiah(order.total)));
    foot.appendChild(el('span', 'text-[11px] text-coffee/65', order.lunas ? 'Lunas · ' + order.metode : 'Bayar di kasir'));
    body.appendChild(foot);
    const open = el('a', 'underline text-xs mt-2 inline-block', 'Buka pesanan');
    open.href = ORDERS_URL;
    body.appendChild(open);

    mount(t, [num, body]);
  }

  /* Banyak pesanan sekaligus (mis. kasir baru membuka tab setelah lama): satu ringkasan saja. */
  function summaryToast(n) {
    const t = el('div', 'kn-toast'); t.setAttribute('role', 'status');
    const body = el('div', 'min-w-0 flex-1');
    body.appendChild(el('p', 'font-semibold text-sm', n + ' pesanan baru masuk'));
    const open = el('a', 'underline text-xs mt-2 inline-block', 'Buka daftar pesanan');
    open.href = ORDERS_URL;
    body.appendChild(open);
    mount(t, [body]);
  }

  function mount(t, children) {
    const close = el('button', 'kn-toast-close', '×');
    close.type = 'button'; close.setAttribute('aria-label', 'Tutup notifikasi');
    close.addEventListener('click', () => dismiss(t));

    t.append(...children, close);
    stack.appendChild(t);
    requestAnimationFrame(() => t.classList.add('kn-in'));

    let timer = setTimeout(() => dismiss(t), 12000);
    const pause = () => clearTimeout(timer);
    const resume = () => { timer = setTimeout(() => dismiss(t), 4000); };
    t.addEventListener('pointerenter', pause); t.addEventListener('pointerleave', resume);
    t.addEventListener('focusin', pause); t.addEventListener('focusout', resume);
  }

  /* ---------- Polling ---------- */
  let seen = null; // null = belum ada baseline: pesanan yang sudah ada tidak dibunyikan
  try { const raw = ss.get('kopiah_kn_seen'); if (raw) seen = new Set(JSON.parse(raw)); } catch (e) { seen = null; }

  let failures = 0, timer = null, inFlight = false, stopped = false;

  function handle(data) {
    const ids = data.ids || [];
    let fresh = [];
    if (seen === null) {
      seen = new Set(ids);
    } else {
      fresh = (data.orders || []).filter(o => !seen.has(o.id)).reverse(); // yang paling lama dulu
      seen = new Set(ids);
    }
    ss.set('kopiah_kn_seen', JSON.stringify([...seen]));

    setBadge(data.new_count || 0);

    if (fresh.length) {
      chime();
      if (fresh.length > 3) summaryToast(fresh.length);
      else fresh.forEach(toast);
    }
    window.dispatchEvent(new CustomEvent('kasir:poll', { detail: { data, fresh } }));
  }

  async function poll() {
    if (inFlight || stopped) return;
    inFlight = true;
    try {
      const r = await fetch(POLL_URL, {
        cache: 'no-store', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const isJson = (r.headers.get('content-type') || '').includes('json');
      if (r.status === 401 || r.status === 419 || (r.redirected && !isJson)) { // sesi habis: dialihkan ke halaman login
        stopped = true; setStatus('auth'); return;
      }
      if (!r.ok || !isJson) throw new Error('HTTP ' + r.status);
      handle(await r.json());
      failures = 0; setStatus('ok');
    } catch (e) {
      failures++; setStatus('down');
    } finally {
      inFlight = false;
      schedule();
    }
  }

  function schedule() {
    clearTimeout(timer);
    if (stopped) return;
    const delay = failures ? Math.min(30000, 3000 * 2 ** Math.min(failures, 4)) : (document.hidden ? 10000 : 3000);
    timer = setTimeout(poll, delay);
  }

  window.addEventListener('online', () => { failures = 0; clearTimeout(timer); poll(); });
  document.addEventListener('visibilitychange', () => { if (!document.hidden) { clearTimeout(timer); poll(); } });

  poll();
})();
