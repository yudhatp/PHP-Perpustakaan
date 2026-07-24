<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');

$isAdmin = (int) $account['id_p_role'] === 1;
$staffId = request_int($_GET, 'id');
$error = '';
$tampil_data = ['nama' => '', 'alamat' => '', 'status' => 'Aktif'];

if (!$isAdmin) {
    $accountId = (int) $account['id_t_account'];
    $stmt = mysqli_prepare($db, 'SELECT * FROM t_staff WHERE id_t_account = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $staff = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$staff) {
        redirect_to('dashboard.php');
    }
    $staffId = (int) $staff['id_t_staff'];
    $tampil_data = array_merge($tampil_data, $staff);
} elseif ($staffId > 0) {
    $stmt = mysqli_prepare($db, 'SELECT * FROM t_staff WHERE id_t_staff = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $staffId);
    mysqli_stmt_execute($stmt);
    $staff = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$staff) {
        redirect_to('data_staff.php');
    }
    $tampil_data = array_merge($tampil_data, $staff);
}

$isCreate = $isAdmin && $staffId <= 0;
$judul = $isCreate ? 'Input Staff' : 'Edit Staff';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $nama = request_string($_POST, 'nama');
    $alamat = request_string($_POST, 'alamat');
    $status = request_string($_POST, 'statusnya', 'Aktif');
    $username = strtolower(request_string($_POST, 'user'));
    $password = request_string($_POST, 'pass');

    $tampil_data = array_merge($tampil_data, ['nama' => $nama, 'alamat' => $alamat, 'status' => $status]);

    if ($nama === '' || text_length($nama) > 25) {
        $error = 'Nama staff wajib diisi dan maksimal 25 karakter.';
    } elseif ($alamat === '' || text_length($alamat) > 255) {
        $error = 'Alamat wajib diisi dan maksimal 255 karakter.';
    } elseif (!in_array($status, ['Aktif', 'Tidak Aktif'], true)) {
        $error = 'Status tidak valid.';
    } elseif ($isCreate && !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = 'Username harus 3–30 karakter berupa huruf, angka, atau underscore.';
    } elseif ($isCreate && strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        $actor = (string) $account['username'];
        if ($isCreate) {
            $check = mysqli_prepare($db, 'SELECT id_t_account FROM t_account WHERE username = ? LIMIT 1');
            mysqli_stmt_bind_param($check, 's', $username);
            mysqli_stmt_execute($check);
            $exists = mysqli_stmt_get_result($check)->num_rows > 0;
            mysqli_stmt_close($check);

            if ($exists) {
                $error = 'Username sudah digunakan.';
            } else {
                mysqli_begin_transaction($db);
                try {
                    $role = 2;
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $insertAccount = mysqli_prepare($db, 'INSERT INTO t_account (id_p_role, username, password, create_date, create_by) VALUES (?, ?, ?, NOW(), ?)');
                    mysqli_stmt_bind_param($insertAccount, 'isss', $role, $username, $hash, $actor);
                    if (!mysqli_stmt_execute($insertAccount)) {
                        throw new RuntimeException(mysqli_stmt_error($insertAccount));
                    }
                    $newAccountId = mysqli_insert_id($db);
                    mysqli_stmt_close($insertAccount);

                    $insertStaff = mysqli_prepare($db, 'INSERT INTO t_staff (id_t_account, nama, alamat, status, create_date, create_by) VALUES (?, ?, ?, ?, NOW(), ?)');
                    mysqli_stmt_bind_param($insertStaff, 'issss', $newAccountId, $nama, $alamat, $status, $actor);
                    if (!mysqli_stmt_execute($insertStaff)) {
                        throw new RuntimeException(mysqli_stmt_error($insertStaff));
                    }
                    mysqli_stmt_close($insertStaff);
                    mysqli_commit($db);
                    redirect_to('data_staff.php');
                } catch (Throwable $exception) {
                    mysqli_rollback($db);
                    error_log($exception->getMessage());
                    $error = 'Data staff gagal disimpan.';
                }
            }
        } else {
            $update = mysqli_prepare($db, 'UPDATE t_staff SET nama = ?, alamat = ?, status = ?, update_date = NOW(), update_by = ? WHERE id_t_staff = ?');
            mysqli_stmt_bind_param($update, 'ssssi', $nama, $alamat, $status, $actor, $staffId);
            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);
                redirect_to($isAdmin ? 'data_staff.php' : 'dashboard.php');
            }
            $error = 'Data staff gagal diperbarui.';
            mysqli_stmt_close($update);
        }
    }
}

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header"><?php echo e($judul); ?></h1></div></div>
    <div class="row">
        <div class="col-lg-6">
            <?php if ($error !== ''): ?>
                <div class="alert"><span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span><?php echo e($error); ?></div>
            <?php endif; ?>
            <form class="form-horizontal" method="post">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="control-label col-sm-4">Nama Staff</label>
                    <div class="col-sm-8"><input type="text" maxlength="25" class="form-control" name="nama" value="<?php echo e($tampil_data['nama']); ?>" required></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Tanggal Daftar</label>
                    <div class="col-sm-8"><p class="form-control-static"><?php echo e($tampil_data['create_date'] ?? date('Y-m-d')); ?></p></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Alamat</label>
                    <div class="col-sm-8"><textarea name="alamat" maxlength="255" class="form-control" rows="3" required><?php echo e($tampil_data['alamat']); ?></textarea></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Status</label>
                    <div class="col-sm-8">
                        <select name="statusnya" class="form-control">
                            <option value="Aktif" <?php echo $tampil_data['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Tidak Aktif" <?php echo $tampil_data['status'] === 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <?php if ($isCreate): ?>
                    <div class="form-group">
                        <label class="control-label col-sm-4">Username</label>
                        <div class="col-sm-8"><input type="text" minlength="3" maxlength="30" class="form-control" name="user" value="<?php echo e($_POST['user'] ?? ''); ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4">Password</label>
                        <div class="col-sm-8"><input type="password" minlength="8" maxlength="72" class="form-control" name="pass" required></div>
                    </div>
                <?php endif; ?>
                <div class="form-group text-right">
                    <div class="col-sm-offset-4 col-sm-8">
                        <a href="<?php echo $isAdmin ? 'data_staff.php' : 'dashboard.php'; ?>" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
