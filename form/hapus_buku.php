<?php
require_once __DIR__ . '/../setting/session.php';
require_roles($db, [1, 2], 'dashboard.php');
verify_csrf($_GET['csrf'] ?? null);

$bukuId = request_int($_GET, 'id');
if ($bukuId <= 0) {
    redirect_to('data_buku.php');
}

$stmt = mysqli_prepare($db, 'SELECT COUNT(*) AS jumlah FROM t_detil_pinjam WHERE id_t_buku = ?');
mysqli_stmt_bind_param($stmt, 'i', $bukuId);
mysqli_stmt_execute($stmt);
$used = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jumlah'] ?? 0);
mysqli_stmt_close($stmt);

if ($used > 0) {
    redirect_to('data_buku.php?error=buku_digunakan');
}

$stmt = mysqli_prepare($db, 'SELECT gambar FROM t_buku WHERE id_t_buku = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $bukuId);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$delete = mysqli_prepare($db, 'DELETE FROM t_buku WHERE id_t_buku = ?');
mysqli_stmt_bind_param($delete, 'i', $bukuId);
mysqli_stmt_execute($delete);
mysqli_stmt_close($delete);

if ($book && !empty($book['gambar'])) {
    $path = __DIR__ . '/../image/buku/' . basename($book['gambar']);
    if (is_file($path)) {
        @unlink($path);
    }
}

redirect_to('data_buku.php');
