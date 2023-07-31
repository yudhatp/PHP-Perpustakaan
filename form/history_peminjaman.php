<?php
include("header.php");
$id_ag = mysqli_real_escape_string($db,$_GET['id']);

if ($id_ag <> ''){
	
	$sql_data = "SELECT * FROM v_history_peminjaman WHERE id_t_anggota = $id_ag ";
	//echo $sql_data;
	$result_data = mysqli_query($db,$sql_data);
	$tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);
	
	$usersession = $_SESSION['login_user'];

	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	$roleid = $row['id_p_role'];
	
}else{
	header('location:dashboard.php');
}

?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">History Peminjaman</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-12">
			<table>
						<tr>
							<td>No Anggota :</td>
							<td><?php echo $tampil_data['no_anggota']; ?></td>
						</tr>
						<tr>
							<td>Nama :</td>
							<td><?php echo $tampil_data['nama']; ?></td>
						</tr>
						<tr>
							<td>Tanggal Daftar:</td>
							<td><?php echo $tampil_data['tgl_daftar']; ?></td>
						</tr>
						<tr>
							<td>Tanggal Terakhir Peminjaman :</td>
							<td><?php echo $tampil_data['tgl_terakhir_pinjam']; ?></td>
						</tr>
			</table>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-lg-12">
		<table class="table table-striped table-bordered table-hover">
			<tr>
				<th>No</th>
				<th>No Peminjaman</th>
				<th>Staff</th>
				<th>Tanggal Pinjam</th>
				<th>Tanggal Kembali</th>
				<th>Jumlah Buku</th>
				<th>Detil</th>
			</tr>
		  <?php
			$hal=1;
	
			if (!isset($_GET['hal'])) {
				$page=1;
			}else{
				$page= $_GET['hal'];
			}
			
			$max_results = 10;
			$from = (($page * $max_results) - $max_results);
			
			$sql_det = "SELECT * FROM v_peminjaman WHERE id_t_anggota = $id_ag LIMIT $from, $max_results";
			$result_det = mysqli_query($db,$sql_det);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result_det,MYSQLI_ASSOC))
			{
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil['no_peminjaman']; ?></td>
						<td><?php echo $tampil['staff']; ?></td>
						<td><?php echo $tampil['tgl_pinjam']; ?></td>
						<td><?php echo $tampil['tgl_kembali']; ?></td>
						<td><?php echo $tampil['jum']; ?></td>
						<td><a href="detil_peminjaman.php?id=<?php echo $tampil['id_t_peminjaman'];?>" class="btn btn-warning">Detil</a></td>
					</tr>
					
				<?php
				$no++;
			}
				?>
		  </table>
		  <br>
		  <?php
				$total_sql = "SELECT COUNT(*) AS NUM FROM v_peminjaman";
				$total_results = mysqli_query($db,$total_sql);
				$row = mysqli_fetch_array($total_results,MYSQLI_ASSOC);
				$jum = $row['NUM'];
				$total_pages= ceil($jum / $max_results);
				//echo $jum;
				echo "<center> Halaman <br>";
				
				if ($hal > 1){
					$prev= ($page - 1);
					}
					
				for($i=1; $i<=$total_pages; $i++){
						if (($hal)== $i){
							echo "<a href=$_SERVER[PHP_SELF]?hal=$i> $i</a>";
							}else{
							echo "<a href=$_SERVER[PHP_SELF]?hal=$i> $i</a>";
							}
						}
						
				if($hal < $total_pages){
					$next=($page + 1);
					}
				
				echo "</center>";
				?>
		  </div>
	</div>
	<div class="row">
		<div class="col-sm-8">
			<?php
			if($roleid!=3){
			?>
			<a href="data_anggota.php" class="btn btn-primary">Kembali</a>
			<?php
			}else{
			?>
			<a href="dashboard.php" class="btn btn-primary">Kembali</a>
			<?php
			}
			?>
		</div>
	</div>
</div>
<?php
include("footer.php");
?>