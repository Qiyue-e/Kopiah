# Kopiah — versi Laravel 12

Website kedai kopi (pesan tanpa akun pelanggan, panel owner & kasir, POS, laporan, QR meja) yang dimigrasikan dari PHP native ke **Laravel 12**, lengkap dengan fitur **Kopiah Moments gaya Locket**.

## Cara menjalankan (XAMPP / lokal)

Butuh: PHP 8.2+ (ekstensi `gd`, `mbstring`, `pdo_mysql`, `fileinfo`), Composer, MySQL.

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
# buat database kosong bernama `kopiah` di phpMyAdmin, atur DB_* di .env bila perlu
php artisan migrate --seed
php artisan storage:link      # wajib: foto menu/moments disimpan di storage/app/public
php artisan serve             # buka http://localhost:8000
```

Atau cukup `composer run setup` setelah `.env` dan database siap.

> Kamera Moments hanya jalan di **HTTPS atau `localhost`**. Tes dari HP: pakai tunnel HTTPS (mis. `ngrok http 8000`) dan set `APP_URL` yang sama.

**Akun demo** (owner mengganti passwordnya lewat menu *Ganti password*; kasir tidak punya menu ini, passwordnya direset owner lewat *Akun Kasir*)

| Role  | Username | Password       | Login                          |
|-------|----------|----------------|--------------------------------|
| Owner | `owner`  | `Kopiah#2026!` | `/admin/login`                 |
| Kasir | `kasir`  | `Kasir#2026!`  | `/admin/login`                 |

Kode promo demo: `NGOPI10` (10%, min. Rp30.000).

## Pemetaan PHP native → Laravel

| Sebelumnya                              | Sekarang (fitur Laravel)                                            |
|-----------------------------------------|---------------------------------------------------------------------|
| `*.php` per halaman                     | **Routing** bernama `routes/web.php` + group & middleware           |
| `config/database.php`, PDO manual       | **Eloquent ORM** (relasi, scope, cast, accessor) di `app/Models`    |
| `database/kopiah.sql`                   | **Migration** (15 tabel) + **Seeder** di `database/`                |
| `includes/header.php`, `footer.php`     | **Blade** layout (`layouts/`), komponen `<x-flash>`, `<x-menu-card>` |
| `$_SESSION['user']`, `password_hash`    | **Auth** Laravel (`Auth::attempt`, hash `bcrypt`, `regenerate`)     |
| `login_attempts` + kode manual          | **RateLimiter** (5 gagal → kunci 5 menit)                           |
| `require_role()`                        | Middleware `role:owner` / `role:kasir,owner`                        |
| `csrf_check()`                          | CSRF bawaan (`@csrf`, header `X-CSRF-TOKEN`)                        |
| validasi manual `$_POST`                | `$request->validate()` + **FormRequest** + pesan Indonesia (`lang/id`) |
| keranjang `$_SESSION['cart']`           | `CartService` (harga selalu dihitung ulang dari DB)                 |
| transaksi PDO `FOR UPDATE`              | `OrderService` + `DB::transaction` + `lockForUpdate()`              |
| `move_uploaded_file`, GD manual         | `Storage` disk `public` + `MomentImageService`                      |
| `api/*.php`                             | Route JSON/partial Blade di controller yang sama                    |
| `get_setting()`                         | Model `Setting` + `Cache` + helper `setting()`                      |

Struktur utama: `app/Http/Controllers/{Admin,Kasir,Staff,Auth}`, `app/Services`, `app/Models`, `resources/views`.

## Kopiah Moments — gaya Locket

Halaman `/moments`:

- **Kamera-first, persegi 1:1** dengan sudut membulat; tanpa unggah dari galeri (live dari kamera, depan/belakang, filter Normal/Hangat/B&W).
- **Caption overlay** langsung di atas foto (maks. 140 karakter) sebelum dikirim, tombol kirim ➤ di tengah seperti Locket.
- **Feed "teman Kopiah"** + **Riwayat** (grid 3 kolom, ketuk untuk membuka) — tombol kiri bawah untuk berganti tampilan.
- **Reaksi emoji** (❤️ ☕ 😋 🔥 😂), 1 reaksi per perangkat per foto, bisa diganti/dibatalkan.
- **Tanpa akun**: identitas anonim lewat cookie `kopiah_device` (middleware `EnsureDeviceToken`) + nama & avatar emoji di perangkat. Pengirim bisa menghapus fotonya sendiri.
- Notifikasi **"↑ momen baru"** (polling 20 detik), batas 5 foto/jam per perangkat.
- Server meng-*decode ulang* gambar dengan GD → crop persegi, maks. 1080px, JPEG (membuang metadata/payload).
- Moderasi: default foto menunggu persetujuan (pengirim tetap melihat fotonya dengan badge "menunggu"). Owner bisa mengaktifkan **persetujuan otomatis** di *Pengaturan* agar instan seperti Locket.

## Catatan

- Tailwind masih via CDN (seperti versi asli), jadi tidak perlu `npm`. Perlu internet untuk Tailwind, font, Chart.js, dan QR.
- Foto lama di `uploads/moments/` tidak dimigrasikan (tidak ada barisnya di database asli).
- Tabel `login_attempts` dihapus (diganti RateLimiter); `password_hash` → `password`; semua tabel kini punya `created_at/updated_at`. Nama tabel/kolom lain dipertahankan.
