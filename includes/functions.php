<?php
declare(strict_types=1);
function h(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function dbq(string $sql): PDOStatement { global $pdo; $stmt = $pdo->prepare($sql); $stmt->execute(); return $stmt; }
function safe_substr(string $value, int $start, ?int $length = null): string { if (function_exists('mb_substr')) return mb_substr($value, $start, $length); if (preg_match_all('/./us', $value, $m)) return implode('', array_slice($m[0], $start, $length)); return substr($value, $start, $length); }
function money(mixed $value): string { return 'Rp' . number_format((float)$value, 0, ',', '.'); }
function go(string $path): never { header('Location: ' . BASE_URL . '/' . ltrim($path, '/')); exit; }
function get_setting(string $key, string $default = ''): string {
    global $pdo;
    static $cache = [];
    if (array_key_exists($key, $cache)) return (string)$cache[$key];
    try { $st=$pdo->prepare('SELECT `value` FROM settings WHERE `key`=?'); $st->execute([$key]); $v=$st->fetchColumn(); }
    catch (Throwable $e) { $v=false; }
    return $cache[$key] = ($v === false || $v === null ? $default : (string)$v);
}

function refresh_cart_prices(): void {
    global $pdo;
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) return;
    foreach ($_SESSION['cart'] as $key => $item) {
        $st = $pdo->prepare('SELECT id,nama,harga,foto,stok,is_available FROM menus WHERE id=? LIMIT 1');
        $st->execute([(int)($item['menu_id'] ?? 0)]);
        $menu = $st->fetch();
        if (!$menu) { unset($_SESSION['cart'][$key]); continue; }
        $ids = [];
        foreach ((array)($item['options'] ?? []) as $option) if (isset($option['id']) && ctype_digit((string)$option['id'])) $ids[]=(int)$option['id'];
        $options=[]; $extra=0.0;
        if ($ids) {
            $ids=array_values(array_unique($ids));
            $marks=implode(',',array_fill(0,count($ids),'?'));
            $os=$pdo->prepare("SELECT id,nama_opsi,tipe,harga_tambahan FROM menu_options WHERE menu_id=? AND id IN ($marks) ORDER BY id");
            $os->execute(array_merge([(int)$menu['id']],$ids));
            foreach ($os->fetchAll() as $o) { $options[]=['id'=>(int)$o['id'],'nama'=>$o['nama_opsi'],'tipe'=>$o['tipe'],'harga'=>(float)$o['harga_tambahan']]; $extra+=(float)$o['harga_tambahan']; }
        }
        $item['name']=$menu['nama'];$item['foto']=$menu['foto'];$item['base_price']=(float)$menu['harga'];$item['price']=(float)$menu['harga']+$extra;$item['options']=$options;
        $_SESSION['cart'][$key]=$item;
    }
}

function cart_count(): int { $n=0; foreach (($_SESSION['cart'] ?? []) as $item) $n += (int)($item['qty'] ?? 0); return $n; }
function cart_subtotal(): float { $n=0; foreach (($_SESSION['cart'] ?? []) as $item) $n += (float)$item['price'] * (int)$item['qty']; return $n; }
function random_order_code(): string { return 'CFY' . date('ymd') . strtoupper(bin2hex(random_bytes(3))); }
function clean_text(string $v, int $max = 500): string { $v = trim(preg_replace('/\s+/', ' ', $v) ?? ''); return safe_substr($v, 0, $max); }
function json_response(array $data, int $status = 200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function safe_upload_image(array $file, string $folder, int $maxBytes = 2097152): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Foto gagal diunggah.');
    if ((int)$file['size'] > $maxBytes) throw new RuntimeException('Ukuran foto maksimal 2 MB.');
    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) throw new RuntimeException('Sumber foto tidak valid.');
    $finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($tmp);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime]) || @getimagesize($tmp) === false) throw new RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
    $root = dirname(__DIR__) . '/uploads/' . trim($folder, '/');
    if (!is_dir($root) && !mkdir($root, 0755, true) && !is_dir($root)) throw new RuntimeException('Folder upload tidak tersedia.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($tmp, $root . '/' . $name)) throw new RuntimeException('Foto tidak berhasil disimpan.');
    return 'uploads/' . trim($folder, '/') . '/' . $name;
}
function table_id_from_qr(): ?int {
    global $pdo;
    if (!empty($_GET['meja']) && ctype_digit((string)$_GET['meja'])) {
        $st=$pdo->prepare('SELECT id FROM `tables` WHERE id=?'); $st->execute([(int)$_GET['meja']]); return $st->fetchColumn() ? (int)$_GET['meja'] : null;
    }
    if (!empty($_GET['token'])) {
        $st=$pdo->prepare('SELECT id FROM `tables` WHERE qr_token=?'); $st->execute([(string)$_GET['token']]); $id=$st->fetchColumn(); return $id ? (int)$id : null;
    }
    return isset($_SESSION['selected_table_id']) ? (int)$_SESSION['selected_table_id'] : null;
}
