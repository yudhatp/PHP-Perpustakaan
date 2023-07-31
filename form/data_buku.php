<?php
	require_once '../setting/session.php';
	include("header.php");	
	
	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	
	$where = " WHERE 1=1 ";
	if(isset($_GET['txtNama'])){
		$where .= " AND nama_buku LIKE '%".$_GET['txtNama']."%' ";
	}
	
	if(isset($_GET['txtTahun'])){
		$where .= " AND tahun_terbit = '".$_GET['txtTahun']."' ";
	}
					
	if(isset($_GET['txtPenulis'])){
		$where .= " AND penulis LIKE '%".$_GET['txtPenulis']."%' ";
	}
						
	if(isset($_GET['txtJenis']) && $_GET['txtJenis'] <> 'Semua'){
		$where .= " AND jenis LIKE '%".$_GET['txtJenis']."%' ";
	} 
							
	if(isset($_GET['txtPenerbit'])){
		$where .= " AND penerbit LIKE '%".$_GET['txtPenerbit']."%' ";
	} 
	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Data Buku</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
    <div class="row">
		<div class="col-lg-12">
		<form method="GET">
			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Nama Buku</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtNama" value="<?php echo $_GET['txtNama'] ?? ''; ?>">
				</div>
				<label class="col-sm-2 col-form-label">Tahun Terbit</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtTahun" value="<?php echo $_GET['txtTahun'] ?? ''; ?>">
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Nama Penulis</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtPenulis" value="<?php echo $_GET['txtPenulis'] ?? ''; ?>">
				</div>
				<label class="col-sm-2 col-form-label">Jenis Buku</label>
				<div class="col-sm-4">
					<?php
					if(isset($_GET['txtJenis'])){
						if($_GET['txtJenis'] == "Umum") {
						 $um = "selected=selected";
						}else if($_GET['txtJenis'] == "Komputer") {
						$kom = "selected=selected";
						}else if($_GET['txtJenis'] == "Novel") {
							$nov = "selected=selected";
							}else if($_GET['txtJenis'] == "Pengembangan Diri") {
								$pd = "selected=selected";
								}else if($_GET['txtJenis'] == "Komik") {
									$kk = "selected=selected";
								}
							}
					?>
					<select name="txtJenis" class="form-control">
							<option>Semua</option>
							<option <?php echo $um;?> >Umum</option>
                            <option <?php echo $kom;?> >Komputer</option>
                            <option <?php echo $nov;?> >Novel</option>
							<option <?php echo $pd;?> >Pengembangan Diri</option>
							<option <?php echo $kk;?> >Komik</option>
                    </select>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Penerbit</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtPenerbit" value="<?php echo $_GET['txtPenerbit'] ?? ''; ?>">
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
		<table width="100%" class="table table-striped table-bordered table-hover">
			<tr>
				<th>No</th>
				<th>Cover</th>
				<th>Nama Buku</th>
				<th>Jenis Buku</th>
				<th>Penulis</th>
				<th>Tahun Terbit</th>
				<th>Penerbit</th>
				<th>Sinopsis</th>
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
			
			$sql = "SELECT * FROM t_buku $where LIMIT $from, $max_results";
			$result = mysqli_query($db,$sql);
			$jum_data = mysqli_num_rows($result);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				$sinopsis = substr($tampil['sinopsis'],0,250);
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><img src="<?php echo "../image/buku/".$tampil['gambar'];?>" class="small" align="left" width="150" height="200"></td>
						<td><?php echo $tampil['nama_buku']; ?></td>
						<td><?php echo $tampil['jenis']; ?></td>
						<td><?php echo $tampil['penulis']; ?></td>
						<td><?php echo $tampil['tahun_terbit']; ?></td>
						<td><?php echo $tampil['penerbit']; ?></td>
						<td><a href="sinopsis_buku.php?id=<?php echo $tampil['id_t_buku'];?>" class="btn btn-primary">Sinopsis</a></td>
						<?php
							if($row['id_p_role']==3){
						?>
						<td>-</td>
						<td>-</td>
						<?php
							}else{
						?>
						<td><a href="input_buku.php?id=<?php echo $tampil['id_t_buku'];?>" class="btn btn-info">Edit</a></td>
						<td><a onclick="if(confirm('Apakah anda yakin ingin menghapus data ini ??')){ location.href='hapus_buku.php?id=<?php echo $tampil['id_t_buku']; ?>' }" class="btn btn-danger">Hapus</a></td>
						</td>
						<?php } ?>
					</tr>
					
		  <?php
				$no++;
			}
		  ?>
		  </table>
		  <br>
		  <?php
				$total_sql = "SELECT COUNT(*) AS NUM FROM t_buku ";
				$total_results = mysqli_query($db,$total_sql);
				$row = mysqli_fetch_array($total_results,MYSQLI_ASSOC);
				$jum = $row['NUM'];
				$total_pages= ceil($jum / $max_results);
				
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