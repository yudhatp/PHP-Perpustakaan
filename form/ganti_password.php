<?php
require_once __DIR__ . '/../setting/session.php';

$error = '';
$account = $current_account;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $oldPassword = request_string($_POST, 'password_lama');
    $newPassword = request_string($_POST, 'password_baru');
    $confirmation = request_string($_POST, 'password_conf');

    $stmt = mysqli_prepare($db, 'SELECT password FROM t_account WHERE id_t_account = ? LIMIT 1');
    $accountId = (int) $account['id_t_account'];
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $stored = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$stored || !password_matches($oldPassword, (string) $stored['password'])) {
        $error = 'Password lama tidak sesuai.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($newPassword !== $confirmation) {
        $error = 'Konfirmasi password baru tidak sama.';
    } elseif (hash_equals($oldPassword, $newPassword)) {
        $error = 'Password baru harus berbeda dari password lama.';
    } else {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $username = (string) $account['username'];
        $update = mysqli_prepare($db, 'UPDATE t_account SET password = ?, update_date = NOW(), update_by = ? WHERE id_t_account = ?');
        mysqli_stmt_bind_param($update, 'ssi', $newHash, $username, $accountId);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        $_SESSION['flash_success'] = 'Password berhasil diubah.';
        redirect_to('profile.php');
    }
}

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12"><h1 class="page-header">Ganti Password</h1></div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="panel-body">
                <form method="post" autocomplete="off">
                    <?php echo csrf_field(); ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert">
                            <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                            <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group row">
                        <label class="control-label col-sm-4">Password Lama</label>
                        <div class="col-sm-8"><input type="password" class="form-control" name="password_lama" autocomplete="current-password" required></div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-4">Password Baru</label>
                        <div class="col-sm-8"><input type="password" minlength="8" maxlength="72" class="form-control" name="password_baru" autocomplete="new-password" required></div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-4">Konfirmasi Password</label>
                        <div class="col-sm-8"><input type="password" minlength="8" maxlength="72" class="form-control" name="password_conf" autocomplete="new-password" required></div>
                    </div>
                    <div class="form-group row text-right">
                        <div class="col-sm-offset-4 col-sm-8">
                            <a href="profile.php" class="btn btn-default">Batal</a>
                            <button type="submit" class="btn btn-primary">Ganti Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
