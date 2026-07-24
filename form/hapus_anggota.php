<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');
verify_csrf($_GET['csrf'] ?? null);

$anggotaId = request_int($_GET, 'id');
if ($anggotaId <= 0) {
    redirect_to('data_anggota.php');
}

$stmt = mysqli_prepare($db, 'SELECT id_t_account FROM t_anggota WHERE id_t_anggota = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $anggotaId);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$member) {
    redirect_to('data_anggota.php');
}

$stmt = mysqli_prepare($db, 'SELECT COUNT(*) AS jumlah FROM t_peminjaman WHERE id_t_anggota = ?');
mysqli_stmt_bind_param($stmt, 'i', $anggotaId);
mysqli_stmt_execute($stmt);
$hasHistory = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jumlah'] ?? 0) > 0;
mysqli_stmt_close($stmt);

if ($hasHistory) {
    $actor = (string) $account['username'];
    $update = mysqli_prepare($db, "UPDATE t_anggota SET status = 'Tidak Aktif', update_date = CURDATE(), update_by = ? WHERE id_t_anggota = ?");
    mysqli_stmt_bind_param($update, 'si', $actor, $anggotaId);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);
    redirect_to('data_anggota.php?info=dinonaktifkan');
}

mysqli_begin_transaction($db);
try {
    $deleteMember = mysqli_prepare($db, 'DELETE FROM t_anggota WHERE id_t_anggota = ?');
    mysqli_stmt_bind_param($deleteMember, 'i', $anggotaId);
    mysqli_stmt_execute($deleteMember);
    mysqli_stmt_close($deleteMember);

    $accountId = (int) $member['id_t_account'];
    $deleteAccount = mysqli_prepare($db, 'DELETE FROM t_account WHERE id_t_account = ? AND id_p_role = 3');
    mysqli_stmt_bind_param($deleteAccount, 'i', $accountId);
    mysqli_stmt_execute($deleteAccount);
    mysqli_stmt_close($deleteAccount);

    mysqli_commit($db);
} catch (Throwable $exception) {
    mysqli_rollback($db);
    error_log($exception->getMessage());
}
redirect_to('data_anggota.php');
