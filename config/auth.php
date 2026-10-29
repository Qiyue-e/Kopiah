<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): array {
    $u = current_user();
    if (!$u) { header('Location: ' . BASE_URL . '/admin/login.php'); exit; }
    if (isset($u['id'])) {
        global $pdo;
        $st = $pdo->prepare('SELECT id, nama, username, role, is_active FROM users WHERE id = ? LIMIT 1');
        $st->execute([(int)$u['id']]);
        $fresh = $st->fetch();
        if (!$fresh || !(int)$fresh['is_active']) { $_SESSION = []; session_destroy(); header('Location: ' . BASE_URL . '/admin/login.php?inactive=1'); exit; }
        $_SESSION['user'] = $fresh;
        return $fresh;
    }
    return $u;
}
function require_role(string|array $roles): array {
    $u = require_login(); $roles = (array)$roles;
    if (!in_array($u['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('<h1>Akses ditolak</h1><p>Akun kamu tidak memiliki izin untuk halaman ini.</p><a href="' . h(BASE_URL) . '">Kembali ke beranda</a>');
    }
    return $u;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_check(): void {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419); exit('Sesi formulir berakhir. Muat ulang halaman lalu coba lagi.');
    }
}
function log_activity(string $aksi, string $detail = ''): void {
    global $pdo; $uid = current_user()['id'] ?? null;
    $st = $pdo->prepare('INSERT INTO activity_logs (user_id, aksi, detail) VALUES (?, ?, ?)');
    $st->execute([$uid, $aksi, $detail]);
}
