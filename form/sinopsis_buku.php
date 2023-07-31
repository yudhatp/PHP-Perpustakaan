<?php
require_once '../setting/koneksi.php';
include("header.php");	

$id_bk = mysqli_real_escape_string($db,$_GET['id']);
	//$usersession = $_SESSION['login_user'];

if($id_bk == null){
	header('location:data_buku.php');
}else{
	$sql = "SELECT * FROM t_buku WHERE id_t_buku = $id_bk ";
	$result = mysqli_query($db,$sql);
	$tampil = mysqli_fetch_array($result,MYSQLI_ASSOC);
	//echo $sql;
}


?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Sinopsis Buku</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-sm-4">
			<img src="<?php echo "../image/buku/".$tampil['gambar'];?>" align="left" width="400" height="500">
		</div>
		<div class="col-sm-8">
			<div class="row">
				<label class="col-sm-2 col-form-label">Judul</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['nama_buku'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Jenis</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['jenis'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Penulis</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['penulis'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Penerbit</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['penerbit'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Tahun Terbit</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['tahun_terbit'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Sinopsis</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['sinopsis'] ?? ''; ?></p>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-8">
			<a href="data_buku.php" class="btn btn-primary">Kembali</a>
		</div>
	</div>
	<br>
</div>
<?php
include("footer.php");
?>