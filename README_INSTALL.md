<<<<<<< HEAD
# Cafeyu — Coffee Shop PHP + MySQL
=======
# Kopiah — Coffee Shop PHP + MySQL
>>>>>>> 0119df2 (ubah nama website)

**Tagline:** Ngopi santai, momen jadi berarti  
**Teknologi:** PHP 8+ native, PDO, MySQL/MariaDB, Tailwind CSS CDN, JavaScript vanilla, Chart.js, MediaDevices API.

<<<<<<< HEAD
Cafeyu dibuat sebagai starter project fungsional untuk demonstrasi dan pengembangan lokal. Pelanggan dapat memesan tanpa akun; owner dan kasir memakai login staf terpisah. Tailwind, Chart.js, Google Fonts, QRCode.js, dan foto contoh memerlukan koneksi internet saat browser memuatnya.

## 1. Isi fitur

- Halaman pelanggan: beranda, menu dengan pencarian/filter, detail dan opsi menu, keranjang sesi, checkout tanpa login, promo, lacak status AJAX setiap 5 detik, review setelah pesanan selesai, Cafeyu Moments, kamera langsung, tentang kami, peta, dan WhatsApp.
=======
Kopiah dibuat sebagai starter project fungsional untuk demonstrasi dan pengembangan lokal. Pelanggan dapat memesan tanpa akun; owner dan kasir memakai login staf terpisah. Tailwind, Chart.js, Google Fonts, QRCode.js, dan foto contoh memerlukan koneksi internet saat browser memuatnya.

## 1. Isi fitur

- Halaman pelanggan: beranda, menu dengan pencarian/filter, detail dan opsi menu, keranjang sesi, checkout tanpa login, promo, lacak status AJAX setiap 5 detik, review setelah pesanan selesai, Kopiah Moments, kamera langsung, tentang kami, peta, dan WhatsApp.
>>>>>>> 0119df2 (ubah nama website)
- Kamera: depan/belakang, preview, jepret/ulangi/kirim, filter normal/hangat/monokrom, watermark waktu, token sesi sekali pakai, pemeriksaan MIME dengan `finfo`, batas data 2 MB, resize sisi terpanjang hingga 1600 px, pembatasan 5 foto per IP dan sesi per jam, serta moderasi sebelum foto dipublikasikan. Halaman kamera tidak memiliki input file atau pemilih galeri.
- Kasir: dashboard dan polling pesanan, suara notifikasi setelah diaktifkan pengguna, ubah status pesanan, konfirmasi pembayaran, POS, preview/cetak struk thermal, toggle ketersediaan menu, moderasi foto, dan buka/tutup sesi kas.
- Owner: dashboard dan grafik, CRUD menu/kategori/akun kasir/promo/meja, QR meja yang bisa dicetak/simpan PDF dari dialog print, laporan dengan filter, export CSV yang bisa dibuka di Excel, cetak laporan ke PDF melalui dialog print browser, moderasi foto/review, pengaturan identitas, dan log aktivitas.
- Keamanan dasar: PDO prepared statements, `password_hash`/`password_verify`, regenerasi ID sesi, CSRF, escaping HTML, pemeriksaan role per panel, throttle login 5 kegagalan dengan lock 5 menit per username, validasi upload owner, dan `.htaccess` di folder upload.

## 2. Instalasi XAMPP (Windows)

1. Pasang XAMPP yang menyertakan PHP 8.x dan MariaDB/MySQL.
<<<<<<< HEAD
2. Ekstrak folder proyek `cafeyu` ke `C:\xampp\htdocs\cafeyu`. Pastikan `index.php` ada langsung di dalam folder `cafeyu`, bukan di folder bertingkat seperti `cafeyu/cafeyu/index.php`.
3. Buka **XAMPP Control Panel**, lalu klik **Start** pada **Apache** dan **MySQL**.
   - Jika MySQL XAMPP gagal menyala karena port 3306 dipakai layanan Windows `MySQL80`, buka `services.msc`, hentikan layanan `MySQL80` sementara, lalu start MySQL XAMPP lagi. Jangan hapus data layanan lain.
