<?php
require_once __DIR__ . '/../setting/session.php';

$usersession = (string) $current_account['username'];
$idnya = (int) $current_account['id_t_account'];
$roleId = (int) $current_account['id_p_role'];
if (!in_array($roleId, [1, 2, 3], true)) {
    redirect_to('../logout.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan 5F</title>
    <link rel="stylesheet" href="../template/alert.css">
    <link rel="stylesheet" href="../template/css/bootstrap.min.css">
    <link rel="stylesheet" href="../template/css/sb-admin-2.css">
    <link rel="stylesheet" href="../template/js/jquery-ui-1.13.2/jquery-ui.min.css">
    <script src="../template/js/jquery-3.7.0.min.js"></script>
    <script src="../template/js/jquery-ui-1.13.2/jquery-ui.min.js"></script>
</head>
<body>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
                <span class="sr-only">Buka navigasi</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="dashboard.php">Perpustakaan 5F</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> <?php echo e($usersession); ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php"><span class="glyphicon glyphicon-tasks"></span> Profil</a></li>
                        <li><a href="ganti_password.php"><span class="glyphicon glyphicon-lock"></span> Ganti Password</a></li>
                        <li class="divider"></li>
                        <li><a href="../logout.php"><span class="glyphicon glyphicon-off"></span> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <ul class="nav nav-sidebar">
                <li><a href="dashboard.php"><span class="glyphicon glyphicon-home"></span> Dashboard</a></li>

                <?php if (in_array($roleId, [1, 2], true)): ?>
                    <li class="parent">
                        <a href="#sub-peminjaman" data-toggle="collapse"><span class="glyphicon glyphicon-transfer"></span> Peminjaman <span class="glyphicon glyphicon-chevron-down pull-right"></span></a>
                        <ul class="children collapse" id="sub-peminjaman">
                            <li><a href="data_peminjaman.php">Data Peminjaman</a></li>
                            <li><a href="input_peminjaman.php">Input Peminjaman</a></li>
                            <li><a href="laporan_peminjaman.php">Laporan Peminjaman</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <li class="parent">
                    <a href="#sub-buku" data-toggle="collapse"><span class="glyphicon glyphicon-book"></span> Buku <span class="glyphicon glyphicon-chevron-down pull-right"></span></a>
                    <ul class="children collapse" id="sub-buku">
                        <li><a href="data_buku.php">Data Buku</a></li>
                        <?php if (in_array($roleId, [1, 2], true)): ?>
                            <li><a href="input_buku.php">Input Buku</a></li>
                            <li><a href="laporan_buku.php">Laporan Buku</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <?php if (in_array($roleId, [1, 2], true)): ?>
                    <li class="parent">
                        <a href="#sub-anggota" data-toggle="collapse"><span class="glyphicon glyphicon-user"></span> Anggota <span class="glyphicon glyphicon-chevron-down pull-right"></span></a>
                        <ul class="children collapse" id="sub-anggota">
                            <li><a href="data_anggota.php">Data Anggota</a></li>
                            <li><a href="laporan_anggota.php">Laporan Anggota</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($roleId === 1): ?>
                    <li class="parent">
                        <a href="#sub-staff" data-toggle="collapse"><span class="glyphicon glyphicon-briefcase"></span> Staff <span class="glyphicon glyphicon-chevron-down pull-right"></span></a>
                        <ul class="children collapse" id="sub-staff">
                            <li><a href="data_staff.php">Data Staff</a></li>
                            <li><a href="input_staff.php">Input Staff</a></li>
                            <li><a href="laporan_staff.php">Laporan Staff</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
