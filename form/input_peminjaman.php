<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');

$error = '';
$peminjamanId = request_int($_GET, 'id');
$isCreate = $peminjamanId <= 0;
$loan = [
    'no_peminjaman' => '',
    'staff' => '',
    'no_anggota' => '',
    'anggota' => '',
    'tgl_pinjam' => date('Y-m-d'),
    'tgl_kembali' => null,
];

$errorCode = request_string($_GET, 'error');
if ($errorCode === 'stok_kurang') {
    $error = 'Stok buku tidak mencukupi.';
} elseif ($errorCode === 'gagal_simpan') {
    $error = 'Detail buku gagal disimpan. Buku yang sama mungkin sudah tercatat.';
}

if (!$isCreate) {
    $stmt = mysqli_prepare($db, 'SELECT * FROM v_peminjaman WHERE id_t_peminjaman = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $peminjamanId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$row) {
        redirect_to('data_peminjaman.php');
    }
    $loan = array_merge($loan, $row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isCreate) {
    verify_csrf($_POST['csrf_token'] ?? null);
    $noAnggota = request_string($_POST, 'noanggota');

    $stmt = mysqli_prepare($db, "SELECT id_t_anggota, no_anggota, nama FROM t_anggota WHERE no_anggota = ? AND status = 'Aktif' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $noAnggota);
    mysqli_stmt_execute($stmt);
    $member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$member) {
        $error = 'Nomor anggota tidak ditemukan atau anggota tidak aktif.';
        $loan['no_anggota'] = $noAnggota;
    } else {
        $staffId = null;
        if ((int) $account['id_p_role'] === 2) {
            $accountId = (int) $account['id_t_account'];
            $stmt = mysqli_prepare($db, "SELECT id_t_staff FROM t_staff WHERE id_t_account = ? AND status = 'Aktif' LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'i', $accountId);
            mysqli_stmt_execute($stmt);
            $staff = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if (!$staff) {
                $error = 'Profil staff aktif tidak ditemukan.';
            } else {
                $staffId = (int) $staff['id_t_staff'];
            }
        }

        if ($error === '') {
            mysqli_begin_transaction($db);
            try {
                $numberResult = mysqli_query($db, "SELECT COALESCE(MAX(CAST(SUBSTRING(no_peminjaman, 4) AS UNSIGNED)), 1000000) AS nomor FROM t_peminjaman");
                $lastNumber = (int) (mysqli_fetch_assoc($numberResult)['nomor'] ?? 1000000);
                $noPeminjaman = 'PJ-' . str_pad((string) ($lastNumber + 1), 7, '0', STR_PAD_LEFT);
                $memberId = (int) $member['id_t_anggota'];
                $username = (string) $account['username'];

                $insert = mysqli_prepare($db, 'INSERT INTO t_peminjaman (no_peminjaman, id_t_staff, id_t_anggota, tgl_pinjam, create_date, create_by) VALUES (?, ?, ?, CURDATE(), CURDATE(), ?)');
                mysqli_stmt_bind_param($insert, 'siis', $noPeminjaman, $staffId, $memberId, $username);
                if (!mysqli_stmt_execute($insert)) {
                    throw new RuntimeException(mysqli_stmt_error($insert));
                }
                $newId = mysqli_insert_id($db);
                mysqli_stmt_close($insert);
                mysqli_commit($db);
                redirect_to('input_peminjaman.php?id=' . $newId);
            } catch (Throwable $exception) {
                mysqli_rollback($db);
                error_log($exception->getMessage());
                $error = 'Peminjaman gagal disimpan. Silakan coba kembali.';
            }
        }
    }
}

$details = null;
$availableBooks = null;
if (!$isCreate) {
    $stmt = mysqli_prepare($db, 'SELECT * FROM v_detil_pinjam WHERE id_t_peminjaman = ? ORDER BY id_t_detil_pinjam');
    mysqli_stmt_bind_param($stmt, 'i', $peminjamanId);
    mysqli_stmt_execute($stmt);
    $details = mysqli_stmt_get_result($stmt);

    if (empty($loan['tgl_kembali'])) {
        $availableBooks = mysqli_query($db, 'SELECT id_t_buku, nama_buku, stok FROM t_buku WHERE stok > 0 ORDER BY nama_buku');
    }
}

$memberNumbers = [];
if ($isCreate) {
    $memberResult = mysqli_query($db, "SELECT no_anggota FROM t_anggota WHERE status = 'Aktif' ORDER BY no_anggota");
    while ($memberRow = mysqli_fetch_assoc($memberResult)) {
        $memberNumbers[] = $memberRow['no_anggota'];
    }
}

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header"><?php echo $isCreate ? 'Input Peminjaman' : 'Detail Peminjaman'; ?></h1></div></div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-warning"><span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="row"><div class="col-lg-7">
        <div class="panel panel-default"><div class="panel-body">
            <?php if ($isCreate): ?>
                <form class="form-horizontal" method="post" autocomplete="off">
                    <?php echo csrf_field(); ?>
                    <div class="form-group"><label class="control-label col-sm-4">No Peminjaman</label><div class="col-sm-8"><p class="form-control-static">Dibuat otomatis</p></div></div>
                    <div class="form-group"><label class="control-label col-sm-4">Tanggal Pinjam</label><div class="col-sm-8"><p class="form-control-static"><?php echo date('Y-m-d'); ?></p></div></div>
                    <div class="form-group"><label class="control-label col-sm-4" for="noanggota">No Anggota</label><div class="col-sm-8"><input type="text" maxlength="20" class="form-control" value="<?php echo e($loan['no_anggota']); ?>" id="noanggota" name="noanggota" required></div></div>
                    <div class="form-group"><div class="col-sm-offset-4 col-sm-8"><a href="data_peminjaman.php" class="btn btn-default">Batal</a> <button type="submit" class="btn btn-primary">Simpan</button></div></div>
                </form>
            <?php else: ?>
                <div class="row"><div class="col-sm-4"><strong>No Peminjaman</strong></div><div class="col-sm-8"><?php echo e($loan['no_peminjaman']); ?></div></div><hr>
                <div class="row"><div class="col-sm-4"><strong>Staff</strong></div><div class="col-sm-8"><?php echo e($loan['staff']); ?></div></div><hr>
                <div class="row"><div class="col-sm-4"><strong>Tanggal Pinjam</strong></div><div class="col-sm-8"><?php echo e($loan['tgl_pinjam']); ?></div></div><hr>
                <div class="row"><div class="col-sm-4"><strong>Anggota</strong></div><div class="col-sm-8"><?php echo e($loan['no_anggota'] . ' - ' . $loan['anggota']); ?></div></div><hr>
                <div class="row"><div class="col-sm-4"><strong>Status</strong></div><div class="col-sm-8"><?php echo empty($loan['tgl_kembali']) ? '<span class="label label-warning">Dipinjam</span>' : '<span class="label label-success">Selesai ' . e($loan['tgl_kembali']) . '</span>'; ?></div></div>
                <hr><a href="data_peminjaman.php" class="btn btn-default">Kembali</a> <a href="cetak_bukti.php?id=<?php echo $peminjamanId; ?>" target="_blank" class="btn btn-primary">Cetak Bukti</a>
            <?php endif; ?>
        </div></div>
    </div></div>

    <?php if (!$isCreate): ?>
        <div class="row"><div class="col-lg-12">
            <?php if (empty($loan['tgl_kembali'])): ?>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalBuku"><span class="glyphicon glyphicon-plus"></span> Tambah Buku</button>
                <br><br>
            <?php endif; ?>
            <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
                <thead><tr><th>No</th><th>Nama Buku</th><th>Penulis</th><th>Qty</th><th>Status</th><th>Harga</th><th>Denda</th><th>Keterangan</th><th colspan="2">Aksi</th></tr></thead>
                <tbody>
                <?php $no = 1; $hasDetails = false; while ($detail = mysqli_fetch_assoc($details)): $hasDetails = true; ?>
                    <tr>
                        <td><?php echo $no++; ?></td><td><?php echo e($detail['nama_buku']); ?></td><td><?php echo e($detail['penulis']); ?></td><td><?php echo (int) $detail['qty']; ?></td>
                        <td><?php echo e($detail['kondisi'] ?: 'Dipinjam'); ?></td><td>Rp <?php echo number_format((int) $detail['harga'], 0, ',', '.'); ?></td><td>Rp <?php echo number_format((int) ($detail['denda'] ?? 0), 0, ',', '.'); ?></td><td><?php echo e($detail['keterangan']); ?></td>
                        <?php if (empty($detail['tgl_kembali'])): ?>
                            <td><a href="edit_detil_pinjam.php?id=<?php echo (int) $detail['id_t_detil_pinjam']; ?>&id_pj=<?php echo $peminjamanId; ?>" class="btn btn-info btn-sm">Kembalikan</a></td>
                            <td><a href="hapus_detil_pinjam.php?id=<?php echo $peminjamanId; ?>&id_buku=<?php echo (int) $detail['id_t_buku']; ?>&csrf=<?php echo urlencode(csrf_token()); ?>" onclick="return confirm('Hapus buku dari transaksi ini?')" class="btn btn-danger btn-sm">Hapus</a></td>
                        <?php else: ?><td>-</td><td>-</td><?php endif; ?>
                    </tr>
                <?php endwhile; mysqli_stmt_close($stmt); ?>
                <?php if (!$hasDetails): ?><tr><td colspan="10" class="text-center">Belum ada buku pada transaksi ini.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    <?php endif; ?>
</div>

<?php if (!$isCreate && empty($loan['tgl_kembali'])): ?>
<div id="modalBuku" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Tambah Buku</h4></div>
    <form method="post" action="simpan_detil_pinjam.php?id=<?php echo $peminjamanId; ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-body">
            <div class="form-group"><label>Nama Buku</label><select class="form-control" name="idbuku" required>
                <?php $bookCount = 0; while ($book = mysqli_fetch_assoc($availableBooks)): $bookCount++; ?>
                    <option value="<?php echo (int) $book['id_t_buku']; ?>"><?php echo e($book['nama_buku']); ?> (stok: <?php echo (int) $book['stok']; ?>)</option>
                <?php endwhile; ?>
            </select><?php if ($bookCount === 0): ?><p class="help-block text-danger">Tidak ada buku dengan stok tersedia.</p><?php endif; ?></div>
            <div class="form-group"><label>Qty</label><input name="qty" type="number" min="1" max="99" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" <?php echo $bookCount === 0 ? 'disabled' : ''; ?>>Simpan</button></div>
    </form>
</div></div></div>
<?php endif; ?>

<?php if ($isCreate): ?>
<script>
$(function () {
    $('#noanggota').autocomplete({source: <?php echo json_encode($memberNumbers, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>});
});
</script>
<?php endif; ?>
<?php include __DIR__ . '/footer.php'; ?>
