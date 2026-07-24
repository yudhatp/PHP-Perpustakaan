<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_login($db, '../index.php');
$roleId = (int) $account['id_p_role'];

$todayLoans = 0;
$todayMembers = 0;
$todayBooks = 0;
$totalMemberLoans = 0;
$memberId = 0;

if (in_array($roleId, [1, 2], true)) {
    $result = mysqli_query($db, 'SELECT COUNT(*) AS jumlah FROM t_peminjaman WHERE create_date = CURDATE()');
    $todayLoans = (int) (mysqli_fetch_assoc($result)['jumlah'] ?? 0);
    $result = mysqli_query($db, 'SELECT COUNT(*) AS jumlah FROM t_anggota WHERE create_date = CURDATE()');
    $todayMembers = (int) (mysqli_fetch_assoc($result)['jumlah'] ?? 0);
    $result = mysqli_query($db, 'SELECT COUNT(*) AS jumlah FROM t_buku WHERE create_date = CURDATE()');
    $todayBooks = (int) (mysqli_fetch_assoc($result)['jumlah'] ?? 0);
} else {
    $accountId = (int) $account['id_t_account'];
    $stmt = mysqli_prepare($db, 'SELECT id_t_anggota FROM t_anggota WHERE id_t_account = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($member) {
        $memberId = (int) $member['id_t_anggota'];
        $stmt = mysqli_prepare($db, 'SELECT COUNT(*) AS jumlah FROM t_peminjaman WHERE id_t_anggota = ?');
        mysqli_stmt_bind_param($stmt, 'i', $memberId);
        mysqli_stmt_execute($stmt);
        $totalMemberLoans = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jumlah'] ?? 0);
        mysqli_stmt_close($stmt);
    }
}

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Dashboard</h1></div></div>

    <?php if (in_array($roleId, [1, 2], true)): ?>
        <div class="row">
            <div class="col-lg-3 col-md-6"><div class="panel panel-green"><div class="panel-heading"><div class="text-right"><div class="huge"><?php echo $todayLoans; ?></div><div>Peminjaman Hari Ini</div></div></div><a href="data_peminjaman.php"><div class="panel-footer">Lihat Detail<div class="clearfix"></div></div></a></div></div>
            <div class="col-lg-3 col-md-6"><div class="panel panel-primary"><div class="panel-heading"><div class="text-right"><div class="huge"><?php echo $todayMembers; ?></div><div>Anggota Baru</div></div></div><a href="data_anggota.php"><div class="panel-footer">Lihat Detail<div class="clearfix"></div></div></a></div></div>
            <div class="col-lg-3 col-md-6"><div class="panel panel-yellow"><div class="panel-heading"><div class="text-right"><div class="huge"><?php echo $todayBooks; ?></div><div>Buku Baru</div></div></div><a href="data_buku.php"><div class="panel-footer">Lihat Detail<div class="clearfix"></div></div></a></div></div>
        </div>
    <?php else: ?>
        <?php if ($memberId <= 0): ?>
            <div class="alert alert-warning">Profil anggota belum lengkap. <a href="input_anggota.php">Lengkapi profil sekarang</a>.</div>
        <?php else: ?>
            <div class="row"><div class="col-lg-3 col-md-6"><div class="panel panel-primary"><div class="panel-heading"><div class="text-right"><div class="huge"><?php echo $totalMemberLoans; ?></div><div>Total Peminjaman</div></div></div><a href="history_peminjaman.php?id=<?php echo $memberId; ?>"><div class="panel-footer">Lihat Riwayat<div class="clearfix"></div></div></a></div></div></div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
