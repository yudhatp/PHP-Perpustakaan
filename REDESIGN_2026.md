# Redesign Antarmuka 2026

Versi ini tetap menggunakan PHP native, PHP 8.3, struktur database, URL halaman, hak akses, dan proses bisnis yang sama. Perubahan difokuskan pada tampilan dan pengalaman penggunaan.

## Perubahan visual

- Layout aplikasi baru dengan sidebar gelap dan topbar terang.
- Navigasi aktif otomatis mengikuti halaman yang sedang dibuka.
- Sidebar responsif untuk desktop, tablet, dan perangkat seluler.
- Dashboard baru dengan kartu ringkasan yang lebih mudah dibaca.
- Halaman login dan registrasi menggunakan layout modern dua kolom.
- Panel, form, tabel, tombol, modal, alert, pagination, serta halaman profil diseragamkan.
- Tampilan tabel dibuat lebih nyaman untuk data yang lebar dan tetap dapat digulir pada layar kecil.
- Gaya cetak dasar ditambahkan agar elemen navigasi tidak ikut tercetak.

## File utama redesign

- `template/css/app-modern.css`
- `template/js/app-modern.js`
- `form/header.php`
- `form/footer.php`
- `form/dashboard.php`
- `form/profile.php`
- `login.php`
- `register.php`

## Catatan pengujian

Lakukan pengujian pada resolusi desktop dan mobile. Fokuskan pada:

1. Login, registrasi, dan logout.
2. Buka-tutup sidebar pada layar kecil.
3. Dropdown profil pada topbar.
4. Seluruh submenu sesuai role Admin, Staff, dan Anggota.
5. Form tambah/edit, modal, datepicker, pagination, dan tabel.
6. Cetak laporan PDF dan kartu anggota.

Redesign ini tidak mengganti Bootstrap 3 karena penggantian langsung ke Bootstrap 5 berisiko merusak JavaScript, modal, dan komponen lama. Bootstrap 3 tetap dipertahankan sebagai fondasi kompatibilitas, kemudian tampilannya dioverride melalui design system baru.
