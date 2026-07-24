<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2, 3], 'dashboard.php');

$roleId = (int) $account['id_p_role'];
$nama = request_string($_GET, 'txtNama');
$tahun = request_int($_GET, 'txtTahun');
$penulis = request_string($_GET, 'txtPenulis');
$jenis = request_string($_GET, 'txtJenis', 'Semua');
$penerbit = request_string($_GET, 'txtPenerbit');
$page = max(1, request_int($_GET, 'hal', 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
if ($nama !== '') {
    $where[] = "nama_buku LIKE '%" . mysqli_real_escape_string($db, $nama) . "%'";
}
if ($tahun > 0) {
    $where[] = 'tahun_terbit = ' . $tahun;
}
if ($penulis !== '') {
    $where[] = "penulis LIKE '%" . mysqli_real_escape_string($db, $penulis) . "%'";
}
$allowedTypes = ['Umum', 'Komputer', 'Novel', 'Pengembangan Diri', 'Komik'];
if (in_array($jenis, $allowedTypes, true)) {
    $where[] = "jenis = '" . mysqli_real_escape_string($db, $jenis) . "'";
}
if ($penerbit !== '') {
    $where[] = "penerbit LIKE '%" . mysqli_real_escape_string($db, $penerbit) . "%'";
}
$whereSql = implode(' AND ', $where);

$countResult = mysqli_query($db, "SELECT COUNT(*) AS jumlah FROM t_buku WHERE $whereSql");
$total = (int) (mysqli_fetch_assoc($countResult)['jumlah'] ?? 0);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$result = mysqli_query($db, "SELECT * FROM t_buku WHERE $whereSql ORDER BY nama_buku ASC LIMIT $offset, $perPage");
$errorCode = request_string($_GET, 'error');

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Data Buku</h1></div></div>

    <?php if ($errorCode === 'buku_digunakan'): ?>
        <div class="alert alert-warning">Buku tidak dapat dihapus karena sudah memiliki riwayat peminjaman.</div>
    <?php endif; ?>

    <form method="get" class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"><label>Nama Buku</label><input type="text" class="form-control" name="txtNama" value="<?php echo e($nama); ?>"></div>
                <div class="col-md-2"><label>Tahun</label><input type="number" class="form-control" name="txtTahun" value="<?php echo $tahun > 0 ? $tahun : ''; ?>"></div>
                <div class="col-md-2"><label>Penulis</label><input type="text" class="form-control" name="txtPenulis" value="<?php echo e($penulis); ?>"></div>
                <div class="col-md-2"><label>Jenis</label><select class="form-control" name="txtJenis"><option>Semua</option><?php foreach ($allowedTypes as $type): ?><option value="<?php echo e($type); ?>" <?php echo $jenis === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label>Penerbit</label><input type="text" class="form-control" name="txtPenerbit" value="<?php echo e($penerbit); ?>"></div>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="data_buku.php" class="btn btn-default">Reset</a>
            <?php if ($roleId !== 3): ?><a href="input_buku.php" class="btn btn-success pull-right">Tambah Buku</a><?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr><th>No</th><th>Cover</th><th>Nama Buku</th><th>Jenis</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>Rak</th><th>Stok</th><?php if ($roleId !== 3): ?><th colspan="2">Aksi</th><?php endif; ?></tr></thead>
            <tbody>
            <?php $no = $offset + 1; while ($book = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php if (!empty($book['gambar'])): ?><img src="../image/buku/<?php echo rawurlencode(basename($book['gambar'])); ?>" alt="Cover" style="width:55px;height:75px;object-fit:cover"><?php else: ?>-<?php endif; ?></td>
                    <td><a href="sinopsis_buku.php?id=<?php echo (int) $book['id_t_buku']; ?>"><?php echo e($book['nama_buku']); ?></a></td>
                    <td><?php echo e($book['jenis']); ?></td>
                    <td><?php echo e($book['penulis']); ?></td>
                    <td><?php echo e($book['penerbit']); ?></td>
                    <td><?php echo e($book['tahun_terbit']); ?></td>
                    <td><?php echo e($book['kode_rak']); ?></td>
                    <td><?php echo (int) $book['stok']; ?></td>
                    <?php if ($roleId !== 3): ?>
                        <td><a href="input_buku.php?id=<?php echo (int) $book['id_t_buku']; ?>" class="btn btn-info btn-sm">Edit</a></td>
                        <td><a href="hapus_buku.php?id=<?php echo (int) $book['id_t_buku']; ?>&csrf=<?php echo urlencode(csrf_token()); ?>" onclick="return confirm('Hapus buku ini?')" class="btn btn-danger btn-sm">Hapus</a></td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
            <?php if ($total === 0): ?><tr><td colspan="11" class="text-center">Data tidak ditemukan.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav><ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): $url = '?' . http_build_query(array_merge($_GET, ['hal' => $i])); ?>
                <li class="<?php echo $i === $page ? 'active' : ''; ?>"><a href="<?php echo e($url); ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
