<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');

$detailId = request_int($_GET, 'id');
$peminjamanId = request_int($_GET, 'id_pj');
if ($detailId <= 0 || $peminjamanId <= 0) {
    redirect_to('data_peminjaman.php');
}

function load_loan_detail(mysqli $db, int $detailId, int $peminjamanId): ?array
{
    $stmt = mysqli_prepare($db, 'SELECT * FROM v_detil_pinjam WHERE id_t_detil_pinjam = ? AND id_t_peminjaman = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'ii', $detailId, $peminjamanId);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);
    return $data;
}

$tampil_data = load_loan_detail($db, $detailId, $peminjamanId);
if (!$tampil_data) {
    redirect_to('input_peminjaman.php?id=' . $peminjamanId);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $kondisi = request_string($_POST, 'kondisi');
    $keterangan = request_string($_POST, 'keterangan');
    $allowedConditions = ['Bagus', 'Rusak', 'Hilang'];

    if (!in_array($kondisi, $allowedConditions, true)) {
        $error = 'Kondisi buku tidak valid.';
    } elseif (!empty($tampil_data['tgl_kembali'])) {
        $error = 'Buku ini sudah dikembalikan.';
    } else {
        $harga = (int) $tampil_data['harga'];
        $qty = (int) $tampil_data['qty'];
        $bukuId = (int) $tampil_data['id_t_buku'];
        $denda = 0;

        if ($kondisi === 'Rusak') {
            $denda = (int) round($harga * 0.30) * $qty;
        } elseif ($kondisi === 'Hilang') {
            $denda = $harga * $qty;
        }

        mysqli_begin_transaction($db);
        try {
            $lock = mysqli_prepare($db, 'SELECT tgl_kembali FROM t_detil_pinjam WHERE id_t_detil_pinjam = ? AND id_t_peminjaman = ? FOR UPDATE');
            mysqli_stmt_bind_param($lock, 'ii', $detailId, $peminjamanId);
            mysqli_stmt_execute($lock);
            $lockedDetail = mysqli_fetch_assoc(mysqli_stmt_get_result($lock));
            mysqli_stmt_close($lock);

            if (!$lockedDetail || !empty($lockedDetail['tgl_kembali'])) {
                throw new RuntimeException('Detail sudah diproses oleh pengguna lain.');
            }

            $username = (string) $account['username'];
            $update = mysqli_prepare($db, 'UPDATE t_detil_pinjam SET tgl_kembali = NOW(), kondisi = ?, keterangan = ?, denda = ?, update_date = NOW(), update_by = ? WHERE id_t_detil_pinjam = ?');
            mysqli_stmt_bind_param($update, 'ssisi', $kondisi, $keterangan, $denda, $username, $detailId);
            if (!mysqli_stmt_execute($update)) {
                throw new RuntimeException(mysqli_stmt_error($update));
            }
            mysqli_stmt_close($update);

            // Buku bagus/rusak kembali menjadi stok. Buku hilang tidak dikurangi lagi
            // karena stoknya sudah berkurang saat peminjaman dibuat.
            if ($kondisi === 'Bagus' || $kondisi === 'Rusak') {
                $stock = mysqli_prepare($db, 'UPDATE t_buku SET stok = stok + ? WHERE id_t_buku = ?');
                mysqli_stmt_bind_param($stock, 'ii', $qty, $bukuId);
                mysqli_stmt_execute($stock);
                mysqli_stmt_close($stock);
            }

            $summary = mysqli_prepare($db, 'SELECT SUM(CASE WHEN tgl_kembali IS NULL THEN 1 ELSE 0 END) AS belum_kembali, COALESCE(SUM(denda), 0) AS total_denda FROM t_detil_pinjam WHERE id_t_peminjaman = ?');
            mysqli_stmt_bind_param($summary, 'i', $peminjamanId);
            mysqli_stmt_execute($summary);
            $loanSummary = mysqli_fetch_assoc(mysqli_stmt_get_result($summary));
            mysqli_stmt_close($summary);

            $pending = (int) ($loanSummary['belum_kembali'] ?? 0);
            $totalDenda = (int) ($loanSummary['total_denda'] ?? 0);
            if ($pending === 0) {
                $loanUpdate = mysqli_prepare($db, 'UPDATE t_peminjaman SET tgl_kembali = CURDATE(), total_denda = ?, update_date = CURDATE(), update_by = ? WHERE id_t_peminjaman = ?');
            } else {
                $loanUpdate = mysqli_prepare($db, 'UPDATE t_peminjaman SET tgl_kembali = NULL, total_denda = ?, update_date = CURDATE(), update_by = ? WHERE id_t_peminjaman = ?');
            }
            mysqli_stmt_bind_param($loanUpdate, 'isi', $totalDenda, $username, $peminjamanId);
            mysqli_stmt_execute($loanUpdate);
            mysqli_stmt_close($loanUpdate);

            mysqli_commit($db);
            redirect_to('input_peminjaman.php?id=' . $peminjamanId);
        } catch (Throwable $exception) {
            mysqli_rollback($db);
            error_log($exception->getMessage());
            $error = 'Pengembalian gagal disimpan. Silakan coba kembali.';
        }
    }
}

include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12"><h1 class="page-header">Pengembalian Buku</h1></div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <?php if ($error !== ''): ?>
                <div class="alert"><span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span><?php echo e($error); ?></div>
            <?php endif; ?>
            <form class="form-horizontal" method="post">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="control-label col-sm-4">Nama Buku</label>
                    <div class="col-sm-8"><p class="form-control-static"><?php echo e($tampil_data['nama_buku']); ?></p></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Penulis</label>
                    <div class="col-sm-8"><p class="form-control-static"><?php echo e($tampil_data['penulis']); ?></p></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Harga</label>
                    <div class="col-sm-8"><p class="form-control-static">Rp <?php echo number_format((int) $tampil_data['harga'], 0, ',', '.'); ?></p></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Qty</label>
                    <div class="col-sm-8"><p class="form-control-static"><?php echo (int) $tampil_data['qty']; ?></p></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Kondisi</label>
                    <div class="col-sm-8">
                        <select name="kondisi" class="form-control" required>
                            <?php foreach (['Bagus', 'Rusak', 'Hilang'] as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo (($tampil_data['kondisi'] ?? '') === $option) ? 'selected' : ''; ?>><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Keterangan</label>
                    <div class="col-sm-8"><textarea name="keterangan" maxlength="64" class="form-control" rows="3"><?php echo e($tampil_data['keterangan']); ?></textarea></div>
                </div>
                <div class="form-group text-right">
                    <div class="col-sm-offset-4 col-sm-8">
                        <a href="input_peminjaman.php?id=<?php echo $peminjamanId; ?>" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Pengembalian</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
