<?php
	include("../setting/koneksi.php");
	$where = " WHERE 1=1 ";

			if($_GET['txtNama'] <>'' ){
				$where .= " AND nama LIKE '%".$_GET['txtNama']."%' ";
			}
					if($_GET['txtAlamat'] <>'' ){
						$where .= " AND alamat LIKE '%".$_GET['txtAlamat']."%' ";
					}
						if($_GET['txtStatus'] <>'' && $_GET['txtStatus'] <> 'Semua'){
							$where .= " AND status = '".$_GET['txtStatus']."' ";
						} 
include("header.php");	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Data Staff</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
   <div class="row">
		<div class="col-lg-8 col-md-6">
		<form method="GET">
		<table>
		  <tr>
			<td>Nama Staff&nbsp;</td>
			<td><input type="text" class="form-control"  name="txtNama" value="<?php echo $_GET['txtNama']; ?>"></td>
			<td>&nbsp;Status&nbsp;</td>
			<?php
					if($_GET['txtStatus']  == "Aktif") {
						$aktif = "selected=selected";
					}else if($_GET['txtStatus']  == "Tidak Aktif") {
						$tidak = "selected=selected";
					}
				?>
			<td>
				<select class="form-control" name="txtStatus">
						<option>Semua</option>
						<option <?php echo $aktif;?> >Aktif</option>
                        <option <?php echo $tidak;?> >Tidak Aktif</option>
				</select>
			</td>
		  </tr>
		  <tr colspan="2">
			<td>Alamat&nbsp;</td>
			<td><input type="text" class="form-control"  name="txtAlamat" value="<?php echo $_GET['txtAlamat']; ?>"></td>
		  </tr>
		  <tr style="height:50px">
			<td></td>
			<td valign="middle"><button type="submit" class="btn btn-small btn-primary btn-block" name="btncari">Cari</button></td>
			<td></td>
			<td></td>
		  </tr>
		</table>
		</form>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-lg-12">
		<table width="100%" class="table table-striped table-bordered table-hover">
			<tr>
				<th>No</th>
				<th>Nama Staff</th>
				<th>Alamat</th>
				<th>Status</th>
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
			
			$sql = "SELECT * FROM t_staff $where LIMIT $from, $max_results";
			//echo $sql;
			$result = mysqli_query($db,$sql);
			$jum_data = mysqli_num_rows($result);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil['nama']; ?></td>
						<td><?php echo $tampil['alamat']; ?></td>
						<td><?php echo $tampil['status']; ?></td>
						<td><a href="input_staff.php?id=<?php echo $tampil['id_t_staff'];?>" class="btn btn-info">Edit</a></td>
						<td><a onclick="if(confirm('Apakah anda yakin ingin menghapus data ini ??')){ location.href='hapus_staff.php?id=<?php echo $tampil['id_t_staff']; ?>' }" class="btn btn-danger">Hapus</a></td>
						</td>
					</tr>
					
				<?php
				$no++;
			}
				?>
		  </table>
		  <br>
		  <?php
				$total_sql = "SELECT COUNT(*) AS NUM FROM t_staff";
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