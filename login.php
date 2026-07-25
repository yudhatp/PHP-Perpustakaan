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
    <meta name="theme-color" content="#0f172a">
    <title>Login | Perpustakaan 5F</title>
    <link rel="stylesheet" href="template/css/bootstrap.min.css">
    <link rel="stylesheet" href="template/alert.css">
    <link rel="stylesheet" href="template/css/app-modern.css">
</head>
<body class="auth-page">
<div class="auth-layout">
    <section class="auth-visual" aria-label="Informasi aplikasi">
        <div class="auth-visual-content">
            <div class="auth-logo">
                <span class="auth-logo-mark"><span class="glyphicon glyphicon-book"></span></span>
                <span><strong>Perpustakaan 5F</strong><span>Library Management System</span></span>
            </div>
            <h1>Kelola perpustakaan dengan lebih rapi dan efisien.</h1>
            <p>Satu aplikasi untuk mengelola koleksi buku, data anggota, peminjaman, pengembalian, serta laporan perpustakaan.</p>
            <div class="auth-feature-list">
                <span><i class="glyphicon glyphicon-ok-circle"></i> Koleksi buku</span>
                <span><i class="glyphicon glyphicon-ok-circle"></i> Data anggota</span>
                <span><i class="glyphicon glyphicon-ok-circle"></i> Transaksi peminjaman</span>
            </div>
        </div>
    </section>

    <main class="auth-form-side">
        <div class="auth-card">
            <div class="auth-mobile-brand"><span class="glyphicon glyphicon-book"></span> Perpustakaan 5F</div>
            <header class="auth-card-header">
                <h2>Selamat datang</h2>
                <p>Masuk menggunakan akun yang telah terdaftar.</p>
            </header>

            <form method="post" autocomplete="on">
                <?php echo csrf_field(); ?>
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><?php echo e($success); ?></div>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <div class="alert">
                        <span class="closebtn" onclick="this.parentElement.style.display='none';" aria-label="Tutup">&times;</span>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" class="form-control" placeholder="Contoh: adm" name="username" maxlength="30" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Masukkan password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Masuk ke Aplikasi</button>
                <p class="auth-footer-link">Belum memiliki akun? <a href="register.php">Daftar sebagai anggota</a></p>
            </form>
        </div>
    </main>
</div>
<script src="template/js/jquery-3.7.0.min.js"></script>
<script src="template/js/bootstrap.min.js"></script>
</body>
</html>