4. Buka `http://localhost/phpmyadmin` di browser.
5. Pilih tab **Import**, klik **Choose File**, pilih `C:\xampp\htdocs\cafeyu\database\cafeyu.sql`, lalu klik **Import/Go**. File SQL membuat database `cafeyu`, tabel, akun demo, kategori, menu, pilihan menu, promo, dan 10 meja.
   - File yang sama juga tersedia di root sebagai `cafeyu.sql`.
=======
2. Ekstrak folder proyek `kopiah` ke `C:\xampp\htdocs\kopiah`. Pastikan `index.php` ada langsung di dalam folder `kopiah`, bukan di folder bertingkat seperti `kopiah/kopiah/index.php`.
3. Buka **XAMPP Control Panel**, lalu klik **Start** pada **Apache** dan **MySQL**.
   - Jika MySQL XAMPP gagal menyala karena port 3306 dipakai layanan Windows `MySQL80`, buka `services.msc`, hentikan layanan `MySQL80` sementara, lalu start MySQL XAMPP lagi. Jangan hapus data layanan lain.
4. Buka `http://localhost/phpmyadmin` di browser.
5. Pilih tab **Import**, klik **Choose File**, pilih `C:\xampp\htdocs\kopiah\database\kopiah.sql`, lalu klik **Import/Go**. File SQL membuat database `kopiah`, tabel, akun demo, kategori, menu, pilihan menu, promo, dan 10 meja.
   - File yang sama juga tersedia di root sebagai `kopiah.sql`.
>>>>>>> 0119df2 (ubah nama website)
6. Buka `config/database.php` dan pastikan pengaturannya sesuai instalasi:

   ```php
   define('DB_HOST', '127.0.0.1');
<<<<<<< HEAD
   define('DB_NAME', 'cafeyu');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('BASE_URL', '/cafeyu');
=======
   define('DB_NAME', 'kopiah');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('BASE_URL', '/kopiah');
>>>>>>> 0119df2 (ubah nama website)
   ```

   Untuk konfigurasi XAMPP default, username biasanya `root` dan password kosong. Jika kamu sudah memberi password pada MySQL, isi `DB_PASS` dengan password tersebut.
7. Periksa ekstensi PHP di `C:\xampp\php\php.ini`. Pastikan ekstensi berikut aktif (hapus tanda `;` di awal jika masih dikomentari), lalu restart Apache:

   ```ini
   extension=pdo_mysql
   extension=fileinfo
   extension=gd
   extension=mbstring
   ```

   - `pdo_mysql`: koneksi database PDO.
   - `fileinfo`: validasi MIME foto.
<<<<<<< HEAD
   - `gd`: resize dan simpan foto Cafeyu Moments.
   - `mbstring`: dukungan teks UTF-8; kode memiliki fallback sederhana untuk sebagian fungsi string jika ekstensi ini tidak tersedia.
8. Buka website: **http://localhost/cafeyu/**.
9. Buka login staf: **http://localhost/cafeyu/admin/login.php**.
=======
   - `gd`: resize dan simpan foto Kopiah Moments.
   - `mbstring`: dukungan teks UTF-8; kode memiliki fallback sederhana untuk sebagian fungsi string jika ekstensi ini tidak tersedia.
8. Buka website: **http://localhost/kopiah/**.
9. Buka login staf: **http://localhost/kopiah/admin/login.php**.

## Jika kamu sudah punya instalasi Cafeyu dan ingin mempertahankan datanya

Jangan import `database/kopiah.sql` ke database lama jika ingin mempertahankan riwayat pesanan. File itu untuk instalasi baru. Untuk mengganti branding pada data lama:

1. Backup dulu database `cafeyu` lewat phpMyAdmin → pilih database → **Export**.
2. Ekstrak folder `kopiah` ke `C:\xampp\htdocs\kopiah`.
3. Di `kopiah/config/database.php`, gunakan `DB_NAME` = `cafeyu` agar tetap memakai database lama. Gunakan `BASE_URL` = `/kopiah` karena folder barunya bernama `kopiah`.
4. Pilih database `cafeyu` di phpMyAdmin → **Import** → pilih `kopiah/migrasi_branding_kopiah.sql`. Migrasi ini mengubah nama cafe, jalur logo dan beberapa label seed tanpa menghapus transaksi.
5. Buka `http://localhost/kopiah/`. Password akun lama tetap sama. Untuk logo, file `assets/img/logo-kopiah.png` sudah ada di folder baru.

