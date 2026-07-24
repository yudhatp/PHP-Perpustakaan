<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_login($db, '../index.php');
$peminjamanId = request_int($_GET, 'id');
if ($peminjamanId <= 0) {
    redirect_to('dashboard.php');
}

$sql = "SELECT p.id_t_peminjaman, p.no_peminjaman, p.tgl_pinjam, p.tgl_kembali, p.total_denda,
               p.id_t_anggota, a.no_anggota, a.nama AS anggota, a.id_t_account,
               COALESCE(s.nama, 'Admin') AS staff
        FROM t_peminjaman p
        JOIN t_anggota a ON a.id_t_anggota = p.id_t_anggota
        LEFT JOIN t_staff s ON s.id_t_staff = p.id_t_staff
        WHERE p.id_t_peminjaman = ? LIMIT 1";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, 'i', $peminjamanId);
mysqli_stmt_execute($stmt);
$loan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$loan) {
    redirect_to('dashboard.php');
}

if ((int) $account['id_p_role'] === 3 && (int) $loan['id_t_account'] !== (int) $account['id_t_account']) {
    redirect_to('dashboard.php');
}

$stmt = mysqli_prepare($db, 'SELECT * FROM v_detil_pinjam WHERE id_t_peminjaman = ? ORDER BY id_t_detil_pinjam ASC');
mysqli_stmt_bind_param($stmt, 'i', $peminjamanId);
mysqli_stmt_execute($stmt);
$details = mysqli_stmt_get_result($stmt);

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Detail Peminjaman</h1></div></div>
    <div class="panel panel-default"><div class="panel-body">
        <div class="row"><div class="col-md-4"><strong>No Peminjaman</strong><p><?php echo e($loan['no_peminjaman']); ?></p></div><div class="col-md-4"><strong>Anggota</strong><p><?php echo e($loan['no_anggota'] . ' - ' . $loan['anggota']); ?></p></div><div class="col-md-4"><strong>Staff</strong><p><?php echo e($loan['staff']); ?></p></div></div>
        <div class="row"><div class="col-md-4"><strong>Tanggal Pinjam</strong><p><?php echo e($loan['tgl_pinjam']); ?></p></div><div class="col-md-4"><strong>Tanggal Kembali</strong><p><?php echo e($loan['tgl_kembali'] ?: '-'); ?></p></div><div class="col-md-4"><strong>Total Denda</strong><p>Rp <?php echo number_format((int) ($loan['total_denda'] ?? 0), 0, ',', '.'); ?></p></div></div>
    </div></div>
    <div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>No</th><th>Buku</th><th>Penulis</th><th>Qty</th><th>Tanggal Kembali</th><th>Kondisi</th><th>Denda</th><th>Keterangan</th></tr></thead><tbody>
    <?php $no = 1; $hasRows = false; while ($detail = mysqli_fetch_assoc($details)): $hasRows = true; ?><tr><td><?php echo $no++; ?></td><td><?php echo e($detail['nama_buku']); ?></td><td><?php echo e($detail['penulis']); ?></td><td><?php echo (int) $detail['qty']; ?></td><td><?php echo e($detail['tgl_kembali'] ?: '-'); ?></td><td><?php echo e($detail['kondisi'] ?: 'Dipinjam'); ?></td><td>Rp <?php echo number_format((int) ($detail['denda'] ?? 0), 0, ',', '.'); ?></td><td><?php echo e($detail['keterangan']); ?></td></tr><?php endwhile; mysqli_stmt_close($stmt); ?>
    <?php if (!$hasRows): ?><tr><td colspan="8" class="text-center">Belum ada buku pada transaksi ini.</td></tr><?php endif; ?>
    </tbody></table></div>
    <a href="<?php echo (int) $account['id_p_role'] === 3 ? 'history_peminjaman.php?id=' . (int) $loan['id_t_anggota'] : 'data_peminjaman.php'; ?>" class="btn btn-default">Kembali</a>
</div>
<?php include __DIR__ . '/footer.php'; ?>
