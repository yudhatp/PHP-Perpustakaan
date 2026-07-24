-- Jalankan sekali pada database lama sebelum menggunakan versi aplikasi ini.
-- Disarankan membuat backup database terlebih dahulu.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

UPDATE t_account SET update_date = NULL WHERE update_date = '0000-00-00 00:00:00';

ALTER TABLE p_role ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE t_account ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE t_anggota ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE t_buku ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE t_detil_pinjam ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE t_peminjaman ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE t_staff ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE t_account
    MODIFY username VARCHAR(30) NOT NULL,
    MODIFY password VARCHAR(255) NOT NULL,
    MODIFY create_by VARCHAR(30) NOT NULL,
    MODIFY update_by VARCHAR(30) NULL;

ALTER TABLE t_anggota
    MODIFY no_anggota VARCHAR(20) NOT NULL,
    MODIFY nama VARCHAR(100) NOT NULL,
    MODIFY no_telp VARCHAR(20) NOT NULL,
    MODIFY alamat TEXT NOT NULL,
    MODIFY keterangan VARCHAR(255) NULL,
    MODIFY status VARCHAR(20) NULL,
    MODIFY create_by VARCHAR(30) NOT NULL,
    MODIFY update_by VARCHAR(30) NULL;

ALTER TABLE t_buku
    MODIFY kode_rak VARCHAR(10) NOT NULL,
    MODIFY sinopsis TEXT NULL,
    MODIFY gambar VARCHAR(255) NULL,
    MODIFY create_by VARCHAR(30) NOT NULL,
    MODIFY update_by VARCHAR(30) NULL;

ALTER TABLE t_detil_pinjam
    MODIFY kondisi VARCHAR(20) NULL,
    MODIFY keterangan VARCHAR(255) NULL,
    MODIFY update_by VARCHAR(30) NULL;

ALTER TABLE t_peminjaman
    MODIFY no_peminjaman VARCHAR(20) NOT NULL,
    MODIFY create_by VARCHAR(30) NOT NULL,
    MODIFY update_by VARCHAR(30) NULL;

ALTER TABLE t_staff
    MODIFY nama VARCHAR(100) NOT NULL,
    MODIFY alamat TEXT NULL,
    MODIFY status VARCHAR(20) NOT NULL,
    MODIFY create_by VARCHAR(30) NOT NULL,
    MODIFY update_by VARCHAR(30) NULL;

-- Tambahkan index unik setelah memastikan tidak ada data duplikat.
ALTER TABLE t_account ADD UNIQUE KEY uk_account_username (username);
ALTER TABLE t_anggota ADD UNIQUE KEY uk_anggota_account (id_t_account), ADD UNIQUE KEY uk_anggota_nomor (no_anggota);
ALTER TABLE t_staff ADD UNIQUE KEY uk_staff_account (id_t_account);
ALTER TABLE t_peminjaman ADD UNIQUE KEY uk_peminjaman_nomor (no_peminjaman);
ALTER TABLE t_detil_pinjam ADD UNIQUE KEY uk_detil_peminjaman_buku (id_t_peminjaman, id_t_buku);

DROP VIEW IF EXISTS v_detil_pinjam;
CREATE VIEW v_detil_pinjam AS
SELECT p.id_t_peminjaman, d.id_t_detil_pinjam, b.id_t_buku, b.nama_buku, b.penulis, b.harga,
       d.tgl_kembali, d.qty, d.kondisi, d.denda, d.keterangan
FROM t_peminjaman p
JOIN t_detil_pinjam d ON p.id_t_peminjaman = d.id_t_peminjaman
JOIN t_buku b ON d.id_t_buku = b.id_t_buku;

DROP VIEW IF EXISTS v_history_peminjaman;
CREATE VIEW v_history_peminjaman AS
SELECT a.id_t_anggota, a.no_anggota, a.nama, a.tgl_daftar, MAX(p.tgl_pinjam) AS tgl_terakhir_pinjam
FROM t_anggota a
LEFT JOIN t_peminjaman p ON p.id_t_anggota = a.id_t_anggota
GROUP BY a.id_t_anggota, a.no_anggota, a.nama, a.tgl_daftar;

DROP VIEW IF EXISTS v_peminjaman;
CREATE VIEW v_peminjaman AS
SELECT p.id_t_peminjaman, p.no_peminjaman, COALESCE(s.nama, 'Admin') AS staff,
       p.tgl_pinjam, p.tgl_kembali, a.no_anggota, a.nama AS anggota,
       ac.username, ac.id_t_account AS ID, a.id_t_anggota, COALESCE(SUM(d.qty), 0) AS jum
FROM t_peminjaman p
JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota
LEFT JOIN t_staff s ON p.id_t_staff = s.id_t_staff
JOIN t_account ac ON a.id_t_account = ac.id_t_account
LEFT JOIN t_detil_pinjam d ON d.id_t_peminjaman = p.id_t_peminjaman
GROUP BY p.id_t_peminjaman, p.no_peminjaman, s.nama, p.tgl_pinjam, p.tgl_kembali,
         a.no_anggota, a.nama, ac.username, ac.id_t_account, a.id_t_anggota;

SET FOREIGN_KEY_CHECKS = 1;
SET SQL_MODE = @OLD_SQL_MODE;