Kalau kamu memilih instalasi baru, ikuti langkah standar di atas dan import `database/kopiah.sql` ke database `kopiah`.
>>>>>>> 0119df2 (ubah nama website)

## 3. Akun default dan password

| Role | Username | Password awal |
|---|---|---|
<<<<<<< HEAD
| Owner | `owner` | `Cafeyu#2026!` |
=======
| Owner | `owner` | `Kopiah#2026!` |
>>>>>>> 0119df2 (ubah nama website)
| Kasir demo | `kasir` | `Kasir#2026!` |

**Penting:** ini kredensial demo untuk instalasi lokal. Setelah berhasil login, buka menu **Ganti password** pada header panel dan segera buat password sendiri yang kuat (minimal 12 karakter). Jangan menggunakan kredensial demo di server publik. Owner dapat membuat akun kasir baru dan mereset password akun kasir melalui panel Owner → Akun Kasir. Jangan pernah membagikan password owner ke kasir.

Jika lupa password owner pada database lokal, cara yang disarankan adalah login dengan password lama atau buat hash baru melalui PHP lalu perbarui `users.password_hash` untuk username `owner`. Contoh membuat hash dari terminal XAMPP:

```bat
C:\xampp\php\php.exe -r "echo password_hash('PasswordBaruYangPanjang!', PASSWORD_DEFAULT), PHP_EOL;"
```

Salin hasil hash ke kolom `password_hash` untuk user `owner` di phpMyAdmin. Jangan simpan password polos di database.

## 4. Kamera lokal dan HTTPS

### Cara paling gampang untuk laptop yang sama

<<<<<<< HEAD
Buka `http://localhost/cafeyu/camera.php`. Browser modern menganggap `localhost` sebagai konteks aman sehingga `getUserMedia()` dapat digunakan setelah izin kamera diberikan. Jangan membuka file PHP dengan double-click atau melalui `file:///...`.
=======
Buka `http://localhost/kopiah/camera.php`. Browser modern menganggap `localhost` sebagai konteks aman sehingga `getUserMedia()` dapat digunakan setelah izin kamera diberikan. Jangan membuka file PHP dengan double-click atau melalui `file:///...`.
>>>>>>> 0119df2 (ubah nama website)

### HTTPS lokal dengan mkcert (opsional)

Langkah ini berguna untuk menguji lewat nama host/akses jaringan lokal. Pada Windows, instal `mkcert` dari sumber tepercaya sesuai kebijakan perangkatmu, kemudian buka terminal sebagai pengguna yang akan menjalankan browser:

1. Buat dan percayai local certificate authority:

   ```bat
   mkcert -install
   ```

2. Buat sertifikat untuk localhost. Perintah berikut menghasilkan file certificate dan key pada folder aktif:

   ```bat
   mkcert localhost 127.0.0.1 ::1
   ```

3. Salin hasil file `.pem` dan `-key.pem` ke lokasi Apache, misalnya:
<<<<<<< HEAD
   - `C:\xampp\apache\conf\ssl.crt\cafeyu.pem`
   - `C:\xampp\apache\conf\ssl.key\cafeyu-key.pem`
=======
   - `C:\xampp\apache\conf\ssl.crt\kopiah.pem`
   - `C:\xampp\apache\conf\ssl.key\kopiah-key.pem`
>>>>>>> 0119df2 (ubah nama website)

   Sesuaikan nama file dengan output `mkcert` yang sebenarnya.
4. Di `C:\xampp\apache\conf\httpd.conf`, pastikan modul SSL aktif dan konfigurasi SSL di-include (hapus `#` di awal baris jika diperlukan):

   ```apache
   LoadModule ssl_module modules/mod_ssl.so
   Include conf/extra/httpd-ssl.conf
   ```

   Jangan menghapus konfigurasi Apache lain yang sudah dibutuhkan instalasimu.
