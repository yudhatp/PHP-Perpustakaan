<?php
require_once '../setting/koneksi.php';
//require_once '../setting/session.php';
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

include("header.php");	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Sinopsis Buku</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-12">
		<img src="<?php echo "../image/buku/".$tampil['gambar'];?>" align="left" width="400" height="500">
			<table>
						<tr>
							<th>Judul:</th>
							<td><?php echo $tampil['nama_buku']; ?></td>
						</tr>
						<tr>
							<th>Jenis:</th>
							<td><?php echo $tampil['jenis']; ?></td>
						</tr>
						<tr>
							<th>Penulis:</th>
							<td><?php echo $tampil['penulis']; ?></td>
						</tr>
						<tr>
							<th>Penerbit:</th>
							<td><?php echo $tampil['penerbit']; ?></td>
						</tr>
						<tr>
							<th>Tahun:</th>
							<td><?php echo $tampil['tahun_terbit']; ?></td>
						</tr>
						<tr>
							<th valign="top">Sinopsis :</th>
							<td><?php echo $tampil['sinopsis']; ?></td>
						</tr>
			</table>
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