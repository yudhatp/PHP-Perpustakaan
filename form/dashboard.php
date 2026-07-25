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

    <section class="dashboard-welcome">
        <div class="dashboard-welcome-copy">
            <h2>Selamat datang, <?php echo e((string) $account['username']); ?>.</h2>
            <p><?php echo in_array($roleId, [1, 2], true)
                ? 'Pantau aktivitas perpustakaan hari ini dan akses modul utama melalui menu di sebelah kiri.'
                : 'Lihat koleksi buku dan pantau riwayat peminjaman Anda melalui satu dashboard yang lebih ringkas.'; ?></p>
        </div>
        <span class="dashboard-welcome-icon"><span class="glyphicon glyphicon-book"></span></span>
    </section>

    <?php if (in_array($roleId, [1, 2], true)): ?>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6">
                <article class="dashboard-stat is-green">
                    <div class="dashboard-stat-main">
                        <span class="dashboard-stat-icon"><span class="glyphicon glyphicon-transfer"></span></span>
                        <div>
                            <p class="dashboard-stat-value"><?php echo $todayLoans; ?></p>
                            <div class="dashboard-stat-label">Peminjaman hari ini</div>
                        </div>
                    </div>
                    <a class="dashboard-stat-link" href="data_peminjaman.php"><span>Lihat data peminjaman</span><span class="glyphicon glyphicon-arrow-right"></span></a>
                </article>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <article class="dashboard-stat is-blue">
                    <div class="dashboard-stat-main">
                        <span class="dashboard-stat-icon"><span class="glyphicon glyphicon-user"></span></span>
                        <div>
                            <p class="dashboard-stat-value"><?php echo $todayMembers; ?></p>
                            <div class="dashboard-stat-label">Anggota baru hari ini</div>
                        </div>
                    </div>
                    <a class="dashboard-stat-link" href="data_anggota.php"><span>Lihat data anggota</span><span class="glyphicon glyphicon-arrow-right"></span></a>
                </article>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <article class="dashboard-stat is-amber">
                    <div class="dashboard-stat-main">
                        <span class="dashboard-stat-icon"><span class="glyphicon glyphicon-book"></span></span>
                        <div>
                            <p class="dashboard-stat-value"><?php echo $todayBooks; ?></p>
                            <div class="dashboard-stat-label">Buku baru hari ini</div>
                        </div>
                    </div>
                    <a class="dashboard-stat-link" href="data_buku.php"><span>Lihat katalog buku</span><span class="glyphicon glyphicon-arrow-right"></span></a>
                </article>
            </div>
        </div>
    <?php else: ?>
        <?php if ($memberId <= 0): ?>
            <div class="alert alert-warning">Profil anggota belum lengkap. <a href="input_anggota.php">Lengkapi profil sekarang</a> agar seluruh fitur anggota dapat digunakan.</div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-4 col-md-5 col-sm-6">
                    <article class="dashboard-stat is-blue">
                        <div class="dashboard-stat-main">
                            <span class="dashboard-stat-icon"><span class="glyphicon glyphicon-list-alt"></span></span>
                            <div>
                                <p class="dashboard-stat-value"><?php echo $totalMemberLoans; ?></p>
                                <div class="dashboard-stat-label">Total riwayat peminjaman</div>
                            </div>
                        </div>
                        <a class="dashboard-stat-link" href="history_peminjaman.php?id=<?php echo $memberId; ?>"><span>Lihat riwayat peminjaman</span><span class="glyphicon glyphicon-arrow-right"></span></a>
                    </article>
                </div>
                <div class="col-lg-4 col-md-5 col-sm-6">
                    <article class="dashboard-stat is-green">
                        <div class="dashboard-stat-main">
                            <span class="dashboard-stat-icon"><span class="glyphicon glyphicon-book"></span></span>
                            <div>
                                <p class="dashboard-stat-value">Katalog</p>
                                <div class="dashboard-stat-label">Temukan buku yang tersedia</div>
                            </div>
                        </div>
                        <a class="dashboard-stat-link" href="data_buku.php"><span>Buka katalog buku</span><span class="glyphicon glyphicon-arrow-right"></span></a>
                    </article>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
