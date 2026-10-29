<?php
<<<<<<< HEAD
$siteName = get_setting('cafe_name', 'Cafeyu');
$siteLogo = get_setting('logo_path', '');
=======
$siteName = get_setting('cafe_name', 'Kopiah');
$siteLogo = get_setting('logo_path', 'assets/img/logo-kopiah.png') ?: 'assets/img/logo-kopiah.png';
>>>>>>> 0119df2 (ubah nama website)
$pageTitle = $pageTitle ?? $siteName;
$user = current_user();
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
  <meta name="theme-color" content="#4B2E1E"><meta name="description" content="Cafeyu — Ngopi santai, momen jadi berarti.">
=======
  <meta name="theme-color" content="#4B2E1E"><meta name="description" content="Kopiah — Ngopi santai, momen jadi berarti.">
>>>>>>> 0119df2 (ubah nama website)
  <title><?= h($pageTitle) ?> | <?= h($siteName) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{coffee:'#4B2E1E',cream:'#F5E9DA',sage:'#8A9A5B',caramel:'#D98E32'},fontFamily:{sans:['Poppins','sans-serif'],serif:['Playfair Display','serif']}}}}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= h(BASE_URL) ?>/assets/css/style.css">
  <script defer src="<?= h(BASE_URL) ?>/assets/js/app.js"></script>
</head>
<body class="bg-cream text-coffee font-sans min-h-screen flex flex-col">
<header class="sticky top-0 z-40 bg-cream/95 backdrop-blur border-b border-coffee/10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
<<<<<<< HEAD
    <a href="<?= h(BASE_URL) ?>/index.php" class="flex items-center gap-2.5 shrink-0"><?php if ($siteLogo): ?><img src="<?= h(BASE_URL . '/' . $siteLogo) ?>" class="w-10 h-10 object-contain rounded-xl" alt="Logo Cafeyu"><?php else: ?><span class="brand-mark">c</span><?php endif; ?><span><b class="font-serif text-xl leading-none">Cafeyu</b><small class="block text-[10px] tracking-wide text-coffee/60">NGOPI &amp; MOMEN</small></span></a>
    <button class="md:hidden rounded-xl border border-coffee/15 p-2" type="button" aria-label="Buka navigasi" data-menu-toggle><span class="text-xl">☰</span></button>
    <nav class="hidden md:flex items-center gap-5 text-sm font-medium" data-main-nav>
      <a class="nav-link" href="<?= h(BASE_URL) ?>/index.php">Beranda</a><a class="nav-link" href="<?= h(BASE_URL) ?>/menu.php">Menu</a><a class="nav-link" href="<?= h(BASE_URL) ?>/moments.php">Cafeyu Moments</a><a class="nav-link" href="<?= h(BASE_URL) ?>/about.php">Tentang</a>
=======
    <a href="<?= h(BASE_URL) ?>/index.php" class="flex items-center gap-2.5 shrink-0"><?php if ($siteLogo): ?><img src="<?= h(BASE_URL . '/' . $siteLogo) ?>" class="w-12 h-12 object-contain rounded-xl bg-[#E9D6B6]" alt="Logo Kopiah"><?php else: ?><img src="<?= h(BASE_URL) ?>/assets/img/logo-kopiah.png" class="w-12 h-12 object-contain rounded-xl bg-[#E9D6B6]" alt="Logo Kopiah"><?php endif; ?><span><b class="font-serif text-xl leading-none">Kopiah</b><small class="block text-[10px] tracking-wide text-coffee/60">NGOPI &amp; MOMEN</small></span></a>
    <button class="md:hidden rounded-xl border border-coffee/15 p-2" type="button" aria-label="Buka navigasi" data-menu-toggle><span class="text-xl">☰</span></button>
    <nav class="hidden md:flex items-center gap-5 text-sm font-medium" data-main-nav>
      <a class="nav-link" href="<?= h(BASE_URL) ?>/index.php">Beranda</a><a class="nav-link" href="<?= h(BASE_URL) ?>/menu.php">Menu</a><a class="nav-link" href="<?= h(BASE_URL) ?>/moments.php">Kopiah Moments</a><a class="nav-link" href="<?= h(BASE_URL) ?>/about.php">Tentang</a>
>>>>>>> 0119df2 (ubah nama website)
      <a href="<?= h(BASE_URL) ?>/cart.php" class="btn btn-coffee !py-2 !px-4">Keranjang <span class="cart-count"><?= cart_count() ?></span></a>
      <?php if ($user): ?><a class="nav-link" href="<?= h(BASE_URL) ?>/<?= $user['role']==='owner'?'admin/dashboard.php':'kasir/dashboard.php' ?>">Panel <?= h(ucfirst($user['role'])) ?></a><?php else: ?><a class="nav-link" href="<?= h(BASE_URL) ?>/admin/login.php">Login staf</a><?php endif; ?>
    </nav>
  </div>
<<<<<<< HEAD
  <nav class="hidden md:hidden flex-col gap-4 px-4 pb-4 text-sm" data-mobile-nav><a href="<?= h(BASE_URL) ?>/index.php">Beranda</a><a href="<?= h(BASE_URL) ?>/menu.php">Menu</a><a href="<?= h(BASE_URL) ?>/moments.php">Cafeyu Moments</a><a href="<?= h(BASE_URL) ?>/about.php">Tentang</a><a href="<?= h(BASE_URL) ?>/cart.php">Keranjang (<?= cart_count() ?>)</a><a href="<?= h(BASE_URL) ?>/admin/login.php">Login staf</a></nav>
=======
  <nav class="hidden md:hidden flex-col gap-4 px-4 pb-4 text-sm" data-mobile-nav><a href="<?= h(BASE_URL) ?>/index.php">Beranda</a><a href="<?= h(BASE_URL) ?>/menu.php">Menu</a><a href="<?= h(BASE_URL) ?>/moments.php">Kopiah Moments</a><a href="<?= h(BASE_URL) ?>/about.php">Tentang</a><a href="<?= h(BASE_URL) ?>/cart.php">Keranjang (<?= cart_count() ?>)</a><a href="<?= h(BASE_URL) ?>/admin/login.php">Login staf</a></nav>
>>>>>>> 0119df2 (ubah nama website)
</header>
<main class="flex-1">
