<?php
require_once __DIR__ . '/setting/koneksi.php';
app_session_start();

if (!empty($_SESSION['login_user'])) {
    redirect_to('form/dashboard.php');
}

$error = '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $username = strtolower(request_string($_POST, 'username'));
    $password = request_string($_POST, 'password');

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) || $password === '') {
        $error = 'Username atau password tidak valid.';
    } else {
        $stmt = mysqli_prepare($db, 'SELECT id_t_account, username, password FROM t_account WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $account = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($account && password_matches($password, (string) $account['password'])) {
            // Migrasi otomatis password MD5 lama ke password_hash saat login berhasil.
            if (empty(password_get_info((string) $account['password'])['algo'])) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = mysqli_prepare($db, 'UPDATE t_account SET password = ?, update_date = NOW(), update_by = ? WHERE id_t_account = ?');
                $accountId = (int) $account['id_t_account'];
                mysqli_stmt_bind_param($update, 'ssi', $newHash, $username, $accountId);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }

            session_regenerate_id(true);
            $_SESSION['login_user'] = $account['username'];
            redirect_to('form/dashboard.php');
        }

        $error = 'Username atau password Anda salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Perpustakaan</title>
    <link href="template/css/bootstrap.min.css" rel="stylesheet">
    <link href="template/css/sb-admin-2.css" rel="stylesheet">
    <link rel="stylesheet" href="template/alert.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Silakan Login</h3>
                </div>
                <div class="panel-body">
                    <form method="post" autocomplete="on">
                        <fieldset>
                            <?php echo csrf_field(); ?>
                            <?php if ($success !== ''): ?>
                                <div class="alert alert-success"><?php echo e($success); ?></div>
                            <?php endif; ?>
                            <?php if ($error !== ''): ?>
                                <div class="form-group">
                                    <div class="alert">
                                        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                                        <?php echo e($error); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Username, contoh: adm" name="username" maxlength="30" autocomplete="username" required autofocus>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" placeholder="Password" name="password" autocomplete="current-password" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-md btn-primary btn-block">Login</button>
                            </div>
                            <div class="form-group text-center">
                                Belum punya akun? <a href="register.php">Daftar di sini</a>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="template/js/jquery-3.7.0.min.js"></script>
<script src="template/js/bootstrap.min.js"></script>
</body>
</html>
