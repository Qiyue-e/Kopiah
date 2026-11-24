/* Kopiah Moments — kamera persegi gaya Locket, caption overlay, feed/riwayat, reaksi emoji. */
(() => {
  const app = document.getElementById('lkApp');
  if (!app) return;

  const $ = (id) => document.getElementById(id);
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const el = {
    video: $('lkVideo'), canvas: $('lkCanvas'), idle: $('lkIdle'), capWrap: $('lkCaptionWrap'), cap: $('lkCaption'),
    shutter: $('lkShutter'), left: $('lkLeft'), right: $('lkRight'), filter: $('lkFilter'), msg: $('lkMsg'),
    list: $('lkList'), pill: $('lkNewPill'), modeLabel: $('lkModeLabel'),
    profileBtn: $('lkProfileBtn'), dlg: $('lkProfile'), name: $('lkName'), avatars: $('lkAvatars'),
  };

  const FILTERS = [
    ['normal', 'Normal', 'none', ''],
    ['warm', 'Hangat', 'sepia(.22) saturate(1.15)', 'warm'],
    ['bw', 'B&W', 'grayscale(1)', 'bw'],
  ];
  let stream = null, facing = 'user', filterIdx = 0, state = 'idle', lastId = Number(app.dataset.lastId || 0);
  let profile = {nama: '', avatar: '☕'};
  try { profile = Object.assign(profile, JSON.parse(localStorage.getItem('kopiah_profile') || '{}')); } catch (e) {}

  const say = (t, err = false) => { el.msg.textContent = t; el.msg.style.color = err ? '#b91c1c' : ''; };

  /* ---------- state: idle → live → shot → sending ---------- */
  function setState(s) {
    state = s;
    el.video.classList.toggle('hidden', s !== 'live');
    el.canvas.classList.toggle('hidden', s !== 'shot' && s !== 'sending');
    el.idle.classList.toggle('hidden', s !== 'idle');
    el.filter.classList.toggle('hidden', s !== 'live');
    el.capWrap.classList.toggle('hidden', s !== 'shot' && s !== 'sending');
    el.shutter.classList.toggle('is-send', s === 'shot' || s === 'sending');
    el.shutter.firstElementChild.textContent = (s === 'shot' || s === 'sending') ? '➤' : '';
    el.shutter.disabled = s === 'sending';
    el.shutter.setAttribute('aria-label', s === 'shot' ? 'Kirim momen' : 'Ambil foto');
    el.left.textContent = s === 'shot' ? '✕' : (el.list.classList.contains('lk-mode-grid') ? '☰' : '▦');
    el.left.setAttribute('aria-label', s === 'shot' ? 'Ulangi foto' : 'Ganti tampilan feed / riwayat');
    el.right.classList.toggle('lk-invisible', s === 'shot' || s === 'sending');
  }

  async function startCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      say('browser ini belum mendukung kamera. coba Chrome / Safari terbaru dengan HTTPS.', true); return;
    }
    stopCamera();
    try {
      stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: facing}, width: {ideal: 1440}, height: {ideal: 1440}}, audio: false});
      el.video.srcObject = stream;
      el.video.classList.toggle('mirror', facing === 'user');
      applyFilterPreview();
      await el.video.play();
      setState('live'); say('');
    } catch (e) {
      setState('idle');
      say(e && e.name === 'NotAllowedError' ? 'izin kamera ditolak. aktifkan izin kamera di pengaturan browser.' : 'kamera tidak bisa dibuka. pastikan situs dibuka lewat HTTPS atau localhost.', true);
    }
  }
  function stopCamera() { if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; } }

  function applyFilterPreview() { el.video.style.filter = FILTERS[filterIdx][2]; el.filter.textContent = FILTERS[filterIdx][1]; }

  /* ---------- ambil foto: crop persegi tengah, mirror untuk kamera depan ---------- */
  function capture() {
    const vw = el.video.videoWidth, vh = el.video.videoHeight;
    if (!vw || !vh) { say('kamera belum siap, coba lagi.', true); return; }
    const side = Math.min(vw, vh), sx = (vw - side) / 2, sy = (vh - side) / 2, out = Math.min(1080, side);
    el.canvas.width = out; el.canvas.height = out;
    const ctx = el.canvas.getContext('2d');
    const native = typeof ctx.filter === 'string';
    ctx.save();
    if (native) ctx.filter = FILTERS[filterIdx][2];
    if (facing === 'user') { ctx.translate(out, 0); ctx.scale(-1, 1); }
    ctx.drawImage(el.video, sx, sy, side, side, 0, 0, out, out);
    ctx.restore();
    if (!native && filterIdx > 0) manualFilter(ctx, out, FILTERS[filterIdx][3]); // fallback Safari
    stopCamera();
    el.cap.value = '';
    setState('shot'); say('tambahkan caption lalu tekan ➤ untuk kirim.');
    setTimeout(() => el.cap.focus({preventScroll: true}), 50);
  }
  function manualFilter(ctx, size, kind) {
    const img = ctx.getImageData(0, 0, size, size), d = img.data;
    for (let i = 0; i < d.length; i += 4) {
      if (kind === 'bw') { const g = .3 * d[i] + .59 * d[i + 1] + .11 * d[i + 2]; d[i] = d[i + 1] = d[i + 2] = g; }
      else { d[i] = Math.min(255, d[i] * 1.08); d[i + 2] = d[i + 2] * .9; }
    }
    ctx.putImageData(img, 0, 0);
  }

  /* ---------- kirim ---------- */
  async function send() {
    if (!profile.nama) { say('beri nama panggilan dulu ya, lalu tekan ➤ lagi.'); openProfile(); return; }
    setState('sending'); say('mengirim…');
    try {
      const blob = await new Promise(r => el.canvas.toBlob(r, 'image/jpeg', .86));
      if (!blob) throw new Error('foto gagal disiapkan. coba ambil ulang ya.');
      const fd = new FormData();
      fd.append('foto', blob, 'moment.jpg');
      fd.append('caption', el.cap.value.trim());
      fd.append('nama', profile.nama);
      fd.append('avatar', profile.avatar);
      fd.append('type', facing === 'user' ? 'Selfie' : 'Makanan');
      const r = await fetch(app.dataset.storeUrl, {method: 'POST', body: fd, headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}});
      const d = await r.json().catch(() => ({}));
      if (!r.ok || !d.ok) {
        const first = d.errors ? Object.values(d.errors)[0][0] : null;
        throw new Error(d.message || first || 'gagal mengirim momen.');
      }
      sessionStorage.setItem('lkSent', d.message);
      location.reload();
    } catch (e) {
      setState('shot'); say(e.message || 'koneksi gagal, coba lagi.', true);
    }
  }

  /* ---------- tombol kontrol ---------- */
  el.idle.addEventListener('click', startCamera);
  el.shutter.addEventListener('click', () => { if (state === 'idle') startCamera(); else if (state === 'live') capture(); else if (state === 'shot') send(); });
  el.right.addEventListener('click', () => { facing = facing === 'user' ? 'environment' : 'user'; if (state === 'live') startCamera(); else say('kamera ' + (facing === 'user' ? 'depan' : 'belakang') + ' dipilih.'); });
  el.filter.addEventListener('click', () => { filterIdx = (filterIdx + 1) % FILTERS.length; applyFilterPreview(); });
  el.left.addEventListener('click', () => {
    if (state === 'shot') { setState('idle'); say(''); startCamera(); return; }
    const grid = el.list.classList.toggle('lk-mode-grid');
    el.list.classList.toggle('lk-mode-feed', !grid);
    el.modeLabel.textContent = grid ? 'riwayat' : 'feed';
    el.left.textContent = grid ? '☰' : '▦';
  });
  window.addEventListener('pagehide', stopCamera);

  /* ---------- feed: reaksi, hapus, buka dari grid ---------- */
  el.list.addEventListener('click', async (e) => {
    const card = e.target.closest('.lk-card'); if (!card) return;
    if (el.list.classList.contains('lk-mode-grid') && e.target.closest('.lk-photo-wrap')) {
      el.list.classList.replace('lk-mode-grid', 'lk-mode-feed'); el.modeLabel.textContent = 'feed'; el.left.textContent = '▦';
      card.scrollIntoView({behavior: 'smooth', block: 'center'}); return;
    }
    const react = e.target.closest('[data-emoji]');
    if (react && !react.disabled) {
      try {
        const r = await fetch(app.dataset.baseUrl + '/' + card.dataset.id + '/reaksi', {
          method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
          body: JSON.stringify({emoji: react.dataset.emoji}),
        });
        const d = await r.json();
        if (!d.ok) return;
        card.querySelectorAll('[data-emoji]').forEach(b => {
          b.classList.toggle('is-on', d.mine === b.dataset.emoji);
          b.querySelector('b').textContent = d.counts[b.dataset.emoji] || '';
        });
      } catch (err) {}
      return;
    }
    if (e.target.closest('[data-delete]') && confirm('Hapus momen ini?')) {
      const r = await fetch(app.dataset.baseUrl + '/' + card.dataset.id, {method: 'DELETE', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}});
      if (r.ok) card.remove();
    }
  });

  /* ---------- "momen baru" ---------- */
  async function pollNew() {
    try {
      const r = await fetch(app.dataset.countUrl + '?after=' + lastId, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
      const d = await r.json();
      if (d.count > 0) { el.pill.textContent = '↑ ' + d.count + ' momen baru'; el.pill.classList.remove('hidden'); }
    } catch (e) {}
  }
  el.pill.addEventListener('click', () => location.reload());
  setInterval(pollNew, 20000);

  /* ---------- profil (tanpa akun, disimpan di perangkat) ---------- */
  function paintProfile() {
    el.profileBtn.textContent = profile.avatar;
    el.avatars.querySelectorAll('[data-avatar]').forEach(b => b.classList.toggle('is-picked', b.dataset.avatar === profile.avatar));
  }
  function openProfile() { el.name.value = profile.nama; paintProfile(); el.dlg.showModal(); }
  el.profileBtn.addEventListener('click', openProfile);
  el.avatars.addEventListener('click', (e) => { const b = e.target.closest('[data-avatar]'); if (b) { profile.avatar = b.dataset.avatar; paintProfile(); } });
  $('lkProfileForm').addEventListener('submit', () => {
    profile.nama = el.name.value.trim();
    localStorage.setItem('kopiah_profile', JSON.stringify(profile));
    paintProfile();
  });

  /* ---------- init ---------- */
  paintProfile(); setState('idle');
  const sent = sessionStorage.getItem('lkSent');
  if (sent) { sessionStorage.removeItem('lkSent'); say(sent); el.list.scrollIntoView({behavior: 'smooth'}); }
  if (location.hash.startsWith('#moment-')) { const t = document.querySelector(location.hash); if (t) t.scrollIntoView({block: 'center'}); }
})();
