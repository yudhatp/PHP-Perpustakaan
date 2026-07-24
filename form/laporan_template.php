<?php
require_once __DIR__ . '/../setting/session.php';
require_roles($db, $allowedRoles);
include __DIR__ . '/header.php';
?>
<div id="page-wrapper">
    <div class="row"><div class="col-lg-12"><h1 class="page-header"><?php echo e($reportTitle); ?></h1></div></div>
    <div class="row"><div class="col-lg-7">
        <div class="panel panel-default">
            <div class="panel-heading">Pilih periode laporan</div>
            <div class="panel-body">
                <p class="text-muted">Kosongkan kedua tanggal untuk mencetak seluruh data.</p>
                <form class="form-horizontal" method="post" action="<?php echo e($reportAction); ?>" target="_blank">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="tglawal">Tanggal Awal</label>
                        <div class="col-sm-8"><input type="date" class="form-control" name="tglawal" id="tglawal"></div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="tglakhir">Tanggal Akhir</label>
                        <div class="col-sm-8"><input type="date" class="form-control" name="tglakhir" id="tglakhir"></div>
                    </div>
                    <div class="form-group"><div class="col-sm-offset-4 col-sm-8">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-print"></i> Cetak PDF</button>
                    </div></div>
                </form>
            </div>
        </div>
    </div></div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
