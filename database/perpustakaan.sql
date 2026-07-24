-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Target database:              MySQL 5.7+ / MariaDB 10.3+
-- Server OS:                    Win32
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

-- Dumping database structure for yudhatpm_perpustakaan
CREATE DATABASE IF NOT EXISTS `yudhatpm_perpustakaan` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `yudhatpm_perpustakaan`;


-- Dumping structure for table yudhatpm_perpustakaan.p_role
CREATE TABLE IF NOT EXISTS `p_role` (
  `id_p_role` int(11) NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(10) NOT NULL,
  `create_date` datetime NOT NULL,
  PRIMARY KEY (`id_p_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.p_role: 3 rows
/*!40000 ALTER TABLE `p_role` DISABLE KEYS */;
INSERT INTO `p_role` (`id_p_role`, `nama_role`, `create_date`) VALUES
	(1, 'admin', '2016-10-29 21:03:10'),
	(2, 'staff', '2016-10-29 21:03:20'),
	(3, 'anggota', '2016-10-29 21:03:22');
/*!40000 ALTER TABLE `p_role` ENABLE KEYS */;


-- Dumping structure for table yudhatpm_perpustakaan.t_account
CREATE TABLE IF NOT EXISTS `t_account` (
  `id_t_account` int(11) NOT NULL AUTO_INCREMENT,
  `id_p_role` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'password_hash; akun lama MD5 dimigrasikan saat login',
  `create_date` datetime NOT NULL,
  `create_by` varchar(30) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL COMMENT 'Username',
  PRIMARY KEY (`id_t_account`),
  UNIQUE KEY `uk_account_username` (`username`),
  KEY `idx_account_role` (`id_p_role`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.t_account: 5 rows
/*!40000 ALTER TABLE `t_account` DISABLE KEYS */;
INSERT INTO `t_account` (`id_t_account`, `id_p_role`, `username`, `password`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
	(1, 1, 'adm', '81186D077459FCA990144F65D3340E06', '2016-10-17 23:24:37', '', NULL, NULL),
	(2, 2, 'ptr', '1d0ba0986c3247c981ac3dad9043e345', '2016-10-29 00:00:00', 'adm', NULL, NULL),
	(3, 3, 'ast', '90cb05f315e6205ad422128317ecf879', '2016-11-06 00:00:00', 'adm', NULL, NULL),
	(4, 3, 'ytp', '6fd162da671f9a34c8c514422205e1fd', '2016-11-06 00:00:00', 'adm', NULL, NULL),
	(5, 3, 'bgn', '700a0bc0975f202d3749c9ac9b4d3467', '2016-11-07 00:00:00', 'adm', NULL, NULL);
/*!40000 ALTER TABLE `t_account` ENABLE KEYS */;


-- Dumping structure for table yudhatpm_perpustakaan.t_anggota
CREATE TABLE IF NOT EXISTS `t_anggota` (
  `id_t_anggota` int(11) NOT NULL AUTO_INCREMENT,
  `id_t_account` int(11) NOT NULL,
  `no_anggota` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tgl_daftar` date NOT NULL,
  `tgl_lahir` date NOT NULL,
  `jenis_kelamin` varchar(10) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL COMMENT 'aktif, tidak aktif',
  `update_date` date DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL COMMENT 'Username',
  `create_by` varchar(30) NOT NULL COMMENT 'Username',
  `create_date` date NOT NULL,
  PRIMARY KEY (`id_t_anggota`),
  UNIQUE KEY `uk_anggota_account` (`id_t_account`),
  UNIQUE KEY `uk_anggota_nomor` (`no_anggota`),
  KEY `idx_anggota_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.t_anggota: ~3 rows (approximately)
/*!40000 ALTER TABLE `t_anggota` DISABLE KEYS */;
INSERT INTO `t_anggota` (`id_t_anggota`, `id_t_account`, `no_anggota`, `nama`, `tgl_daftar`, `tgl_lahir`, `jenis_kelamin`, `no_telp`, `alamat`, `keterangan`, `status`, `update_date`, `update_by`, `create_by`, `create_date`) VALUES
	(3, 3, 'ANG04092016', 'Asti', '2016-10-30', '1993-04-09', 'Wanita', '081298770101', 'Bogor,Cibinong', '', 'Aktif', '2016-11-08', 'adm', 'adm', '2016-10-30'),
	(4, 4, 'ANG05072016', 'Yudha T Putra', '2016-11-01', '1992-05-07', 'Pria', '08230099918', 'Jatijajar Depok', 'Test Gan', 'Aktif', '2016-11-08', 'adm', 'adm', '2016-11-01'),
	(5, 5, 'ANG08162016', 'Bangun', '2016-11-08', '1993-08-16', 'Pria', '08912300010', 'Depok, Cinere', '', 'Aktif', '2016-11-08', 'adm', 'bgn', '2016-11-08');
/*!40000 ALTER TABLE `t_anggota` ENABLE KEYS */;


-- Dumping structure for table yudhatpm_perpustakaan.t_buku
CREATE TABLE IF NOT EXISTS `t_buku` (
  `id_t_buku` int(11) NOT NULL AUTO_INCREMENT,
  `nama_buku` varchar(128) NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `penulis` varchar(64) NOT NULL,
  `penerbit` varchar(64) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `harga` int(8) NOT NULL COMMENT 'Untuk perhitungan denda',
  `kode_rak` varchar(10) NOT NULL,
  `stok` int(11) NOT NULL,
  `sinopsis` text NULL,
  `gambar` varchar(255),
  `create_date` date NOT NULL,
  `create_by` varchar(30) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL COMMENT 'Username',
  PRIMARY KEY (`id_t_buku`),
  KEY `idx_buku_nama` (`nama_buku`),
  KEY `idx_buku_jenis` (`jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.t_buku: ~6 rows (approximately)
/*!40000 ALTER TABLE `t_buku` DISABLE KEYS */;
INSERT INTO `t_buku` (`id_t_buku`, `nama_buku`, `jenis`, `penulis`, `penerbit`, `tahun_terbit`, `harga`, `kode_rak`, `stok`, `sinopsis`, `gambar`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
	(1, 'Mudah Membuat Portal Berita Online dengan PHP dan MySQL', 'Komputer', 'Wahana Komputer', 'Andi', '2012', 71500, 'R1', 9, 'Buku PAS: Mudah Membuat Portal Berita Online dengan PHP dan MySQL ini menjelaskan tentang pembuatan portal berita online menggunakan PHP dan MySQL. Buku ini ditujukan bagi Anda yang tertarik dalam bidang pemrograman website, khususnya PHP', 'Mudah-Membuat-Portal-Berita-Online-Dengan-PHP-MySQL.jpg', '2016-10-30', 'adm', '2016-10-18 08:10:28', NULL),
	(2, '100 Quotes Simple Thinking about Blood Type', 'Pengembangan Diri', 'Park Dong Sun', 'Penerbit Haru', '2016', 65000, 'R2', 10, 'Buku ilustrasi ini berisi 100 kutipan sifat golongan darah A, B, AB, dan O. Dilengkapi juga dengan komik yang belum pernah dipublikasikan di buku sebelumnya.', '100 Quotes Simple Thinking about Blood Type.jpg', '2016-10-30', 'adm', NULL, NULL),
	(3, 'Hujan', 'Novel', 'Tere Liye', 'Gramedia Pustaka Utama', '2016', 57800, 'R3', 9, NULL, 'hujan.jpg', '2016-10-30', 'adm', NULL, NULL),
	(4, 'Cheeky Romance', 'Novel', 'Kim Eun Jeong', 'Penerbit Haru', '2012', 65000, 'R3', 10, 'Wanita yang  tingkahnya tidak terduga, “si ibu hamil nasional”, vs laki-laki yang selalu dianggap sempurna,“si dokter nasional”.', 'Cheeky Romance.jpg', '2016-10-30', 'adm', NULL, NULL),
	(5, 'The Hidden Prince', 'Novel', 'Jjea Mayang', 'Sinar Kejora', '2015', 50000, 'R3', 10, 'Kim Jong Woon, seorang pencuri yang wajah tampannya terekam dan tersebar melalui kamera pengawas dan menjadi daftar pencarian polisi alias buronan, memutuskan untuk menyamar menjadi seorang pelayan wanita bernama Kim Jong Rin di sebuah rumah mewah', 'the hidden prince.jpg', '2016-10-30', 'adm', NULL, NULL),
	(6, 'Designer`S Revenge,The', 'Komik', 'Miyuki Yorita', 'M&C', '2014', 16000, 'R04', 14, '-', 'Designer`S Revenge,The.jpg', '2016-11-01', 'adm', NULL, NULL);
/*!40000 ALTER TABLE `t_buku` ENABLE KEYS */;


-- Dumping structure for table yudhatpm_perpustakaan.t_detil_pinjam
CREATE TABLE IF NOT EXISTS `t_detil_pinjam` (
  `id_t_detil_pinjam` int(10) NOT NULL AUTO_INCREMENT,
  `id_t_peminjaman` int(11) NOT NULL,
  `id_t_buku` int(11) NOT NULL,
  `tgl_kembali` datetime DEFAULT NULL,
  `kondisi` varchar(10) DEFAULT NULL COMMENT 'bagus, rusak, hilang',
  `denda` int(11) DEFAULT NULL COMMENT '30% dari harga buku jika rusak',
  `qty` int(8) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL COMMENT 'Session',
  PRIMARY KEY (`id_t_detil_pinjam`),
  UNIQUE KEY `uk_detil_peminjaman_buku` (`id_t_peminjaman`,`id_t_buku`),
  KEY `idx_detil_buku` (`id_t_buku`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.t_detil_pinjam: ~4 rows (approximately)
/*!40000 ALTER TABLE `t_detil_pinjam` DISABLE KEYS */;
INSERT INTO `t_detil_pinjam` (`id_t_detil_pinjam`, `id_t_peminjaman`, `id_t_buku`, `tgl_kembali`, `kondisi`, `denda`, `qty`, `keterangan`, `update_date`, `update_by`) VALUES
	(1, 1, 3, NULL, NULL, NULL, 1, NULL, NULL, NULL),
	(3, 2, 6, NULL, NULL, NULL, 1, NULL, NULL, NULL),
	(4, 3, 3, '2016-11-08 00:00:00', 'Rusak', 17340, 1, 'Test aja', '2016-11-08 00:00:00', 'adm'),
	(6, 1, 1, NULL, NULL, NULL, 1, NULL, NULL, NULL);
/*!40000 ALTER TABLE `t_detil_pinjam` ENABLE KEYS */;


-- Dumping structure for table yudhatpm_perpustakaan.t_peminjaman
CREATE TABLE IF NOT EXISTS `t_peminjaman` (
  `id_t_peminjaman` int(11) NOT NULL AUTO_INCREMENT,
  `no_peminjaman` varchar(20) NOT NULL,
  `id_t_staff` int(11) DEFAULT NULL,
  `id_t_anggota` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date DEFAULT NULL COMMENT 'By System',
  `total_denda` int(11) DEFAULT NULL,
  `create_date` date NOT NULL,
  `create_by` varchar(30) NOT NULL COMMENT 'Username Staff',
  `update_date` date DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL COMMENT 'Username Staff',
  PRIMARY KEY (`id_t_peminjaman`),
  UNIQUE KEY `uk_peminjaman_nomor` (`no_peminjaman`),
  KEY `idx_peminjaman_anggota` (`id_t_anggota`),
  KEY `idx_peminjaman_staff` (`id_t_staff`),
  KEY `idx_peminjaman_tanggal` (`tgl_pinjam`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.t_peminjaman: ~3 rows (approximately)
/*!40000 ALTER TABLE `t_peminjaman` DISABLE KEYS */;
INSERT INTO `t_peminjaman` (`id_t_peminjaman`, `no_peminjaman`, `id_t_staff`, `id_t_anggota`, `tgl_pinjam`, `tgl_kembali`, `total_denda`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
	(1, 'PJ-1000001', 1, 4, '2016-11-01', NULL, NULL, '2016-11-01', 'adm', NULL, NULL),
	(2, 'PJ-1000002', NULL, 5, '2016-11-08', NULL, NULL, '2016-11-08', 'adm', '2016-11-08', 'adm'),
	(3, 'PJ-1000003', NULL, 3, '2016-11-08', '2016-11-08', NULL, '2016-11-08', 'adm', '2016-11-08', 'adm');
/*!40000 ALTER TABLE `t_peminjaman` ENABLE KEYS */;


-- Dumping structure for table yudhatpm_perpustakaan.t_staff
CREATE TABLE IF NOT EXISTS `t_staff` (
  `id_t_staff` int(11) NOT NULL AUTO_INCREMENT,
  `id_t_account` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NULL,
  `status` varchar(20) NOT NULL COMMENT 'aktif/tidak aktif',
  `create_date` datetime NOT NULL,
  `create_by` varchar(30) NOT NULL COMMENT 'Username',
  `update_date` datetime DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL COMMENT 'Username',
  PRIMARY KEY (`id_t_staff`),
  UNIQUE KEY `uk_staff_account` (`id_t_account`),
  KEY `idx_staff_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yudhatpm_perpustakaan.t_staff: ~1 rows (approximately)
/*!40000 ALTER TABLE `t_staff` DISABLE KEYS */;
INSERT INTO `t_staff` (`id_t_staff`, `id_t_account`, `nama`, `alamat`, `status`, `create_date`, `create_by`, `update_date`, `update_by`) VALUES
	(1, 2, 'Putra', 'Bogor,Cibinong', 'Aktif', '2016-11-01 15:30:33', 'adm', '2016-11-08 00:00:00', 'adm');
/*!40000 ALTER TABLE `t_staff` ENABLE KEYS */;

DROP VIEW IF EXISTS `v_detil_pinjam`;
CREATE VIEW `v_detil_pinjam` AS
SELECT p.id_t_peminjaman,
       d.id_t_detil_pinjam,
       b.id_t_buku,
       b.nama_buku,
       b.penulis,
       b.harga,
       d.tgl_kembali,
       d.qty,
       d.kondisi,
       d.denda,
       d.keterangan
FROM t_peminjaman p
JOIN t_detil_pinjam d ON p.id_t_peminjaman = d.id_t_peminjaman
JOIN t_buku b ON d.id_t_buku = b.id_t_buku;

DROP VIEW IF EXISTS `v_history_peminjaman`;
CREATE VIEW `v_history_peminjaman` AS
SELECT a.id_t_anggota,
       a.no_anggota,
       a.nama,
       a.tgl_daftar,
       MAX(p.tgl_pinjam) AS tgl_terakhir_pinjam
FROM t_anggota a
LEFT JOIN t_peminjaman p ON p.id_t_anggota = a.id_t_anggota
GROUP BY a.id_t_anggota, a.no_anggota, a.nama, a.tgl_daftar;

DROP VIEW IF EXISTS `v_peminjaman`;
CREATE VIEW `v_peminjaman` AS
SELECT p.id_t_peminjaman,
       p.no_peminjaman,
       COALESCE(s.nama, 'Admin') AS staff,
       p.tgl_pinjam,
       p.tgl_kembali,
       a.no_anggota,
       a.nama AS anggota,
       ac.username,
       ac.id_t_account AS ID,
       a.id_t_anggota,
       COALESCE(SUM(d.qty), 0) AS jum
FROM t_peminjaman p
JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota
LEFT JOIN t_staff s ON p.id_t_staff = s.id_t_staff
JOIN t_account ac ON a.id_t_account = ac.id_t_account
LEFT JOIN t_detil_pinjam d ON d.id_t_peminjaman = p.id_t_peminjaman
GROUP BY p.id_t_peminjaman, p.no_peminjaman, s.nama, p.tgl_pinjam, p.tgl_kembali,
         a.no_anggota, a.nama, ac.username, ac.id_t_account, a.id_t_anggota;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
