<?php
require_once __DIR__ . '/../setting/session.php';
require_roles($db, [1, 2], 'dashboard.php');

$nomor = request_string($_GET, 'txtNo');
$nama = request_string($_GET, 'txtNama');
$tanggal = request_string($_GET, 'txtTgl');
$page = max(1, request_int($_GET, 'hal', 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
if ($nomor !== '') {
    $where[] = "p.no_peminjaman LIKE '%" . mysqli_real_escape_string($db, $nomor) . "%'";
}
if ($nama !== '') {
    $where[] = "a.nama LIKE '%" . mysqli_real_escape_string($db, $nama) . "%'";
}
if ($tanggal !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $where[] = "p.tgl_pinjam = '" . mysqli_real_escape_string($db, $tanggal) . "'";
}
$whereSql = implode(' AND ', $where);

$countSql = "SELECT COUNT(*) AS jumlah FROM t_peminjaman p JOIN t_anggota a ON a.id_t_anggota = p.id_t_anggota WHERE $whereSql";
$countResult = mysqli_query($db, $countSql);
$total = (int) (mysqli_fetch_assoc($countResult)['jumlah'] ?? 0);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$sql = "SELECT p.id_t_peminjaman, p.no_peminjaman, COALESCE(s.nama, 'Admin') AS staff,
               p.tgl_pinjam, p.tgl_kembali, p.total_denda, a.no_anggota, a.nama AS anggota,
               COALESCE(SUM(d.qty), 0) AS jum
        FROM t_peminjaman p
        JOIN t_anggota a ON a.id_t_anggota = p.id_t_anggota
        LEFT JOIN t_staff s ON s.id_t_staff = p.id_t_staff
        LEFT JOIN t_detil_pinjam d ON d.id_t_peminjaman = p.id_t_peminjaman
        WHERE $whereSql
        GROUP BY p.id_t_peminjaman, p.no_peminjaman, s.nama, p.tgl_pinjam, p.tgl_kembali,
                 p.total_denda, a.no_anggota, a.nama
        ORDER BY p.id_t_peminjaman DESC
        LIMIT $offset, $perPage";
$result = mysqli_query($db, $sql);

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Data Peminjaman</h1></div></div>
    <form method="get" class="panel panel-default"><div class="panel-body">
        <div class="row">
            <div class="col-md-4"><label>No Peminjaman</label><input type="text" class="form-control" name="txtNo" value="<?php echo e($nomor); ?>"></div>
            <div class="col-md-4"><label>Nama Anggota</label><input type="text" class="form-control" name="txtNama" value="<?php echo e($nama); ?>"></div>
            <div class="col-md-4"><label>Tanggal Peminjaman</label><input type="date" id="txtTgl" class="form-control" name="txtTgl" value="<?php echo e($tanggal); ?>"></div>
        </div><br>
        <button type="submit" class="btn btn-primary">Cari</button> <a href="data_peminjaman.php" class="btn btn-default">Reset</a> <a href="input_peminjaman.php" class="btn btn-success pull-right">Tambah Peminjaman</a>
    </div></form>

    <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
        <thead><tr><th>No</th><th>No Peminjaman</th><th>Staff</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th><th>No Anggota</th><th>Nama Anggota</th><th>Jumlah Buku</th><th>Total Denda</th><th colspan="2">Aksi</th></tr></thead>
        <tbody>
        <?php $no = $offset + 1; while ($loan = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo e($loan['no_peminjaman']); ?></td>
                <td><?php echo e($loan['staff']); ?></td>
                <td><?php echo e($loan['tgl_pinjam']); ?></td>
                <td><?php echo e($loan['tgl_kembali'] ?: '-'); ?></td>
                <td><?php echo e($loan['no_anggota']); ?></td>
                <td><?php echo e($loan['anggota']); ?></td>
                <td><?php echo (int) $loan['jum']; ?></td>
                <td>Rp <?php echo number_format((int) ($loan['total_denda'] ?? 0), 0, ',', '.'); ?></td>
                <td><a href="input_peminjaman.php?id=<?php echo (int) $loan['id_t_peminjaman']; ?>" class="btn btn-info btn-sm">Kelola</a></td>
                <td><a href="detil_peminjaman.php?id=<?php echo (int) $loan['id_t_peminjaman']; ?>" class="btn btn-warning btn-sm">Detail</a></td>
            </tr>
        <?php endwhile; ?>
        <?php if ($total === 0): ?><tr><td colspan="11" class="text-center">Data tidak ditemukan.</td></tr><?php endif; ?>
        </tbody>
    </table></div>

    <?php if ($totalPages > 1): ?><nav><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): $url = '?' . http_build_query(array_merge($_GET, ['hal' => $i])); ?><li class="<?php echo $i === $page ? 'active' : ''; ?>"><a href="<?php echo e($url); ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
