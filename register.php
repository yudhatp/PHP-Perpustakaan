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
    <meta name="theme-color" content="#0f172a">
    <title>Registrasi | Perpustakaan 5F</title>
    <link rel="stylesheet" href="template/css/bootstrap.min.css">
    <link rel="stylesheet" href="template/alert.css">
    <link rel="stylesheet" href="template/css/app-modern.css">
</head>
<body class="auth-page">
<div class="auth-layout">
    <section class="auth-visual" aria-label="Informasi pendaftaran">
        <div class="auth-visual-content">
            <div class="auth-logo">
                <span class="auth-logo-mark"><span class="glyphicon glyphicon-book"></span></span>
                <span><strong>Perpustakaan 5F</strong><span>Library Management System</span></span>
            </div>
            <h1>Mulai akses koleksi perpustakaan dari satu akun.</h1>
            <p>Buat akun anggota untuk melihat katalog buku dan memantau riwayat peminjaman. Data profil dapat dilengkapi setelah login.</p>
            <div class="auth-feature-list">
                <span><i class="glyphicon glyphicon-ok-circle"></i> Registrasi mandiri</span>
                <span><i class="glyphicon glyphicon-ok-circle"></i> Riwayat peminjaman</span>
                <span><i class="glyphicon glyphicon-ok-circle"></i> Akses katalog</span>
            </div>
        </div>
    </section>

    <main class="auth-form-side">
        <div class="auth-card">
            <div class="auth-mobile-brand"><span class="glyphicon glyphicon-book"></span> Perpustakaan 5F</div>
            <header class="auth-card-header">
                <h2>Buat akun anggota</h2>
                <p>Gunakan username yang mudah diingat dan password yang cukup kuat.</p>
            </header>

            <form method="post" autocomplete="on">
                <?php echo csrf_field(); ?>
                <?php if ($error !== ''): ?>
                    <div class="alert">
                        <span class="closebtn" onclick="this.parentElement.style.display='none';" aria-label="Tutup">&times;</span>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" minlength="3" maxlength="30" class="form-control" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" autocomplete="username" placeholder="Huruf, angka, atau underscore" required>
                    <small>Gunakan 3–30 karakter tanpa spasi.</small>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" minlength="8" maxlength="72" class="form-control" id="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                    <small>Kekuatan password: <strong id="passstrength">-</strong></small>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" minlength="8" maxlength="72" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Buat Akun</button>
                <p class="auth-footer-link">Sudah memiliki akun? <a href="login.php">Kembali ke halaman login</a></p>
            </form>
        </div>
    </main>
</div>
<script src="template/js/jquery-3.7.0.min.js"></script>
<script src="template/js/bootstrap.min.js"></script>
<script>
$('#password').on('input', function () {
    var value = $(this).val();
    var label = value.length === 0 ? '-' : 'Lemah';
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