5. Di `C:\xampp\apache\conf\extra\httpd-ssl.conf`, atur path sertifikat dan key pada directive `SSLCertificateFile` dan `SSLCertificateKeyFile`, contohnya:

   ```apache
<<<<<<< HEAD
   SSLCertificateFile "C:/xampp/apache/conf/ssl.crt/cafeyu.pem"
   SSLCertificateKeyFile "C:/xampp/apache/conf/ssl.key/cafeyu-key.pem"
   ```

6. Pastikan Apache mendengarkan port 443 (`Listen 443`), simpan konfigurasi, lalu restart Apache.
7. Buka `https://localhost/cafeyu/camera.php`. Sertifikat yang dibuat untuk localhost tidak otomatis cocok untuk IP LAN atau ponsel; untuk pengujian perangkat lain, buat sertifikat yang menyertakan host/IP yang benar dan instal CA lokal ke perangkat uji. Untuk penggunaan publik, gunakan domain dan sertifikat TLS yang valid.
=======
   SSLCertificateFile "C:/xampp/apache/conf/ssl.crt/kopiah.pem"
   SSLCertificateKeyFile "C:/xampp/apache/conf/ssl.key/kopiah-key.pem"
   ```

6. Pastikan Apache mendengarkan port 443 (`Listen 443`), simpan konfigurasi, lalu restart Apache.
7. Buka `https://localhost/kopiah/camera.php`. Sertifikat yang dibuat untuk localhost tidak otomatis cocok untuk IP LAN atau ponsel; untuk pengujian perangkat lain, buat sertifikat yang menyertakan host/IP yang benar dan instal CA lokal ke perangkat uji. Untuk penggunaan publik, gunakan domain dan sertifikat TLS yang valid.
>>>>>>> 0119df2 (ubah nama website)

### Jika kamera tidak berjalan

- Izinkan kamera dari ikon gembok/pengaturan situs browser.
- Tutup aplikasi lain yang sedang menggunakan kamera.
- Pastikan `extension=gd` aktif di PHP untuk resize foto sisi server.
- Foto harus dibuat di halaman kamera menggunakan tombol **Jepret**. Pilihan file/galeri sengaja tidak disediakan.
- Batas 2 MB berlaku pada data foto sebelum proses penyimpanan/resize. Foto yang terlalu besar akan ditolak dengan pesan untuk mengambil ulang.

## 5. Alur penggunaan

1. Pelanggan buka `/menu.php`, pilih menu, atur varian/catatan, lalu tambahkan ke keranjang.
2. Pelanggan buka keranjang → checkout, isi nama, meja, metode bayar, dan kode promo jika ada.
3. Setelah checkout, simpan kode pesanan yang ditampilkan. Halaman `/track.php?kode=KODE_PESANAN` menampilkan status terbaru dan melakukan polling tiap 5 detik.
4. Kasir login dan membuka **Pesanan** untuk menerima pesanan, mengubah status, dan menandai pembayaran lunas ketika pembayaran benar-benar sudah diterima. Untuk QRIS pada starter ini, kasir harus melakukan konfirmasi secara manual; ini bukan koneksi payment gateway otomatis.
5. Kasir dapat membuat pesanan langsung dari **POS**, melihat/cetak struk, dan membuka/tutup sesi kas.
6. Owner mengelola menu, stok, akun kasir, promo, QR meja, laporan, pengaturan, dan moderasi dari panel owner.
7. Foto yang dikirim dari `/camera.php` disimpan dengan status `pending`. Owner atau kasir perlu menyetujuinya sebelum foto muncul di `/moments.php`.

## 6. Daftar endpoint/halaman

