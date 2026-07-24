<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');
verify_csrf($_GET['csrf'] ?? null);

$peminjamanId = request_int($_GET, 'id');
$bukuId = request_int($_GET, 'id_buku');
if ($peminjamanId <= 0 || $bukuId <= 0) {
    redirect_to('data_peminjaman.php');
}

mysqli_begin_transaction($db);
try {
    $stmt = mysqli_prepare($db, 'SELECT id_t_detil_pinjam, qty, tgl_kembali FROM t_detil_pinjam WHERE id_t_peminjaman = ? AND id_t_buku = ? FOR UPDATE');
    mysqli_stmt_bind_param($stmt, 'ii', $peminjamanId, $bukuId);
    mysqli_stmt_execute($stmt);
    $detail = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$detail || !empty($detail['tgl_kembali'])) {
        throw new RuntimeException('Detail peminjaman tidak ditemukan atau sudah dikembalikan.');
    }

    $qty = (int) $detail['qty'];
    $stock = mysqli_prepare($db, 'UPDATE t_buku SET stok = stok + ? WHERE id_t_buku = ?');
    mysqli_stmt_bind_param($stock, 'ii', $qty, $bukuId);
    mysqli_stmt_execute($stock);
    mysqli_stmt_close($stock);

    $detailId = (int) $detail['id_t_detil_pinjam'];
    $delete = mysqli_prepare($db, 'DELETE FROM t_detil_pinjam WHERE id_t_detil_pinjam = ?');
    mysqli_stmt_bind_param($delete, 'i', $detailId);
    mysqli_stmt_execute($delete);
    mysqli_stmt_close($delete);

    $username = (string) $account['username'];
    $update = mysqli_prepare($db, 'UPDATE t_peminjaman SET update_date = CURDATE(), update_by = ? WHERE id_t_peminjaman = ?');
    mysqli_stmt_bind_param($update, 'si', $username, $peminjamanId);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);

    mysqli_commit($db);
} catch (Throwable $exception) {
    mysqli_rollback($db);
    error_log($exception->getMessage());
}

redirect_to('input_peminjaman.php?id=' . $peminjamanId);
