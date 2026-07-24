<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');

$nama = request_string($_GET, 'txtNama');
$tanggal = request_string($_GET, 'txtTgl');
$alamat = request_string($_GET, 'txtAlamat');
$status = request_string($_GET, 'txtStatus', 'Semua');
$page = max(1, request_int($_GET, 'hal', 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
if ($nama !== '') {
    $where[] = "nama LIKE '%" . mysqli_real_escape_string($db, $nama) . "%'";
}
if ($tanggal !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $where[] = "tgl_daftar = '" . mysqli_real_escape_string($db, $tanggal) . "'";
}
if ($alamat !== '') {
    $where[] = "alamat LIKE '%" . mysqli_real_escape_string($db, $alamat) . "%'";
}
if (in_array($status, ['Aktif', 'Tidak Aktif'], true)) {
    $where[] = "status = '" . mysqli_real_escape_string($db, $status) . "'";
}
$whereSql = implode(' AND ', $where);

$countResult = mysqli_query($db, "SELECT COUNT(*) AS jumlah FROM t_anggota WHERE $whereSql");
$total = (int) (mysqli_fetch_assoc($countResult)['jumlah'] ?? 0);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}
$result = mysqli_query($db, "SELECT * FROM t_anggota WHERE $whereSql ORDER BY nama ASC LIMIT $offset, $perPage");
$info = request_string($_GET, 'info');

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Data Anggota</h1></div></div>

    <?php if ($info === 'dinonaktifkan'): ?><div class="alert alert-info">Anggota memiliki riwayat peminjaman sehingga akun dinonaktifkan, bukan dihapus.</div><?php endif; ?>

    <form method="get" class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"><label>Nama</label><input type="text" class="form-control" name="txtNama" value="<?php echo e($nama); ?>"></div>
                <div class="col-md-2"><label>Tanggal Daftar</label><input type="date" id="txtTgl" class="form-control" name="txtTgl" value="<?php echo e($tanggal); ?>"></div>
                <div class="col-md-4"><label>Alamat</label><input type="text" class="form-control" name="txtAlamat" value="<?php echo e($alamat); ?>"></div>
                <div class="col-md-3"><label>Status</label><select name="txtStatus" class="form-control"><option>Semua</option><option value="Aktif" <?php echo $status === 'Aktif' ? 'selected' : ''; ?>>Aktif</option><option value="Tidak Aktif" <?php echo $status === 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option></select></div>
            </div>
            <br><button type="submit" class="btn btn-primary">Cari</button> <a href="data_anggota.php" class="btn btn-default">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr><th>No</th><th>No Anggota</th><th>Nama</th><th>Tanggal Daftar</th><th>Tanggal Lahir</th><th>Jenis Kelamin</th><th>Telepon</th><th>Alamat</th><th>Status</th><th colspan="2">Aksi</th></tr></thead>
            <tbody>
            <?php $no = $offset + 1; while ($member = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo e($member['no_anggota']); ?></td>
                    <td><?php echo e($member['nama']); ?></td>
                    <td><?php echo e($member['tgl_daftar']); ?></td>
                    <td><?php echo e($member['tgl_lahir']); ?></td>
                    <td><?php echo e($member['jenis_kelamin']); ?></td>
                    <td><?php echo e($member['no_telp']); ?></td>
                    <td><?php echo e($member['alamat']); ?></td>
                    <td><?php echo e($member['status']); ?></td>
                    <td><a href="input_anggota.php?id=<?php echo (int) $member['id_t_anggota']; ?>" class="btn btn-info btn-sm">Edit</a></td>
                    <td><a href="hapus_anggota.php?id=<?php echo (int) $member['id_t_anggota']; ?>&csrf=<?php echo urlencode(csrf_token()); ?>" onclick="return confirm('Hapus atau nonaktifkan anggota ini?')" class="btn btn-danger btn-sm">Hapus</a></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($total === 0): ?><tr><td colspan="11" class="text-center">Data tidak ditemukan.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?><nav><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): $url = '?' . http_build_query(array_merge($_GET, ['hal' => $i])); ?><li class="<?php echo $i === $page ? 'active' : ''; ?>"><a href="<?php echo e($url); ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
