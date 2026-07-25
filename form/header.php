<?php
require_once __DIR__ . '/../setting/session.php';

$usersession = (string) $current_account['username'];
$idnya = (int) $current_account['id_t_account'];
$roleId = (int) $current_account['id_p_role'];
if (!in_array($roleId, [1, 2, 3], true)) {
    redirect_to('../logout.php');
}

$currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? 'dashboard.php'));
$roleNames = [1 => 'Administrator', 2 => 'Staff Perpustakaan', 3 => 'Anggota'];
$roleName = $roleNames[$roleId] ?? 'Pengguna';
$userInitial = strtoupper(substr($usersession !== '' ? $usersession : 'P', 0, 1));

$peminjamanPages = ['data_peminjaman.php', 'input_peminjaman.php', 'detil_peminjaman.php', 'edit_detil_pinjam.php', 'laporan_peminjaman.php'];
$bukuPages = ['data_buku.php', 'input_buku.php', 'sinopsis_buku.php', 'laporan_buku.php'];
$anggotaPages = ['data_anggota.php', 'input_anggota.php', 'history_peminjaman.php', 'laporan_anggota.php'];
$staffPages = ['data_staff.php', 'input_staff.php', 'laporan_staff.php'];
$accountPages = ['profile.php', 'ganti_password.php'];

$isPeminjamanActive = in_array($currentPage, $peminjamanPages, true);
$isBukuActive = in_array($currentPage, $bukuPages, true);
$isAnggotaActive = in_array($currentPage, $anggotaPages, true);
$isStaffActive = in_array($currentPage, $staffPages, true);
$isAccountActive = in_array($currentPage, $accountPages, true);

$pageTitles = [
    'dashboard.php' => 'Dashboard',
    'data_peminjaman.php' => 'Data Peminjaman',
    'input_peminjaman.php' => 'Kelola Peminjaman',
    'detil_peminjaman.php' => 'Detail Peminjaman',
    'edit_detil_pinjam.php' => 'Perbarui Detail Peminjaman',
    'laporan_peminjaman.php' => 'Laporan Peminjaman',
    'data_buku.php' => 'Katalog Buku',
    'input_buku.php' => 'Kelola Data Buku',
    'sinopsis_buku.php' => 'Detail Buku',
    'laporan_buku.php' => 'Laporan Buku',
    'data_anggota.php' => 'Data Anggota',
    'input_anggota.php' => 'Kelola Data Anggota',
    'history_peminjaman.php' => 'Riwayat Peminjaman',
    'laporan_anggota.php' => 'Laporan Anggota',
    'data_staff.php' => 'Data Staff',
    'input_staff.php' => 'Kelola Data Staff',
    'laporan_staff.php' => 'Laporan Staff',
    'profile.php' => 'Profil Pengguna',
    'ganti_password.php' => 'Keamanan Akun',
];
$topbarTitle = $pageTitles[$currentPage] ?? 'Perpustakaan 5F';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title><?php echo e($topbarTitle); ?> | Perpustakaan 5F</title>
    <link rel="stylesheet" href="../template/alert.css">
    <link rel="stylesheet" href="../template/css/bootstrap.min.css">
    <link rel="stylesheet" href="../template/css/sb-admin-2.css">
    <link rel="stylesheet" href="../template/js/jquery-ui-1.13.2/jquery-ui.min.css">
    <link rel="stylesheet" href="../template/css/app-modern.css">
    <script src="../template/js/jquery-3.7.0.min.js"></script>
    <script src="../template/js/jquery-ui-1.13.2/jquery-ui.min.js"></script>