- Pelanggan: `/index.php`, `/menu.php`, `/menu-detail.php`, `/cart.php`, `/checkout.php`, `/track.php`, `/moments.php`, `/camera.php`, `/about.php`.
- Login staf: `/admin/login.php`; ganti password: `/admin/password.php`.
- Owner: `/admin/dashboard.php`, `/admin/menus.php`, `/admin/categories.php`, `/admin/users.php`, `/admin/promos.php`, `/admin/tables.php`, `/admin/reports.php`, `/admin/moments.php`, `/admin/settings.php`.
- Kasir: `/kasir/dashboard.php`, `/kasir/orders.php`, `/kasir/pos.php`, `/kasir/receipt.php`, `/kasir/cash.php`.
- API: `/api/orders.php`, `/api/order-status.php`, `/api/camera-token.php`, `/api/upload-moment.php`.

## 7. Checklist testing sebelum dipakai

### Pelanggan
- [ ] Beranda, menu, filter kategori, pencarian, dan detail menu terbuka pada desktop dan HP.
- [ ] Varian, topping, catatan, kuantitas keranjang dan total harga sesuai.
- [ ] Checkout tanpa login menyimpan nama/meja, kode promo valid/invalid, dan metode pembayaran.
- [ ] Stok berkurang setelah checkout; checkout ditolak jika stok tidak mencukupi.
- [ ] Status di halaman lacak berubah otomatis setelah kasir memperbarui pesanan.
- [ ] Form review hanya muncul sesudah status `Selesai`, dan satu pesanan hanya dapat memiliki satu review.

### Kamera dan moderasi
- [ ] Buka kamera dengan HTTPS atau `localhost`, izinkan izin kamera.
- [ ] Kamera depan untuk selfie dan kamera belakang untuk makanan; tombol **Balik kamera** berfungsi.
- [ ] **Jepret → Ulangi → Jepret → Kirim** berfungsi; filter dan watermark tampak pada hasil.
- [ ] Halaman kamera tidak menampilkan input file, drag & drop, atau tombol pilih galeri.
- [ ] Izin ditolak/kamera tidak ada/browser tidak mendukung menghasilkan pesan yang ramah.
- [ ] Upload yang melebihi 2 MB ditolak; token yang sama tidak bisa dipakai dua kali.
- [ ] Foto masuk `pending` dan tidak tampak di galeri sebelum disetujui owner/kasir.
- [ ] Percobaan foto ke-6 dalam satu jam dari IP/sesi yang sama ditolak.

### Role dan panel
- [ ] Login owner membuka panel owner.
- [ ] Login kasir membuka panel kasir.
- [ ] Kasir yang membuka URL `/admin/menus.php`, `/admin/users.php`, atau URL owner lain mendapat **Akses ditolak**.
- [ ] Lima kegagalan login berturut-turut mengunci username selama 5 menit.
- [ ] Login merotasi session ID; semua form POST menolak token CSRF yang salah.
- [ ] Owner dapat CRUD menu, kategori, akun kasir, promo, meja, serta pengaturan.
- [ ] QR meja memuat URL meja yang tepat; fungsi print bisa disimpan sebagai PDF.
- [ ] Laporan filter dan CSV terbuka di Excel; fungsi cetak browser bisa menyimpan laporan sebagai PDF.

### Kasir
- [ ] Pesanan baru terlihat setelah polling; suara hanya aktif setelah tombol suara diklik.
- [ ] Status pesanan, metode pembayaran, dan status pembayaran tersimpan.
- [ ] POS menolak stok yang kurang dan membuat struk.
- [ ] Struk bisa dicetak pada layout 58 mm/80 mm atau disimpan sebagai PDF.
- [ ] Toggle menu tersedia/habis berpengaruh pada katalog pelanggan.
- [ ] Buka/tutup kas merekam modal awal, kas akhir, dan catatan.

## 8. Catatan untuk produksi

Ini starter project, bukan implementasi kasir produksi yang sudah diaudit. Sebelum dipublikasikan ke internet, gunakan HTTPS/domain resmi, ubah semua kredensial default, atur backup database, simpan rahasia di environment/config yang tidak terpublikasi, tambahkan logging/monitoring, uji ulang perlindungan rate-limit dan upload, dan lakukan audit keamanan. Pembayaran QRIS saat ini dicatat dan dikonfirmasi manual; integrasi payment gateway membutuhkan provider dan kredensial resmi. Ekspor PDF menggunakan dialog cetak browser agar tidak memerlukan library PDF server tambahan.
