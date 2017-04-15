<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

include("header.php");	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Laporan Peminjaman</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-6">
			<form class="form-horizontal" method="post" action="cetak_peminjaman.php">
				isi salah satu tanggal atau kedua nya untuk range tanggal laporan
				<br><br>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Awal</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="tglawal" id="tglawal" />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Akhir</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="tglakhir" id="tglakhir"  />
					</div>
				</div>
				<div class="form-group" align="right">
				<div class="col-sm-4">
				<!-- sengaja dikosongin :D-->
				</div>
				<div class="col-sm-8">
					<button type="submit" name="btnsubmit" class="btn btn-primary">Cetak</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php
include("footer.php");
?>
<script type="text/javascript">
		$(document).ready(function(e) {
		$("#tglawal").datepicker({dateFormat: "yy-mm-dd"});
		$("#tglakhir").datepicker({dateFormat: "yy-mm-dd"});
	});
</script>