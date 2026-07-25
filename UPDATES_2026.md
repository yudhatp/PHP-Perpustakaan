# Perbaikan Versi 2026

## Autentikasi dan Session

- Query login dan registrasi menggunakan prepared statement.
- Session ID diperbarui setelah login.
- Cookie session memakai `HttpOnly` dan `SameSite=Lax`.
- Form penting dilindungi token CSRF.
- Password baru memakai `password_hash()`.
- Password MD5 lama tetap dapat digunakan dan otomatis dimigrasikan setelah login berhasil.
- Perubahan password hanya memeriksa akun pengguna yang sedang login.

## Data dan Hak Akses

- Pembatasan akses berdasarkan role untuk halaman admin, staff, dan anggota.
- Validasi ID, tanggal, angka, status, dan data form.
- Output halaman dan PDF di-escape untuk mengurangi risiko XSS.
- Penghapusan anggota/staff yang sudah memiliki riwayat diubah menjadi penonaktifan.
- Penghapusan buku ditolak jika buku sudah memiliki riwayat peminjaman.
- Upload cover dibatasi ke JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB dan nama file acak.

## Peminjaman dan Pengembalian

- Nomor peminjaman diperbaiki dan dibuat konsisten.
- Pengecekan stok dan perubahan stok dilakukan dalam transaksi database.
- Buku yang sama tidak dapat ditambahkan dua kali pada transaksi aktif yang sama.
- Penghapusan detail peminjaman mengembalikan stok.
- Buku bagus/rusak mengembalikan stok saat dikembalikan.
- Buku hilang tidak mengurangi stok untuk kedua kalinya.
- Total denda dan status selesai dihitung ulang setelah setiap pengembalian.

## Laporan PDF

- Form laporan memakai validasi periode dan token CSRF.
- Query laporan memakai parameter tanggal yang tervalidasi.
- Akses cetak bukti dan kartu anggota dibatasi berdasarkan pemilik/role.
- TCPDF lama dipatch agar dapat menjalankan HTML dan tabel pada PHP 8.4.
- Nama file PDF dibuat aman dan tidak mengandung karakter waktu yang bermasalah.

## Database

- Charset diperbarui menjadi `utf8mb4`.
- Tabel lama MyISAM diperbarui menjadi InnoDB.
- Kolom username, password, nama, alamat, keterangan, dan audit user diperlebar.
- View diperbaiki agar kompatibel dengan mode `ONLY_FULL_GROUP_BY`.
- Index unik ditambahkan untuk username, nomor anggota, nomor peminjaman, profil akun, dan detail buku per transaksi.

## Target PHP 8.3

- Minimum runtime dinaikkan ke PHP 8.3.
- Pemeriksaan ekstensi `mysqli` dan driver `mysqlnd` ditambahkan.
- Session strict mode diaktifkan.
- TCPDF lama dipatch agar tidak menghasilkan deprecated warning pada alur PDF yang diuji.
- Folder `lib/tcpdf/old_lib` yang tidak dipakai dihapus karena berisi API PHP usang.
- Ditambahkan `composer.json`, `PHP83_UPGRADE.md`, dan `tools/php83_check.php`.
