<?php
include("header.php");
$where = " WHERE 1=1 ";

	$txtNo = "";
	$txtNama = "";
	$txtTgl = "";
	
	if(isset($_GET['txtNo'])){
		$txtNo = mysqli_real_escape_string($db,$_GET['txtNo']);
		if($txtNo != ""){
			$where .= " AND no_peminjaman LIKE '%$txtNo%' ";
		}
	}
	
	if(isset($_GET['txtNama'])){
		$txtNama = mysqli_real_escape_string($db,$_GET['txtNama']);
		if($txtNama != ""){
			$where .= " AND anggota LIKE '%$txtNama%' ";
		}
	}
	
	if(isset($_GET['txtTgl'])){
		$txtTgl = mysqli_real_escape_string($db,$_GET['txtTgl']);
		if($txtTgl != ""){
			$where .= " AND tgl_pinjam = '$txtTgl' ";
		}
	}
						
		
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Data Peminjaman</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
   <div class="row">
		<div class="col-lg-12">
		<form method="GET">
			<div class="form-group row">
				<label class="col-sm-2 col-form-label">No Peminjaman</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtNo" value="<?php echo $txtNo; ?>">
				</div>
				<label class="col-sm-2 col-form-label">Tanggal Peminjaman</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  id="txtTgl" name="txtTgl" value="<?php echo $txtTgl; ?>">
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Nama Anggota</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtNama" value="<?php echo $txtNama; ?>">
				</div>
				<div class="col-sm-6">
					<button type="submit" class="btn btn-small btn-primary btn-block">Cari</button>
				</div>
			</div>
		</form>
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
				<th>No Anggota</th>
				<th>Nama Anggota</th>
				<th>Jumlah Buku</th>
				<th colspan="2">Action</th>
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
			
			$sql = "SELECT * FROM v_peminjaman $where LIMIT $from, $max_results";
			//echo $sql;
			$result = mysqli_query($db,$sql);
			$jum_data = mysqli_num_rows($result);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil['no_peminjaman']; ?></td>
						<td><?php echo $tampil['staff']; ?></td>
						<td><?php echo $tampil['tgl_pinjam']; ?></td>
						<td><?php echo $tampil['tgl_kembali']; ?></td>
						<td><?php echo $tampil['no_anggota']; ?></td>
						<td><?php echo $tampil['anggota']; ?></td>
						<td><?php echo $tampil['jum']; ?></td>
						<td><a href="input_peminjaman.php?id=<?php echo $tampil['id_t_peminjaman'];?>" class="btn btn-info">Edit</a></td>
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
				
				//jumlah data setelah filter
				if($jum_data == 0){
					echo "Data tidak ditemukan";
				}
				
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
</div>
<?php 
include "footer.php";
?>
<script type="text/javascript">
		$(document).ready(function(e) {
		$("#txtTgl").datepicker({dateFormat: "yy-mm-dd"});
	});
</script>