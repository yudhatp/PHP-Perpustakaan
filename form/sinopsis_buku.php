<?php
require_once __DIR__ . '/../setting/session.php';
require_login($db, '../index.php');
$bukuId = request_int($_GET, 'id');
if ($bukuId <= 0) {
    redirect_to('data_buku.php');
}
$stmt = mysqli_prepare($db, 'SELECT * FROM t_buku WHERE id_t_buku = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $bukuId);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$book) {
    redirect_to('data_buku.php');
}
include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Detail Buku</h1></div></div>
    <div class="row"><div class="col-md-3"><?php if (!empty($book['gambar'])): ?><img class="img-responsive img-thumbnail" src="../image/buku/<?php echo rawurlencode(basename($book['gambar'])); ?>" alt="Cover <?php echo e($book['nama_buku']); ?>"><?php endif; ?></div><div class="col-md-9"><h3><?php echo e($book['nama_buku']); ?></h3><p><strong>Penulis:</strong> <?php echo e($book['penulis']); ?></p><p><strong>Penerbit:</strong> <?php echo e($book['penerbit']); ?></p><p><strong>Tahun:</strong> <?php echo e($book['tahun_terbit']); ?></p><p><strong>Jenis:</strong> <?php echo e($book['jenis']); ?></p><p><strong>Rak/Stok:</strong> <?php echo e($book['kode_rak']); ?> / <?php echo (int) $book['stok']; ?></p><hr><p><?php echo nl2br(e($book['sinopsis'] ?: '-')); ?></p><a href="data_buku.php" class="btn btn-default">Kembali</a></div></div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
