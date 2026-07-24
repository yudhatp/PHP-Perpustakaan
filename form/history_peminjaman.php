<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_login($db, '../index.php');

$roleId = (int) $account['id_p_role'];
$anggotaId = request_int($_GET, 'id');
if ($roleId === 3) {
    $accountId = (int) $account['id_t_account'];
    $stmt = mysqli_prepare($db, 'SELECT id_t_anggota FROM t_anggota WHERE id_t_account = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $memberRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $anggotaId = (int) ($memberRow['id_t_anggota'] ?? 0);
}

if ($anggotaId <= 0) {
    redirect_to('dashboard.php');
}

$stmt = mysqli_prepare($db, 'SELECT no_anggota, nama, tgl_daftar FROM t_anggota WHERE id_t_anggota = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $anggotaId);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$member) {
    redirect_to('dashboard.php');
}

$page = max(1, request_int($_GET, 'hal', 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmt = mysqli_prepare($db, 'SELECT COUNT(*) AS jumlah FROM t_peminjaman WHERE id_t_anggota = ?');
mysqli_stmt_bind_param($stmt, 'i', $anggotaId);
mysqli_stmt_execute($stmt);
$total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jumlah'] ?? 0);
mysqli_stmt_close($stmt);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$sql = "SELECT p.id_t_peminjaman, p.no_peminjaman, p.tgl_pinjam, p.tgl_kembali, p.total_denda,
               COALESCE(s.nama, 'Admin') AS staff, COALESCE(SUM(d.qty), 0) AS jumlah_buku
        FROM t_peminjaman p
        LEFT JOIN t_staff s ON s.id_t_staff = p.id_t_staff
        LEFT JOIN t_detil_pinjam d ON d.id_t_peminjaman = p.id_t_peminjaman
        WHERE p.id_t_anggota = ?
        GROUP BY p.id_t_peminjaman, p.no_peminjaman, p.tgl_pinjam, p.tgl_kembali, p.total_denda, s.nama
        ORDER BY p.id_t_peminjaman DESC LIMIT ?, ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $anggotaId, $offset, $perPage);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Riwayat Peminjaman</h1></div></div>
    <div class="panel panel-default"><div class="panel-body">
        <div><strong>No Anggota:</strong> <?php echo e($member['no_anggota']); ?></div>
        <div><strong>Nama:</strong> <?php echo e($member['nama']); ?></div>
        <div><strong>Tanggal Daftar:</strong> <?php echo e($member['tgl_daftar']); ?></div>
    </div></div>

    <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
        <thead><tr><th>No</th><th>No Peminjaman</th><th>Staff</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th><th>Jumlah Buku</th><th>Total Denda</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php $no = $offset + 1; while ($loan = mysqli_fetch_assoc($result)): ?>
            <tr><td><?php echo $no++; ?></td><td><?php echo e($loan['no_peminjaman']); ?></td><td><?php echo e($loan['staff']); ?></td><td><?php echo e($loan['tgl_pinjam']); ?></td><td><?php echo e($loan['tgl_kembali'] ?: '-'); ?></td><td><?php echo (int) $loan['jumlah_buku']; ?></td><td>Rp <?php echo number_format((int) ($loan['total_denda'] ?? 0), 0, ',', '.'); ?></td><td><a class="btn btn-warning btn-sm" href="detil_peminjaman.php?id=<?php echo (int) $loan['id_t_peminjaman']; ?>">Detail</a></td></tr>
        <?php endwhile; mysqli_stmt_close($stmt); ?>
        <?php if ($total === 0): ?><tr><td colspan="8" class="text-center">Belum ada riwayat peminjaman.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
    <?php if ($totalPages > 1): ?><nav><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): ?><li class="<?php echo $i === $page ? 'active' : ''; ?>"><a href="?id=<?php echo $anggotaId; ?>&hal=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
