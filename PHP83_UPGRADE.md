# Upgrade PHP 8.3

Versi ini ditargetkan untuk **PHP 8.3** dan tetap mempertahankan struktur PHP native, fitur, URL halaman, serta skema database aplikasi.

## Perubahan kompatibilitas

- Menambahkan pemeriksaan minimum `PHP_VERSION_ID >= 80300`.
- Menambahkan pesan konfigurasi yang jelas bila `mysqli` atau `mysqlnd` belum aktif.
- Menambahkan metadata `composer.json` dengan kebutuhan PHP dan ekstensi utama.
- Menambahkan pemeriksaan lingkungan melalui `tools/php83_check.php`.
- Mengaktifkan `session.use_strict_mode` dan `session.use_only_cookies`.
- Menghapus folder contoh `lib/tcpdf/old_lib` yang tidak digunakan dan masih memuat API PHP usang.
- Memperbaiki peringatan deprecated pada TCPDF lama:
  - urutan parameter opsional dan wajib;
  - konversi float ke integer pada indeks karakter.
- Laporan PDF diuji dengan `E_ALL` tanpa warning/deprecated pada PHP 8.4, yang lebih ketat daripada PHP 8.3 untuk bagian yang diuji.

## Ekstensi PHP yang wajib

- `mysqli`
- `mysqlnd` agar `mysqli_stmt_get_result()` tersedia
- `fileinfo`
- `session`

Ekstensi yang disarankan:

- `mbstring`
- `gd`
- `curl`

## Pengaturan Laragon

1. Pilih PHP 8.3 dari menu Laragon.
2. Buka `Menu > PHP > Extensions`.
3. Pastikan `mysqli`, `fileinfo`, `mbstring`, dan `gd` aktif.
4. Restart Laragon.
5. Dari terminal project jalankan:

```bash
php -v
php tools/php83_check.php
```

## Batas pengujian

Pemeriksaan sintaks dilakukan pada seluruh file PHP. Pembuatan PDF dasar dan tabel multi-halaman juga diuji. Pengujian transaksi database secara penuh tetap perlu dilakukan pada Laragon karena paket pengujian ini tidak menjalankan server MySQL aplikasi Anda.
