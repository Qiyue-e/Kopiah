-- Migrasi branding Cafeyu menjadi Kopiah untuk instalasi yang sudah ada.
-- Jalankan hanya jika database lama kamu bernama `cafeyu`.
-- Tidak menghapus pesanan, menu, akun, maupun riwayat transaksi.
USE cafeyu;

INSERT INTO settings (`key`, `value`) VALUES
  ('cafe_name', 'Kopiah'),
  ('logo_path', 'assets/img/logo-kopiah.png'),
  ('instagram', '@kopiah.id')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

UPDATE users SET nama = 'Owner Kopiah' WHERE username = 'owner';
UPDATE menus SET nama = 'Kopiah Aren Latte' WHERE nama = 'Cafeyu Aren Latte';
