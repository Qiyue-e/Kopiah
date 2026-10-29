-- Kopiah Coffee Shop Database
-- Import this file in phpMyAdmin. Demo passwords are documented in README_INSTALL.md.
CREATE DATABASE IF NOT EXISTS kopiah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kopiah;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS cash_sessions;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS moments;
DROP TABLE IF EXISTS promo_usages;
DROP TABLE IF EXISTS promos;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS `tables`;
DROP TABLE IF EXISTS menu_options;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE login_attempts (
  username VARCHAR(50) PRIMARY KEY,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_ip VARCHAR(45) NULL,
  last_attempt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_login_locked (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('owner','kasir') NOT NULL DEFAULT 'kasir',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(60) NOT NULL UNIQUE,
  urutan INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menus (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  nama VARCHAR(120) NOT NULL,
  deskripsi TEXT NULL,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  foto VARCHAR(255) NULL,
  stok INT NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_menus_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_menus_category (category_id),
  INDEX idx_menus_available (is_available),
  INDEX idx_menus_best_seller (is_best_seller),
  INDEX idx_menus_name (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id INT UNSIGNED NOT NULL,
  nama_opsi VARCHAR(100) NOT NULL,
  tipe VARCHAR(40) NOT NULL DEFAULT 'tambahan',
  harga_tambahan DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_menu_options_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_menu_options_menu (menu_id),
  INDEX idx_menu_options_type (tipe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tables` (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_meja VARCHAR(20) NOT NULL UNIQUE,
  qr_token CHAR(48) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan VARCHAR(32) NOT NULL UNIQUE,
  nama_pelanggan VARCHAR(100) NOT NULL,
  table_id INT UNSIGNED NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  diskon DECIMAL(12,2) NOT NULL DEFAULT 0,
  pajak DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  metode_bayar ENUM('Kasir','QRIS') NOT NULL DEFAULT 'Kasir',
  status_bayar ENUM('Belum Bayar','Menunggu QRIS','Lunas','Gagal') NOT NULL DEFAULT 'Belum Bayar',
  status_pesanan ENUM('Diterima','Diproses','Siap','Selesai','Dibatalkan') NOT NULL DEFAULT 'Diterima',
  catatan TEXT NULL,
  kasir_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_table FOREIGN KEY (table_id) REFERENCES `tables`(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_orders_cashier FOREIGN KEY (kasir_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_orders_created (created_at),
  INDEX idx_orders_status (status_pesanan),
  INDEX idx_orders_payment (status_bayar),
  INDEX idx_orders_table (table_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  menu_id INT UNSIGNED NULL,
  nama_menu VARCHAR(120) NOT NULL,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  opsi_json JSON NULL,
  catatan VARCHAR(500) NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_order_items_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_order_items_order (order_id),
  INDEX idx_order_items_menu (menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(40) NOT NULL UNIQUE,
  tipe ENUM('persen','nominal') NOT NULL DEFAULT 'persen',
  nilai DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_belanja DECIMAL(12,2) NOT NULL DEFAULT 0,
  kuota INT UNSIGNED NOT NULL DEFAULT 0,
  mulai DATETIME NULL,
  selesai DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_promos_active (is_active, mulai, selesai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promo_usages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promo_id INT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_promo_usages_promo FOREIGN KEY (promo_id) REFERENCES promos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_promo_usages_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_promo_order (promo_id, order_id),
  INDEX idx_promo_usage (promo_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE moments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_pelanggan VARCHAR(100) NULL,
  caption VARCHAR(500) NULL,
  tipe_foto ENUM('Makanan','Selfie') NOT NULL DEFAULT 'Makanan',
  file_path VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  upload_token VARCHAR(128) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_moments_status_created (status, created_at),
  INDEX idx_moments_ip_created (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  nama VARCHAR(100) NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  komentar VARCHAR(1000) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_review_order (order_id),
  INDEX idx_reviews_status_created (status, created_at),
  CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  aksi VARCHAR(100) NOT NULL,
  detail TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_logs_created (created_at),
  INDEX idx_logs_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cash_sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  modal_awal DECIMAL(12,2) NOT NULL DEFAULT 0,
  kas_akhir DECIMAL(12,2) NULL,
  catatan TEXT NULL,
  dibuka_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ditutup_pada DATETIME NULL,
  CONSTRAINT fk_cash_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_cash_user_open (user_id, ditutup_pada),
  INDEX idx_cash_opened (dibuka_pada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Owner password: Kopiah#2026! | Kasir password: Kasir#2026! (change after first login)
INSERT INTO users (nama, username, password_hash, role, is_active) VALUES
('Owner Kopiah', 'owner', '$2y$12$LPXBBez0aNcqQlu9.igjBeSHJWQODE0ZfD2Y93A7FPXBbCoWyi8Eu', 'owner', 1),
('Kasir Demo', 'kasir', '$2y$12$Cgj7mcUrDBRiSuGuP5eLx.nvjujivF13DHn146VQFPb8AZBT1pjKO', 'kasir', 1);

INSERT INTO categories (nama, urutan) VALUES
('Kopi', 1), ('Non-Kopi', 2), ('Makanan', 3), ('Snack', 4), ('Dessert', 5);

INSERT INTO menus (category_id, nama, deskripsi, harga, foto, stok, is_available, is_best_seller) VALUES
(1, 'Kopiah Aren Latte', 'Espresso, susu creamy, dan manis gula aren yang pas.', 24000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=900&q=85', 35, 1, 1),
(1, 'Americano', 'Espresso dengan air pilihan, ringan dan menyegarkan.', 18000, 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=900&q=85', 30, 1, 0),
(1, 'Caramel Macchiato', 'Kopi susu dengan sentuhan karamel.', 27000, 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=900&q=85', 25, 1, 1),
(1, 'Cappuccino', 'Perpaduan espresso, susu, dan foam lembut.', 23000, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=900&q=85', 28, 1, 0),
(2, 'Matcha Latte', 'Matcha dengan susu yang lembut dan earthy.', 26000, 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=900&q=85', 20, 1, 1),
(2, 'Chocolate Cloud', 'Cokelat pekat dengan tekstur creamy.', 24000, 'https://images.unsplash.com/photo-1542990253-0b8be4edc6c6?auto=format&fit=crop&w=900&q=85', 20, 1, 0),
(3, 'Chicken Mentai Rice', 'Nasi hangat dengan ayam dan saus mentai gurih.', 32000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=900&q=85', 15, 1, 1),
(4, 'French Fries', 'Kentang goreng renyah dengan bumbu pilihan.', 18000, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=900&q=85', 22, 1, 0),
(4, 'Butter Croissant', 'Croissant berlapis dengan aroma butter.', 20000, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=900&q=85', 12, 1, 0),
(5, 'Tiramisu Cup', 'Dessert lembut dengan kopi dan cokelat.', 25000, 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=900&q=85', 10, 1, 1);

INSERT INTO menu_options (menu_id, nama_opsi, tipe, harga_tambahan) VALUES
(1, 'Regular', 'Ukuran', 0), (1, 'Large', 'Ukuran', 5000), (1, 'Hot', 'Suhu', 0), (1, 'Ice', 'Suhu', 0), (1, 'Less Sugar', 'Gula', 0), (1, 'Extra Espresso', 'Topping', 6000),
(2, 'Regular', 'Ukuran', 0), (2, 'Large', 'Ukuran', 5000), (2, 'Hot', 'Suhu', 0), (2, 'Ice', 'Suhu', 0),
(3, 'Regular', 'Ukuran', 0), (3, 'Large', 'Ukuran', 5000), (3, 'Hot', 'Suhu', 0), (3, 'Ice', 'Suhu', 0), (3, 'Extra Caramel', 'Topping', 4000),
(4, 'Regular', 'Ukuran', 0), (4, 'Large', 'Ukuran', 5000), (4, 'Hot', 'Suhu', 0), (4, 'Ice', 'Suhu', 0),
(5, 'Regular', 'Ukuran', 0), (5, 'Large', 'Ukuran', 5000), (5, 'Ice', 'Suhu', 0), (5, 'Oat Milk', 'Topping', 6000),
(6, 'Regular', 'Ukuran', 0), (6, 'Large', 'Ukuran', 5000), (6, 'Hot', 'Suhu', 0), (6, 'Ice', 'Suhu', 0),
(7, 'Extra Sambal', 'Topping', 3000), (7, 'Telur', 'Topping', 5000),
(8, 'Cheese Powder', 'Topping', 3000), (8, 'BBQ Powder', 'Topping', 3000);

INSERT INTO `tables` (nomor_meja, qr_token) VALUES
('01', 'kopiah-meja-01-qr-token-3a65d19b92c7'), ('02', 'kopiah-meja-02-qr-token-5d91f8a0b62e'),
('03', 'kopiah-meja-03-qr-token-7bb2a406d3f1'), ('04', 'kopiah-meja-04-qr-token-9c124a7f55d0'),
('05', 'kopiah-meja-05-qr-token-1f8e39b6a402'), ('06', 'kopiah-meja-06-qr-token-4cb6d087a9e3'),
('07', 'kopiah-meja-07-qr-token-8aa16f37c5d2'), ('08', 'kopiah-meja-08-qr-token-2e9d61c4f807'),
('09', 'kopiah-meja-09-qr-token-6fb3a8421d95'), ('10', 'kopiah-meja-10-qr-token-a13c74e90b26');

INSERT INTO promos (kode, tipe, nilai, min_belanja, kuota, mulai, selesai, is_active) VALUES
('NGOPI10', 'persen', 10, 30000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 1);

INSERT INTO settings (`key`, `value`) VALUES
('cafe_name', 'Kopiah'),
('tagline', 'Ngopi santai, momen jadi berarti'),
('opening_hours', 'Setiap hari, 08.00 - 22.00 WIB'),
('address', 'Jl. Contoh No. 10, Depok, Jawa Barat'),
('whatsapp', '6281234567890'),
('instagram', '@kopiah.id'),
('logo_path', 'assets/img/logo-kopiah.png'),
('tax_percent', '0'),
('service_percent', '0'),
('qris_info', 'Silakan scan QRIS yang tersedia di kasir. Tunjukkan bukti pembayaran kepada kasir.'),
('google_maps_embed', 'https://www.google.com/maps?q=Depok%20Jawa%20Barat&output=embed');
