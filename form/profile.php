<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_login($db, '../index.php');

$roleId = (int) $account['id_p_role'];
$accountId = (int) $account['id_t_account'];
$profile = [
    'nama' => $roleId === 1 ? 'Admin' : '',
    'no_anggota' => '',
    'nama_role' => '',
    'tgl_register' => '',
    'last_change_pass' => '-',
    'last_change_profile' => '-',
];
$editUrl = '';
$cardUrl = '';
$profileIncomplete = false;

$stmt = mysqli_prepare($db, 'SELECT a.create_date, a.update_date, r.nama_role FROM t_account a JOIN p_role r ON r.id_p_role = a.id_p_role WHERE a.id_t_account = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $accountId);
mysqli_stmt_execute($stmt);
$base = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($base) {
    $profile['nama_role'] = $base['nama_role'];
    $profile['tgl_register'] = $base['create_date'];
    $profile['last_change_pass'] = $base['update_date'] ?: '-';
}

if ($roleId === 2) {
    $stmt = mysqli_prepare($db, 'SELECT id_t_staff, nama, update_date FROM t_staff WHERE id_t_account = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $staff = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($staff) {
        $profile['nama'] = $staff['nama'];
        $profile['last_change_profile'] = $staff['update_date'] ?: '-';
        $editUrl = 'input_staff.php?id=' . (int) $staff['id_t_staff'];
    } else {
        $profileIncomplete = true;
    }
} elseif ($roleId === 3) {
    $stmt = mysqli_prepare($db, 'SELECT id_t_anggota, no_anggota, nama, update_date FROM t_anggota WHERE id_t_account = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $accountId);
    mysqli_stmt_execute($stmt);
    $member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($member) {
        $profile['nama'] = $member['nama'];
        $profile['no_anggota'] = $member['no_anggota'];
        $profile['last_change_profile'] = $member['update_date'] ?: '-';
        $editUrl = 'input_anggota.php?id=' . (int) $member['id_t_anggota'];
        $cardUrl = 'cetak_kartu_anggota.php?id=' . (int) $member['id_t_anggota'];
    } else {
        $profileIncomplete = true;
        $editUrl = 'input_anggota.php';
    }
}

$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Profile</h1></div></div>

    <?php if ($flashSuccess !== ''): ?>
        <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
    <?php endif; ?>

    <?php if ($profileIncomplete): ?>
        <div class="alert alert-warning">Profil belum lengkap. Lengkapi profil agar fitur anggota/staff dapat digunakan.</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="row"><label class="col-sm-2">Nama</label><div class="col-sm-6"><p><?php echo e($profile['nama'] ?: '-'); ?></p></div></div>
            <?php if ($roleId === 3): ?>
                <div class="row"><label class="col-sm-2">No Anggota</label><div class="col-sm-6"><p><?php echo e($profile['no_anggota'] ?: '-'); ?></p></div></div>
            <?php else: ?>
                <div class="row"><label class="col-sm-2">Role</label><div class="col-sm-6"><p><?php echo e($profile['nama_role']); ?></p></div></div>
            <?php endif; ?>
            <div class="row"><label class="col-sm-2">Tanggal Register</label><div class="col-sm-6"><p><?php echo e($profile['tgl_register']); ?></p></div></div>
            <div class="row"><label class="col-sm-2">Perubahan Password</label><div class="col-sm-6"><p><?php echo e($profile['last_change_pass']); ?></p></div></div>
            <div class="row"><label class="col-sm-2">Perubahan Profil</label><div class="col-sm-6"><p><?php echo e($profile['last_change_profile']); ?></p></div></div>
        </div>
    </div>
    <hr>
    <div class="row"><div class="col-sm-8">
        <?php if ($editUrl !== ''): ?><a href="<?php echo e($editUrl); ?>" class="btn btn-primary"><?php echo $profileIncomplete ? 'Lengkapi Profil' : 'Ubah Profil'; ?></a><?php endif; ?>
        <a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
        <?php if ($cardUrl !== ''): ?><a href="<?php echo e($cardUrl); ?>" class="btn btn-primary">Cetak Kartu Anggota</a><?php endif; ?>
    </div></div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
