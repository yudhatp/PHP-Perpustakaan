<?php
require_once __DIR__ . '/setting/koneksi.php';
app_session_start();

if (!empty($_SESSION['login_user'])) {
    redirect_to('form/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $username = strtolower(request_string($_POST, 'username'));
    $password = request_string($_POST, 'password');
    $passwordConfirmation = request_string($_POST, 'password_confirmation');

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = 'Username harus 3–30 karakter dan hanya berisi huruf, angka, atau underscore.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $check = mysqli_prepare($db, 'SELECT id_t_account FROM t_account WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($check, 's', $username);
        mysqli_stmt_execute($check);
        $exists = mysqli_stmt_get_result($check)->num_rows > 0;
        mysqli_stmt_close($check);

        if ($exists) {
            $error = 'Username sudah digunakan pengguna lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 3;
            $createdBy = 'sys';
            $stmt = mysqli_prepare($db, 'INSERT INTO t_account (id_p_role, username, password, create_date, create_by) VALUES (?, ?, ?, NOW(), ?)');
            mysqli_stmt_bind_param($stmt, 'isss', $role, $username, $hash, $createdBy);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['flash_success'] = 'Akun berhasil dibuat. Silakan login lalu lengkapi profil anggota.';
                redirect_to('login.php');
            }

            $error = 'Akun gagal dibuat. Silakan coba kembali.';
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi | Perpustakaan</title>
    <link rel="stylesheet" href="template/alert.css">
    <link href="template/css/bootstrap.min.css" rel="stylesheet">
    <link href="template/css/sb-admin-2.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Daftar Akun Anggota</h3>
                </div>
                <div class="panel-body">
                    <form method="post" autocomplete="on">
                        <fieldset>
                            <?php echo csrf_field(); ?>
                            <?php if ($error !== ''): ?>
                                <div class="alert">
                                    <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                                    <?php echo e($error); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" minlength="3" maxlength="30" class="form-control" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" autocomplete="username" required>
                                <small>Gunakan huruf, angka, atau underscore.</small>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" minlength="8" maxlength="72" class="form-control" id="password" name="password" autocomplete="new-password" required>
                                <small>Kekuatan: <strong id="passstrength">-</strong></small>
                            </div>
                            <div class="form-group">
                                <label>Konfirmasi Password</label>
                                <input type="password" minlength="8" maxlength="72" class="form-control" name="password_confirmation" autocomplete="new-password" required>
                            </div>
                            <button type="submit" class="btn btn-md btn-primary btn-block">Buat Akun</button>
                            <br>
                            Sudah punya akun? <a href="login.php">Login di sini</a>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="template/js/jquery-3.7.0.min.js"></script>
<script src="template/js/bootstrap.min.js"></script>
<script>
$('#password').on('input', function () {
    var value = $(this).val();
    var label = 'Lemah';
    if (value.length >= 12 && /[A-Z]/.test(value) && /[a-z]/.test(value) && /\d/.test(value) && /[^A-Za-z0-9]/.test(value)) {
        label = 'Kuat';
    } else if (value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value)) {
        label = 'Sedang';
    }
    $('#passstrength').text(label);
});
</script>
</body>
</html>
