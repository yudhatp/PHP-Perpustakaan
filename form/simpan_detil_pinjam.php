<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('data_peminjaman.php');
}
verify_csrf($_POST['csrf_token'] ?? null);

$peminjamanId = request_int($_GET, 'id');
$bukuId = request_int($_POST, 'idbuku');
$qty = request_int($_POST, 'qty');

if ($peminjamanId <= 0 || $bukuId <= 0 || $qty <= 0 || $qty > 99) {
    redirect_to('data_peminjaman.php');
}

mysqli_begin_transaction($db);
try {
    $loanStmt = mysqli_prepare($db, 'SELECT id_t_peminjaman, tgl_kembali FROM t_peminjaman WHERE id_t_peminjaman = ? FOR UPDATE');
    mysqli_stmt_bind_param($loanStmt, 'i', $peminjamanId);
    mysqli_stmt_execute($loanStmt);
    $loan = mysqli_fetch_assoc(mysqli_stmt_get_result($loanStmt));
    mysqli_stmt_close($loanStmt);

    if (!$loan || !empty($loan['tgl_kembali'])) {
        throw new RuntimeException('Peminjaman tidak ditemukan atau sudah selesai.');
    }

    $bookStmt = mysqli_prepare($db, 'SELECT stok FROM t_buku WHERE id_t_buku = ? FOR UPDATE');
    mysqli_stmt_bind_param($bookStmt, 'i', $bukuId);
    mysqli_stmt_execute($bookStmt);
    $book = mysqli_fetch_assoc(mysqli_stmt_get_result($bookStmt));
    mysqli_stmt_close($bookStmt);

    if (!$book || (int) $book['stok'] < $qty) {
        mysqli_rollback($db);
        redirect_to('input_peminjaman.php?id=' . $peminjamanId . '&error=stok_kurang');
    }

    $duplicateStmt = mysqli_prepare($db, 'SELECT id_t_detil_pinjam FROM t_detil_pinjam WHERE id_t_peminjaman = ? AND id_t_buku = ? AND tgl_kembali IS NULL LIMIT 1');
    mysqli_stmt_bind_param($duplicateStmt, 'ii', $peminjamanId, $bukuId);
    mysqli_stmt_execute($duplicateStmt);
    $duplicate = mysqli_fetch_assoc(mysqli_stmt_get_result($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);

    if ($duplicate) {
        throw new RuntimeException('Buku sudah ada dalam peminjaman ini.');
    }

    $insert = mysqli_prepare($db, 'INSERT INTO t_detil_pinjam (id_t_peminjaman, id_t_buku, qty) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($insert, 'iii', $peminjamanId, $bukuId, $qty);
    if (!mysqli_stmt_execute($insert)) {
        throw new RuntimeException(mysqli_stmt_error($insert));
    }
    mysqli_stmt_close($insert);

    $stock = mysqli_prepare($db, 'UPDATE t_buku SET stok = stok - ? WHERE id_t_buku = ? AND stok >= ?');
    mysqli_stmt_bind_param($stock, 'iii', $qty, $bukuId, $qty);
    mysqli_stmt_execute($stock);
    if (mysqli_stmt_affected_rows($stock) !== 1) {
        throw new RuntimeException('Stok berubah saat transaksi diproses.');
    }
    mysqli_stmt_close($stock);

    $username = (string) $account['username'];
    $updateLoan = mysqli_prepare($db, 'UPDATE t_peminjaman SET update_date = CURDATE(), update_by = ? WHERE id_t_peminjaman = ?');
    mysqli_stmt_bind_param($updateLoan, 'si', $username, $peminjamanId);
    mysqli_stmt_execute($updateLoan);
    mysqli_stmt_close($updateLoan);

    mysqli_commit($db);
    redirect_to('input_peminjaman.php?id=' . $peminjamanId);
} catch (Throwable $exception) {
    mysqli_rollback($db);
    error_log($exception->getMessage());
    redirect_to('input_peminjaman.php?id=' . $peminjamanId . '&error=gagal_simpan');
}