</head>
<body class="app-body">
<nav class="navbar navbar-default app-topbar" role="navigation">
    <div class="app-topbar-inner">
        <div class="app-topbar-copy">
            <button type="button" class="app-sidebar-toggle" data-app-sidebar-toggle aria-label="Buka menu navigasi">
                <span class="glyphicon glyphicon-menu-hamburger"></span>
            </button>
            <div>
                <h1 class="app-topbar-title"><?php echo e($topbarTitle); ?></h1>
                <span class="app-topbar-subtitle">Kelola koleksi, anggota, dan transaksi perpustakaan</span>
            </div>
        </div>

        <ul class="nav navbar-nav navbar-right app-account-menu">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
                    <span class="app-topbar-avatar"><?php echo e($userInitial); ?></span>
                    <span class="app-account-copy">
                        <span class="app-account-name"><?php echo e($usersession); ?></span>
                        <span class="app-account-role"><?php echo e($roleName); ?></span>
                    </span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="profile.php"><span class="glyphicon glyphicon-user"></span> Profil</a></li>
                    <li><a href="ganti_password.php"><span class="glyphicon glyphicon-lock"></span> Ganti Password</a></li>
                    <li class="divider"></li>
                    <li><a href="../logout.php"><span class="glyphicon glyphicon-off"></span> Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<div class="app-shell">
    <aside class="app-sidebar" id="appSidebar" aria-label="Navigasi utama">
        <a class="app-brand" href="dashboard.php">
            <span class="app-brand-mark"><span class="glyphicon glyphicon-book"></span></span>
            <span class="app-brand-copy">
                <span class="app-brand-name">Perpustakaan 5F</span>
                <span class="app-brand-caption">Library Management</span>
            </span>
        </a>

        <div class="app-user-card">
            <span class="app-user-avatar"><?php echo e($userInitial); ?></span>
            <span class="app-user-copy">
                <span class="app-user-name"><?php echo e($usersession); ?></span>
                <span class="app-user-role"><?php echo e($roleName); ?></span>
            </span>
        </div>

        <span class="app-nav-label">Menu Utama</span>
        <ul class="nav nav-sidebar">
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><span class="glyphicon glyphicon-home"></span> Dashboard</a>
            </li>

            <?php if (in_array($roleId, [1, 2], true)): ?>
                <li class="parent <?php echo $isPeminjamanActive ? 'active' : ''; ?>">
                    <a href="#sub-peminjaman" data-toggle="collapse" aria-expanded="<?php echo $isPeminjamanActive ? 'true' : 'false'; ?>">
                        <span class="glyphicon glyphicon-transfer"></span> Peminjaman
                        <span class="glyphicon glyphicon-chevron-down nav-caret"></span>
                    </a>
                    <ul class="children collapse <?php echo $isPeminjamanActive ? 'in' : ''; ?>" id="sub-peminjaman">
                        <li class="<?php echo $currentPage === 'data_peminjaman.php' ? 'active' : ''; ?>"><a href="data_peminjaman.php">Data Peminjaman</a></li>
                        <li class="<?php echo in_array($currentPage, ['input_peminjaman.php', 'detil_peminjaman.php', 'edit_detil_pinjam.php'], true) ? 'active' : ''; ?>"><a href="input_peminjaman.php">Input Peminjaman</a></li>
                        <li class="<?php echo $currentPage === 'laporan_peminjaman.php' ? 'active' : ''; ?>"><a href="laporan_peminjaman.php">Laporan Peminjaman</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="parent <?php echo $isBukuActive ? 'active' : ''; ?>">
                <a href="#sub-buku" data-toggle="collapse" aria-expanded="<?php echo $isBukuActive ? 'true' : 'false'; ?>">
                    <span class="glyphicon glyphicon-book"></span> Buku
                    <span class="glyphicon glyphicon-chevron-down nav-caret"></span>
                </a>
                <ul class="children collapse <?php echo $isBukuActive ? 'in' : ''; ?>" id="sub-buku">
                    <li class="<?php echo in_array($currentPage, ['data_buku.php', 'sinopsis_buku.php'], true) ? 'active' : ''; ?>"><a href="data_buku.php">Data Buku</a></li>
                    <?php if (in_array($roleId, [1, 2], true)): ?>
                        <li class="<?php echo $currentPage === 'input_buku.php' ? 'active' : ''; ?>"><a href="input_buku.php">Input Buku</a></li>
                        <li class="<?php echo $currentPage === 'laporan_buku.php' ? 'active' : ''; ?>"><a href="laporan_buku.php">Laporan Buku</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <?php if (in_array($roleId, [1, 2], true)): ?>
                <li class="parent <?php echo $isAnggotaActive ? 'active' : ''; ?>">
                    <a href="#sub-anggota" data-toggle="collapse" aria-expanded="<?php echo $isAnggotaActive ? 'true' : 'false'; ?>">
                        <span class="glyphicon glyphicon-user"></span> Anggota
                        <span class="glyphicon glyphicon-chevron-down nav-caret"></span>
                    </a>
                    <ul class="children collapse <?php echo $isAnggotaActive ? 'in' : ''; ?>" id="sub-anggota">
                        <li class="<?php echo in_array($currentPage, ['data_anggota.php', 'input_anggota.php', 'history_peminjaman.php'], true) ? 'active' : ''; ?>"><a href="data_anggota.php">Data Anggota</a></li>
                        <li class="<?php echo $currentPage === 'laporan_anggota.php' ? 'active' : ''; ?>"><a href="laporan_anggota.php">Laporan Anggota</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($roleId === 1): ?>
                <li class="parent <?php echo $isStaffActive ? 'active' : ''; ?>">
                    <a href="#sub-staff" data-toggle="collapse" aria-expanded="<?php echo $isStaffActive ? 'true' : 'false'; ?>">
                        <span class="glyphicon glyphicon-briefcase"></span> Staff
                        <span class="glyphicon glyphicon-chevron-down nav-caret"></span>
                    </a>
                    <ul class="children collapse <?php echo $isStaffActive ? 'in' : ''; ?>" id="sub-staff">
                        <li class="<?php echo $currentPage === 'data_staff.php' ? 'active' : ''; ?>"><a href="data_staff.php">Data Staff</a></li>
                        <li class="<?php echo $currentPage === 'input_staff.php' ? 'active' : ''; ?>"><a href="input_staff.php">Input Staff</a></li>
                        <li class="<?php echo $currentPage === 'laporan_staff.php' ? 'active' : ''; ?>"><a href="laporan_staff.php">Laporan Staff</a></li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>

        <span class="app-nav-label">Akun</span>
        <ul class="nav nav-sidebar">
            <li class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>"><a href="profile.php"><span class="glyphicon glyphicon-user"></span> Profil Saya</a></li>
            <li class="<?php echo $currentPage === 'ganti_password.php' ? 'active' : ''; ?>"><a href="ganti_password.php"><span class="glyphicon glyphicon-lock"></span> Ganti Password</a></li>
            <li><a href="../logout.php"><span class="glyphicon glyphicon-off"></span> Logout</a></li>
        </ul>
    </aside>
    <button type="button" class="app-sidebar-overlay" data-app-sidebar-close aria-label="Tutup menu navigasi"></button>
