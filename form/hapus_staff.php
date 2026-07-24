<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1], 'dashboard.php');
verify_csrf($_GET['csrf'] ?? null);

$staffId = request_int($_GET, 'id');
if ($staffId <= 0) {
    redirect_to('data_staff.php');
}

$stmt = mysqli_prepare($db, 'SELECT id_t_account FROM t_staff WHERE id_t_staff = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $staffId);
mysqli_stmt_execute($stmt);
$staff = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$staff) {
    redirect_to('data_staff.php');
}

$stmt = mysqli_prepare($db, 'SELECT COUNT(*) AS jumlah FROM t_peminjaman WHERE id_t_staff = ?');
mysqli_stmt_bind_param($stmt, 'i', $staffId);
mysqli_stmt_execute($stmt);
$hasHistory = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jumlah'] ?? 0) > 0;
mysqli_stmt_close($stmt);

if ($hasHistory) {
    $actor = (string) $account['username'];
    $update = mysqli_prepare($db, "UPDATE t_staff SET status = 'Tidak Aktif', update_date = NOW(), update_by = ? WHERE id_t_staff = ?");
    mysqli_stmt_bind_param($update, 'si', $actor, $staffId);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);
    redirect_to('data_staff.php?info=dinonaktifkan');
}

mysqli_begin_transaction($db);
try {
    $deleteStaff = mysqli_prepare($db, 'DELETE FROM t_staff WHERE id_t_staff = ?');
    mysqli_stmt_bind_param($deleteStaff, 'i', $staffId);
    mysqli_stmt_execute($deleteStaff);
    mysqli_stmt_close($deleteStaff);

    $accountId = (int) $staff['id_t_account'];
    $deleteAccount = mysqli_prepare($db, 'DELETE FROM t_account WHERE id_t_account = ? AND id_p_role = 2');
    mysqli_stmt_bind_param($deleteAccount, 'i', $accountId);
    mysqli_stmt_execute($deleteAccount);
    mysqli_stmt_close($deleteAccount);

    mysqli_commit($db);
} catch (Throwable $exception) {
    mysqli_rollback($db);
    error_log($exception->getMessage());
}
redirect_to('data_staff.php');
