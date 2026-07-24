<?php
require_once __DIR__ . '/../setting/session.php';
require_roles($db, [1], 'dashboard.php');

$nama = request_string($_GET, 'txtNama');
$alamat = request_string($_GET, 'txtAlamat');
$status = request_string($_GET, 'txtStatus', 'Semua');
$page = max(1, request_int($_GET, 'hal', 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
if ($nama !== '') {
    $where[] = "nama LIKE '%" . mysqli_real_escape_string($db, $nama) . "%'";
}
if ($alamat !== '') {
    $where[] = "alamat LIKE '%" . mysqli_real_escape_string($db, $alamat) . "%'";
}
if (in_array($status, ['Aktif', 'Tidak Aktif'], true)) {
    $where[] = "status = '" . mysqli_real_escape_string($db, $status) . "'";
}
$whereSql = implode(' AND ', $where);

$countResult = mysqli_query($db, "SELECT COUNT(*) AS jumlah FROM t_staff WHERE $whereSql");
$total = (int) (mysqli_fetch_assoc($countResult)['jumlah'] ?? 0);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}
$result = mysqli_query($db, "SELECT * FROM t_staff WHERE $whereSql ORDER BY nama ASC LIMIT $offset, $perPage");
$info = request_string($_GET, 'info');

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Data Staff</h1></div></div>
    <?php if ($info === 'dinonaktifkan'): ?><div class="alert alert-info">Staff memiliki riwayat transaksi sehingga akun dinonaktifkan, bukan dihapus.</div><?php endif; ?>
    <form method="get" class="panel panel-default"><div class="panel-body">
        <div class="row">
            <div class="col-md-4"><label>Nama</label><input type="text" class="form-control" name="txtNama" value="<?php echo e($nama); ?>"></div>
            <div class="col-md-4"><label>Alamat</label><input type="text" class="form-control" name="txtAlamat" value="<?php echo e($alamat); ?>"></div>
            <div class="col-md-4"><label>Status</label><select name="txtStatus" class="form-control"><option>Semua</option><option value="Aktif" <?php echo $status === 'Aktif' ? 'selected' : ''; ?>>Aktif</option><option value="Tidak Aktif" <?php echo $status === 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option></select></div>
        </div><br><button type="submit" class="btn btn-primary">Cari</button> <a href="data_staff.php" class="btn btn-default">Reset</a> <a href="input_staff.php" class="btn btn-success pull-right">Tambah Staff</a>
    </div></form>

    <div class="table-responsive"><table class="table table-striped table-bordered table-hover"><thead><tr><th>No</th><th>Nama</th><th>Alamat</th><th>Status</th><th>Tanggal Daftar</th><th colspan="2">Aksi</th></tr></thead><tbody>
    <?php $no = $offset + 1; while ($staff = mysqli_fetch_assoc($result)): ?><tr>
        <td><?php echo $no++; ?></td><td><?php echo e($staff['nama']); ?></td><td><?php echo e($staff['alamat']); ?></td><td><?php echo e($staff['status']); ?></td><td><?php echo e($staff['create_date']); ?></td>
        <td><a href="input_staff.php?id=<?php echo (int) $staff['id_t_staff']; ?>" class="btn btn-info btn-sm">Edit</a></td>
        <td><a href="hapus_staff.php?id=<?php echo (int) $staff['id_t_staff']; ?>&csrf=<?php echo urlencode(csrf_token()); ?>" onclick="return confirm('Hapus atau nonaktifkan staff ini?')" class="btn btn-danger btn-sm">Hapus</a></td>
    </tr><?php endwhile; ?>
    <?php if ($total === 0): ?><tr><td colspan="7" class="text-center">Data tidak ditemukan.</td></tr><?php endif; ?>
    </tbody></table></div>
    <?php if ($totalPages > 1): ?><nav><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): $url = '?' . http_build_query(array_merge($_GET, ['hal' => $i])); ?><li class="<?php echo $i === $page ? 'active' : ''; ?>"><a href="<?php echo e($url); ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
