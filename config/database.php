<?php
declare(strict_types=1);

// Sesuaikan jika username/password MySQL lokal kamu berbeda.
define('DB_HOST', '127.0.0.1');
<<<<<<< HEAD
define('DB_NAME', 'cafeyu');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/cafeyu'); // folder proyek di htdocs; ubah jika nama folder berbeda
=======
define('DB_NAME', 'kopiah');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/kopiah'); // folder proyek di htdocs; ubah jika nama folder berbeda
>>>>>>> 0119df2 (ubah nama website)

date_default_timezone_set('Asia/Jakarta');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa config/database.php dan pastikan MySQL XAMPP aktif.');
}
