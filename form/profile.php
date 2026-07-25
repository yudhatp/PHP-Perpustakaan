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
    <div class="row"><div class="col-lg-12"><h1 class="page-header">Profil Pengguna</h1></div></div>

    <?php if ($flashSuccess !== ''): ?>
        <div class="alert alert-success"><?php echo e($flashSuccess); ?></div>
    <?php endif; ?>

    <?php if ($profileIncomplete): ?>
        <div class="alert alert-warning">Profil belum lengkap. Lengkapi profil agar seluruh fitur akun dapat digunakan.</div>
    <?php endif; ?>

    <section class="profile-summary">
        <div class="profile-summary-aside">
            <div class="profile-summary-avatar"><?php echo e(strtoupper(substr($profile['nama'] ?: (string) $account['username'], 0, 1))); ?></div>
            <h3><?php echo e($profile['nama'] ?: (string) $account['username']); ?></h3>
            <p><?php echo e($profile['nama_role'] ?: 'Pengguna'); ?></p>
        </div>
        <div class="profile-summary-content">
            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <span class="profile-info-label">Nama lengkap</span>
                    <span class="profile-info-value"><?php echo e($profile['nama'] ?: '-'); ?></span>
                </div>
                <?php if ($roleId === 3): ?>
                    <div class="profile-info-item">
                        <span class="profile-info-label">Nomor anggota</span>
                        <span class="profile-info-value"><?php echo e($profile['no_anggota'] ?: '-'); ?></span>
                    </div>
                <?php else: ?>
                    <div class="profile-info-item">
                        <span class="profile-info-label">Hak akses</span>
                        <span class="profile-info-value"><?php echo e($profile['nama_role']); ?></span>
                    </div>
                <?php endif; ?>
                <div class="profile-info-item">
                    <span class="profile-info-label">Tanggal registrasi</span>
                    <span class="profile-info-value"><?php echo e($profile['tgl_register']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Perubahan password terakhir</span>
                    <span class="profile-info-value"><?php echo e($profile['last_change_pass']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Perubahan profil terakhir</span>
                    <span class="profile-info-value"><?php echo e($profile['last_change_profile']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Username</span>
                    <span class="profile-info-value"><?php echo e((string) $account['username']); ?></span>
                </div>
            </div>

            <div class="profile-actions">
                <?php if ($editUrl !== ''): ?><a href="<?php echo e($editUrl); ?>" class="btn btn-primary"><?php echo $profileIncomplete ? 'Lengkapi Profil' : 'Ubah Profil'; ?></a><?php endif; ?>
                <a href="ganti_password.php" class="btn btn-default">Ubah Password</a>
                <?php if ($cardUrl !== ''): ?><a href="<?php echo e($cardUrl); ?>" class="btn btn-success">Cetak Kartu Anggota</a><?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/footer.php'; ?>
