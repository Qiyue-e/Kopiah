</main>
<footer class="bg-coffee text-cream mt-16">
  <div class="max-w-7xl mx-auto px-4 py-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
<<<<<<< HEAD
    <div><div class="flex items-center gap-2 mb-3"><span class="brand-mark brand-mark-light">c</span><b class="font-serif text-2xl">Cafeyu</b></div><p class="text-sm text-cream/70"><?= h(get_setting('tagline','Ngopi santai, momen jadi berarti')) ?>.</p></div>
    <div><h3 class="font-semibold mb-3">Jelajahi</h3><div class="footer-links"><a href="<?= h(BASE_URL) ?>/menu.php">Menu kami</a><a href="<?= h(BASE_URL) ?>/moments.php">Cafeyu Moments</a><a href="<?= h(BASE_URL) ?>/about.php">Tentang kami</a></div></div>
    <div><h3 class="font-semibold mb-3">Jam buka</h3><p class="text-sm text-cream/70"><?= h(get_setting('opening_hours','Setiap hari, 08.00 - 22.00 WIB')) ?></p><p class="text-sm text-cream/70 mt-2"><?= h(get_setting('address','Depok, Jawa Barat')) ?></p></div>
    <div><h3 class="font-semibold mb-3">Terhubung</h3><div class="footer-links"><a href="https://instagram.com/<?= h(ltrim(get_setting('instagram','cafeyu.id'),'@')) ?>" target="_blank" rel="noopener">Instagram <?= h(get_setting('instagram','@cafeyu.id')) ?></a><a href="https://wa.me/<?= h(preg_replace('/\D/','',get_setting('whatsapp','6281234567890'))) ?>" target="_blank" rel="noopener">Chat WhatsApp</a></div></div>
  </div>
  <div class="border-t border-white/10 text-center py-4 text-xs text-cream/50">© <?= date('Y') ?> Cafeyu. Dibuat untuk momen yang berarti.</div>
=======
    <div><div class="flex items-center gap-2 mb-3"><img src="<?= h(BASE_URL) ?>/assets/img/logo-kopiah.png" class="w-10 h-10 object-contain rounded-lg bg-[#E9D6B6]" alt="Logo Kopiah"><b class="font-serif text-2xl">Kopiah</b></div><p class="text-sm text-cream/70"><?= h(get_setting('tagline','Ngopi santai, momen jadi berarti')) ?>.</p></div>
    <div><h3 class="font-semibold mb-3">Jelajahi</h3><div class="footer-links"><a href="<?= h(BASE_URL) ?>/menu.php">Menu kami</a><a href="<?= h(BASE_URL) ?>/moments.php">Kopiah Moments</a><a href="<?= h(BASE_URL) ?>/about.php">Tentang kami</a></div></div>
    <div><h3 class="font-semibold mb-3">Jam buka</h3><p class="text-sm text-cream/70"><?= h(get_setting('opening_hours','Setiap hari, 08.00 - 22.00 WIB')) ?></p><p class="text-sm text-cream/70 mt-2"><?= h(get_setting('address','Depok, Jawa Barat')) ?></p></div>
    <div><h3 class="font-semibold mb-3">Terhubung</h3><div class="footer-links"><a href="https://instagram.com/<?= h(ltrim(get_setting('instagram','kopiah.id'),'@')) ?>" target="_blank" rel="noopener">Instagram <?= h(get_setting('instagram','@kopiah.id')) ?></a><a href="https://wa.me/<?= h(preg_replace('/\D/','',get_setting('whatsapp','6281234567890'))) ?>" target="_blank" rel="noopener">Chat WhatsApp</a></div></div>
  </div>
  <div class="border-t border-white/10 text-center py-4 text-xs text-cream/50">© <?= date('Y') ?> Kopiah. Dibuat untuk momen yang berarti.</div>
>>>>>>> 0119df2 (ubah nama website)
</footer>
</body></html>
