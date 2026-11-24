# Deployment Kopiah Moments

## Perubahan database

Jalankan migration biasa. Migration ini hanya mengubah baris `moments` yang berstatus `pending` menjadi `approved` dan menghapus key pengaturan `moment_auto_approve`. Data `rejected`, foto, reaksi, pelanggan, pesanan, review, dan tabel lain tidak dihapus.

```bash
php artisan migrate --force
php artisan optimize:clear
```

Jangan jalankan `migrate:fresh` pada database yang sudah berisi data.

## Penyimpanan foto

Foto Moments disimpan di `storage/app/public/moments`. Pastikan folder tersebut dapat ditulis oleh user PHP/web server.

Pada Linux hosting:

```bash
mkdir -p storage/app/public/moments
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

`storage:link` tetap disarankan untuk aset publik lain seperti logo. Penyajian foto Moments sendiri memakai route Laravel `/moments-image/{filename}`, sehingga tidak bergantung pada symbolic link `public/storage`.

## Konfigurasi aplikasi

Pertahankan file `.env` yang sudah dipakai aplikasi. Pastikan `APP_URL` berisi domain yang benar ketika hosting, bukan `localhost`, lalu jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

Pastikan ekstensi PHP berikut aktif: `gd`, `fileinfo`, `mbstring`, `dom`, `xml`, dan driver database MySQL (`pdo_mysql`). Ekstensi GD dan fileinfo dipakai pada pemrosesan serta pemeriksaan foto.

## Akses penghapusan

Halaman pengelolaan Moments berada di `/admin/moments` dan hanya dapat dibuka oleh akun dengan role `owner`. Kasir tidak diberi route moderasi atau penghapusan Moments. Pengunjung anonim hanya dapat menghapus foto yang cocok dengan token perangkatnya sendiri.


## Penyajian foto menu

Foto menu lokal yang diunggah owner tetap disimpan di `storage/app/public/menus`. Halaman kartu, detail, dan keranjang menggunakan accessor `foto_url`; path lokal dinormalisasi dan disajikan melalui route Laravel `/menu-image/{path}`. Karena itu, foto menu tidak bergantung pada `APP_URL` untuk membentuk host dan tidak membutuhkan symbolic link `public/storage` untuk tampil. `storage:link` tetap dapat dipakai untuk aset publik lain.

Jika sebuah file lokal tidak ditemukan, aplikasi mencatat peringatannya di `storage/logs/laravel.log` dan menampilkan placeholder umum agar tidak muncul ikon gambar rusak. Placeholder bukan pengganti foto produk. Untuk memeriksa data Chocolate Cloud pada database hosting, jalankan perintah baca-saja berikut dari folder aplikasi:

```bash
php artisan tinker --execute='$m = App\Models\Menu::where("nama", "Chocolate Cloud")->first(); $p = App\Support\MenuImagePath::storagePath($m?->foto); dump(["id" => $m?->id, "foto_database" => $m?->foto, "foto_url" => $m?->foto_url, "path_storage" => $p, "file_ada" => $p ? Storage::disk("public")->exists($p) : null]);'
```

Jika `foto_database` berisi URL eksternal, verifikasi URL itu dari browser hosting. Jika `path_storage` berisi path tetapi `file_ada` bernilai `false`, tambahkan kembali file Chocolate Cloud yang benar ke folder yang ditunjukkan database; jangan menggantinya dengan foto menu lain. Perbaikan ini tidak menjalankan migration dan tidak mengubah baris menu.
