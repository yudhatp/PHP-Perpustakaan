<?php
require_once __DIR__ . '/../setting/session.php';
$account = require_roles($db, [1, 2], 'dashboard.php');

$bukuId = request_int($_GET, 'id');
$error = '';
$tampil_data = [
    'nama_buku' => '', 'jenis' => 'Umum', 'penulis' => '', 'penerbit' => '',
    'tahun_terbit' => date('Y'), 'harga' => 0, 'kode_rak' => '', 'stok' => 0,
    'sinopsis' => '', 'gambar' => '',
];

if ($bukuId > 0) {
    $stmt = mysqli_prepare($db, 'SELECT * FROM t_buku WHERE id_t_buku = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $bukuId);
    mysqli_stmt_execute($stmt);
    $book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$book) {
        redirect_to('data_buku.php');
    }
    $tampil_data = array_merge($tampil_data, $book);
}

$isCreate = $bukuId <= 0;
$judul = $isCreate ? 'Input Buku' : 'Edit Buku';

function save_book_cover(array $file, string $directory): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload cover gagal.');
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran cover maksimal 2 MB.');
    }

    $tmp = $file['tmp_name'] ?? '';
    $imageInfo = @getimagesize($tmp);
    if (!$imageInfo) {
        throw new RuntimeException('File cover bukan gambar yang valid.');
    }

    $mimeExtensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = $imageInfo['mime'] ?? '';
    if (!isset($mimeExtensions[$mime])) {
        throw new RuntimeException('Cover harus berupa JPG, PNG, atau WEBP.');
    }

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder cover tidak dapat dibuat.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $mimeExtensions[$mime];
    $destination = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('File cover tidak dapat disimpan.');
    }

    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $nama = request_string($_POST, 'nama');
    $penulis = request_string($_POST, 'penulis');
    $jenis = request_string($_POST, 'jenisnya');
    $tahun = request_int($_POST, 'tahun');
    $penerbit = request_string($_POST, 'penerbit');
    $rak = strtoupper(request_string($_POST, 'koderak'));
    $stok = request_int($_POST, 'stok', -1);
    $harga = request_int($_POST, 'harga', -1);
    $sinopsis = request_string($_POST, 'sinopsis');

    $tampil_data = array_merge($tampil_data, [
        'nama_buku' => $nama, 'jenis' => $jenis, 'penulis' => $penulis,
        'penerbit' => $penerbit, 'tahun_terbit' => $tahun, 'harga' => $harga,
        'kode_rak' => $rak, 'stok' => $stok, 'sinopsis' => $sinopsis,
    ]);

    $allowedTypes = ['Umum', 'Komputer', 'Novel', 'Pengembangan Diri', 'Komik'];
    $currentYear = (int) date('Y') + 1;

    if ($nama === '' || text_length($nama) > 128) {
        $error = 'Nama buku wajib diisi dan maksimal 128 karakter.';
    } elseif ($penulis === '' || text_length($penulis) > 64) {
        $error = 'Penulis wajib diisi dan maksimal 64 karakter.';
    } elseif (!in_array($jenis, $allowedTypes, true)) {
        $error = 'Jenis buku tidak valid.';
    } elseif ($tahun < 1000 || $tahun > $currentYear) {
        $error = 'Tahun terbit tidak valid.';
    } elseif ($penerbit === '' || text_length($penerbit) > 64) {
        $error = 'Penerbit wajib diisi dan maksimal 64 karakter.';
    } elseif ($rak === '' || text_length($rak) > 10) {
        $error = 'Kode rak wajib diisi dan maksimal 10 karakter.';
    } elseif ($stok < 0 || $harga < 0) {
        $error = 'Stok dan harga tidak boleh negatif.';
    } elseif (text_length($sinopsis) > 1000) {
        $error = 'Sinopsis maksimal 1.000 karakter.';
    } else {
        $newCover = '';
        try {
            $newCover = save_book_cover($_FILES['fileToUpload'] ?? [], __DIR__ . '/../image/buku');
            if ($isCreate && $newCover === '') {
                throw new RuntimeException('Cover buku wajib dipilih.');
            }

            $cover = $newCover !== '' ? $newCover : (string) $tampil_data['gambar'];
            $actor = (string) $account['username'];

            if ($isCreate) {
                $stmt = mysqli_prepare($db, 'INSERT INTO t_buku (nama_buku, jenis, penulis, penerbit, tahun_terbit, harga, kode_rak, stok, sinopsis, gambar, create_date, create_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)');
                mysqli_stmt_bind_param($stmt, 'ssssiisisss', $nama, $jenis, $penulis, $penerbit, $tahun, $harga, $rak, $stok, $sinopsis, $cover, $actor);
            } else {
                $stmt = mysqli_prepare($db, 'UPDATE t_buku SET nama_buku = ?, jenis = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, harga = ?, kode_rak = ?, stok = ?, sinopsis = ?, gambar = ?, update_date = NOW(), update_by = ? WHERE id_t_buku = ?');
                mysqli_stmt_bind_param($stmt, 'ssssiisisssi', $nama, $jenis, $penulis, $penerbit, $tahun, $harga, $rak, $stok, $sinopsis, $cover, $actor, $bukuId);
            }

            if (!mysqli_stmt_execute($stmt)) {
                throw new RuntimeException(mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            if ($newCover !== '' && !$isCreate && !empty($book['gambar']) && $book['gambar'] !== $newCover) {
                $oldPath = realpath(__DIR__ . '/../image/buku/' . basename($book['gambar']));
                $coverDir = realpath(__DIR__ . '/../image/buku');
                if ($oldPath && $coverDir && starts_with($oldPath, $coverDir . DIRECTORY_SEPARATOR)) {
                    @unlink($oldPath);
                }
            }

            redirect_to('data_buku.php');
        } catch (Throwable $exception) {
            if ($newCover !== '') {
                @unlink(__DIR__ . '/../image/buku/' . $newCover);
            }
            error_log($exception->getMessage());
            $error = $exception->getMessage();
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
            <form class="form-horizontal" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="form-group"><label class="control-label col-sm-4">Nama Buku</label><div class="col-sm-8"><input type="text" maxlength="128" class="form-control" name="nama" value="<?php echo e($tampil_data['nama_buku']); ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Penulis</label><div class="col-sm-8"><input type="text" maxlength="64" class="form-control" name="penulis" value="<?php echo e($tampil_data['penulis']); ?>" required></div></div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Jenis Buku</label>
                    <div class="col-sm-8"><select name="jenisnya" class="form-control">
                        <?php foreach (['Umum', 'Komputer', 'Novel', 'Pengembangan Diri', 'Komik'] as $type): ?>
                            <option value="<?php echo e($type); ?>" <?php echo $tampil_data['jenis'] === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option>
                        <?php endforeach; ?>
                    </select></div>
                </div>
                <div class="form-group"><label class="control-label col-sm-4">Tahun Terbit</label><div class="col-sm-8"><input type="number" min="1000" max="<?php echo (int) date('Y') + 1; ?>" class="form-control" name="tahun" value="<?php echo (int) $tampil_data['tahun_terbit']; ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Penerbit</label><div class="col-sm-8"><input type="text" maxlength="64" class="form-control" name="penerbit" value="<?php echo e($tampil_data['penerbit']); ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Kode Rak</label><div class="col-sm-8"><input type="text" maxlength="10" class="form-control" name="koderak" value="<?php echo e($tampil_data['kode_rak']); ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Stok Tersedia</label><div class="col-sm-8"><input type="number" min="0" class="form-control" name="stok" value="<?php echo (int) $tampil_data['stok']; ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Harga</label><div class="col-sm-8"><input type="number" min="0" class="form-control" name="harga" value="<?php echo (int) $tampil_data['harga']; ?>" required></div></div>
                <div class="form-group"><label class="control-label col-sm-4">Sinopsis</label><div class="col-sm-8"><textarea name="sinopsis" maxlength="1000" class="form-control" rows="4"><?php echo e($tampil_data['sinopsis']); ?></textarea></div></div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Cover Buku</label>
                    <div class="col-sm-8">
                        <input type="file" name="fileToUpload" accept="image/jpeg,image/png,image/webp" <?php echo $isCreate ? 'required' : ''; ?>>
                        <?php if (!$isCreate && !empty($tampil_data['gambar'])): ?><p class="help-block">Cover saat ini: <?php echo e($tampil_data['gambar']); ?></p><?php endif; ?>
                    </div>
                </div>
                <div class="form-group text-right"><div class="col-sm-offset-4 col-sm-8"><a href="data_buku.php" class="btn btn-default">Batal</a> <button type="submit" class="btn btn-primary">Simpan</button></div></div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
