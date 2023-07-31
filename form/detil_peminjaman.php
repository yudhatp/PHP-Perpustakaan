<?php
include("header.php");	
$id_pj = mysqli_real_escape_string($db,$_GET['id']);
$usersession = $_SESSION['login_user'];

if ($id_pj != null){
	$judul = "Detil Peminjaman";
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	$roleid = $row['id_p_role'];
}else{
	header('location:data_peminjaman.php');
}
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?php echo $judul;?></h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-12">
		  <?php
			$sql_pj = "SELECT * FROM v_peminjaman WHERE id_t_peminjaman = $id_pj";
			
			$result_pj = mysqli_query($db,$sql_pj);
			
			$tampil = mysqli_fetch_array($result_pj,MYSQLI_ASSOC);
				?>

			<div class="row">
				<label class="col-sm-2 col-form-label">No Peminjaman</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['no_peminjaman'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Nama Staff</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['staff'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Tanggal Pinjam</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['tgl_pinjam'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Tanggal Kembali</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['tgl_kembali'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">No Anggota</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['no_anggota'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Nama Anggota</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['anggota'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Jumlah Buku</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['jum'] ?? '0'; ?></p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-lg-12">
		<table class="table table-striped table-bordered table-hover">
			<tr>
				<th>No</th>
				<th>Nama Buku</th>
				<th>Penulis</th>
				<th>Harga</th>
				<th>Tanggal Kembali</th>
				<th>Qty</th>
			</tr>
		  <?php
			$sql_det = "SELECT * FROM v_detil_pinjam WHERE id_t_peminjaman = $id_pj";
			//echo $sql_det;
			$result_det = mysqli_query($db,$sql_det);
			$no = 1;
			while($tampil_det = mysqli_fetch_array($result_det,MYSQLI_ASSOC))
			{
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil_det['nama_buku']; ?></td>
						<td><?php echo $tampil_det['penulis']; ?></td>
						<td><?php echo $tampil_det['harga']; ?></td>
						<td><?php echo $tampil_det['tgl_kembali']; ?></td>
						<td><?php echo $tampil_det['qty']; ?></td>
					</tr>
				<?php
				$no++;
			}
				?>
		  </table>
		</div>
	</div>
	<br>
	<?php
	if($roleid==3){
	?>
	<a href="history_peminjaman.php?id=<?php echo $idnya;?>" class="btn btn-primary">Kembali</a>
	<?php
	}else{
	?>
	<a href="data_peminjaman.php" class="btn btn-primary">Kembali</a>
	<?php
	}
	?>
</div>
<?php 
include "footer.php";
?>