<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2, 3], 'dashboard.php');

$roleId = (int) $account['id_p_role'];
$isMember = $roleId === 3;
$anggotaId = request_int($_GET, 'id');
$error = '';
$tampil_data = [
    'no_anggota' => '', 'nama' => '', 'tgl_daftar' => date('Y-m-d'),
    'tgl_lahir' => '', 'jenis_kelamin' => 'Pria', 'no_telp' => '',
    'alamat' => '', 'keterangan' => '', 'status' => 'Aktif',
];

if ($isMember) {
    $accountId = (int) $account['id_t_account'];
    $stmt = mysqli_prepare($db, 'SELECT * FROM t_anggota WHERE id_t_account = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($member) {
        $anggotaId = (int) $member['id_t_anggota'];
        $tampil_data = array_merge($tampil_data, $member);
    } else {
        $anggotaId = 0;
    }
} else {
    if ($anggotaId <= 0) {
        redirect_to('data_anggota.php');
    }
    $stmt = mysqli_prepare($db, 'SELECT * FROM t_anggota WHERE id_t_anggota = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $anggotaId);
    mysqli_stmt_execute($stmt);
    $member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$member) {
        redirect_to('data_anggota.php');
    }
    $tampil_data = array_merge($tampil_data, $member);
}

$isCreate = $isMember && $anggotaId <= 0;
$judul = $isCreate ? 'Lengkapi Profil Anggota' : 'Edit Anggota';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $nama = request_string($_POST, 'nama');
    $tglLahir = request_string($_POST, 'tgllahir');
    $jenisKelamin = request_string($_POST, 'optjk');
    $noTelp = request_string($_POST, 'notelp');
    $alamat = request_string($_POST, 'alamat');
    $keterangan = request_string($_POST, 'ket');
    $status = $isMember ? (string) $tampil_data['status'] : request_string($_POST, 'statusnya', 'Aktif');

    $tampil_data = array_merge($tampil_data, [
        'nama' => $nama, 'tgl_lahir' => $tglLahir, 'jenis_kelamin' => $jenisKelamin,
        'no_telp' => $noTelp, 'alamat' => $alamat, 'keterangan' => $keterangan,
        'status' => $status,
    ]);

    $date = DateTime::createFromFormat('Y-m-d', $tglLahir);
    $validDate = $date && $date->format('Y-m-d') === $tglLahir && $date <= new DateTime('today');

    if ($nama === '' || strlen($nama) > 100) {
        $error = 'Nama anggota wajib diisi dan maksimal 100 karakter.';
    } elseif (!$validDate) {
        $error = 'Tanggal lahir tidak valid.';
    } elseif (!in_array($jenisKelamin, ['Pria', 'Wanita'], true)) {
        $error = 'Jenis kelamin tidak valid.';
    } elseif (!preg_match('/^[0-9+\-() ]{7,20}$/', $noTelp)) {
        $error = 'Nomor telepon tidak valid.';
    } elseif ($alamat === '' || strlen($alamat) > 500) {
        $error = 'Alamat wajib diisi dan maksimal 500 karakter.';
    } elseif (!in_array($status, ['Aktif', 'Tidak Aktif'], true)) {
        $error = 'Status tidak valid.';
    } else {
        $actor = (string) $account['username'];
        if ($isCreate) {
            $accountId = (int) $account['id_t_account'];
            $noAnggota = 'ANG' . str_pad((string) $accountId, 8, '0', STR_PAD_LEFT);
            $stmt = mysqli_prepare($db, 'INSERT INTO t_anggota (id_t_account, no_anggota, nama, tgl_daftar, tgl_lahir, jenis_kelamin, no_telp, alamat, keterangan, status, create_date, create_by) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, CURDATE(), ?)');
            mysqli_stmt_bind_param($stmt, 'isssssssss', $accountId, $noAnggota, $nama, $tglLahir, $jenisKelamin, $noTelp, $alamat, $keterangan, $status, $actor);
        } else {
            $stmt = mysqli_prepare($db, 'UPDATE t_anggota SET nama = ?, tgl_lahir = ?, jenis_kelamin = ?, no_telp = ?, alamat = ?, keterangan = ?, status = ?, update_date = CURDATE(), update_by = ? WHERE id_t_anggota = ?');
            mysqli_stmt_bind_param($stmt, 'ssssssssi', $nama, $tglLahir, $jenisKelamin, $noTelp, $alamat, $keterangan, $status, $actor, $anggotaId);
        }

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect_to($isMember ? 'profile.php' : 'data_anggota.php');
        }
        $error = 'Data anggota gagal disimpan.';
        mysqli_stmt_close($stmt);
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
                <div class="form-group"><label class="control-label col-sm-4">No Anggota</label><div class="col-sm-8"><p class="form-control-static"><?php echo e($tampil_data['no_anggota'] ?: 'Dibuat otomatis'); ?></p></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Nama Anggota</label><div class="col-sm-8"><input type="text" maxlength="100" class="form-control" name="nama" value="<?php echo e($tampil_data['nama']); ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Tanggal Daftar</label><div class="col-sm-8"><p class="form-control-static"><?php echo e($tampil_data['tgl_daftar']); ?></p></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Tanggal Lahir</label><div class="col-sm-8"><input type="date" class="form-control" name="tgllahir" value="<?php echo e($tampil_data['tgl_lahir']); ?>" id="tgllahir" required></div></div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Jenis Kelamin</label>
                    <div class="col-sm-8">
                        <label class="radio-inline"><input type="radio" name="optjk" value="Pria" <?php echo $tampil_data['jenis_kelamin'] === 'Pria' ? 'checked' : ''; ?>> Pria</label>
                        <label class="radio-inline"><input type="radio" name="optjk" value="Wanita" <?php echo $tampil_data['jenis_kelamin'] === 'Wanita' ? 'checked' : ''; ?>> Wanita</label>
                    </div>
                </div>
                <div class="form-group"><label class="control-label col-sm-4">No Telepon</label><div class="col-sm-8"><input type="text" maxlength="20" class="form-control" value="<?php echo e($tampil_data['no_telp']); ?>" name="notelp" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Alamat</label><div class="col-sm-8"><textarea name="alamat" maxlength="500" class="form-control" rows="3" required><?php echo e($tampil_data['alamat']); ?></textarea></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Keterangan</label><div class="col-sm-8"><textarea name="ket" maxlength="255" class="form-control" rows="3"><?php echo e($tampil_data['keterangan']); ?></textarea></div></div>
                <?php if (!$isMember): ?>
                    <div class="form-group"><label class="control-label col-sm-4">Status</label><div class="col-sm-8"><select name="statusnya" class="form-control"><option value="Aktif" <?php echo $tampil_data['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option><option value="Tidak Aktif" <?php echo $tampil_data['status'] === 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option></select></div></div>
                <?php endif; ?>
                <div class="form-group text-right"><div class="col-sm-offset-4 col-sm-8"><a href="<?php echo $isMember ? 'profile.php' : 'data_anggota.php'; ?>" class="btn btn-default">Batal</a> <button type="submit" class="btn btn-primary">Simpan</button></div></div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
