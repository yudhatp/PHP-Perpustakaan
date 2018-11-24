<?php
include("header.php");
$error = "";

$id_pj = mysqli_real_escape_string($db,$_GET['id']);
	
if($id_pj == null){
	$id_pj = 0;
	$judul = "Input Peminjaman";
}else{
	$judul = "Edit Peminjaman";
	$sql_data = "SELECT * FROM v_peminjaman WHERE id_t_peminjaman = $id_pj ";
	//echo $sql_data;
	$result_data = mysqli_query($db,$sql_data);
	$tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);
}

if(isset($_POST['btnsubmit'])) {
	$nama = $_POST['noanggota'];
	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	$roleid = $row['id_p_role'];
	
	$sql_ag = "SELECT id_t_anggota FROM t_anggota WHERE no_anggota = '$nama' ";
	$result_ag = mysqli_query($db,$sql_ag);
	$row_ag = mysqli_fetch_array($result_ag,MYSQLI_ASSOC);
	$id_ag = $row_ag['id_t_anggota'];
	//echo $sql_ag;
	
	if($id_pj == null){
		
		//cek dulu no anggotanya ada apa engga
		if($id_ag == null){
			$error = "No Anggota yang diinput salah";
		}else{
			//insert
			//generate no peminjaman
			$sql_2 = "SELECT IFNULL(MAX(no_peminjaman),0) AS nopj FROM t_peminjaman ";
			$result_2 = mysqli_query($db,$sql_2);
			$row_2 = mysqli_fetch_array($result_2,MYSQLI_ASSOC);
			$temp = $row_2['nopj'];
			//echo $temp."<br>";
			
			if($temp > 0){
				$no_peminjaman = "PJ-1000001"; //$awal.$lahir.$tahun;
			}else{
				$awal = "PJ-";
				$temp_pj = substr($temp,3);
				//echo $temp_pj;
				$tambah_no = $temp_pj+1;
				//echo $tambah_no ;
				$no_peminjaman = $awal.$tambah_no;
			}
			
			//echo $no_peminjaman;
			
			//jika admin,maka null saja id_t_staff nya
			if($roleid == 1){
				$idnya = "null";
			}
			$query = "INSERT INTO t_peminjaman(no_peminjaman,id_t_staff,id_t_anggota,tgl_pinjam,create_date,create_by) 
					  VALUES('$no_peminjaman',$idnya, $id_ag, curdate() ,curdate(),'$usersession')";
			//echo $query;
			$db->query($query);
			
			$sql_pj = "SELECT id_t_peminjaman FROM t_peminjaman ORDER BY id_t_peminjaman DESC LIMIT 1 ";
			$result_pj = mysqli_query($db,$sql_pj);
			$row_pj = mysqli_fetch_array($result_pj,MYSQLI_ASSOC);
			$id_pja = $row_pj['id_t_peminjaman'];
			$db->close();
			header('location:input_peminjaman.php?id='.$id_pja);
		}
	}else{
		//update
	}
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
		<div class="col-lg-6">
			<?php
							if ($error != ""){
							?>
							<div class="form-group">
									<div class="alert">
									<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
									<?php echo $error; ?>
									</div>
							</div>
							<?php
								}
							?>
			
			<br>
			<form class="form-horizontal" method="post" id="formpinjam">
				<div class="form-group">
					<label class="control-label col-sm-4">No Peminjaman</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo $tampil_data['no_peminjaman'];?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Nama Staff</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo $tampil_data['staff'];?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Pinjam</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo date("Y-m-d");?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">No Anggota</label>
					<div class="col-sm-8">
					<!--<select class="form-control" name="noanggota">
						<?php 
							/*$sql_ag = "SELECT no_anggota FROM t_anggota";
							$result_ag = mysqli_query($db,$sql_ag);
							while($tampil_ag = mysqli_fetch_array($result_ag,MYSQLI_ASSOC)){ */
							?>	
						<option value="<?php //echo $tampil_ag['no_anggota']; ?>"><?php //echo $tampil_ag['no_anggota'] ?></option>
							<?php 
							//}
							?>
					</select> -->
					<input type="text" maxlength="15" class="form-control" value="<?php echo $tampil_data['no_anggota'];?>" id="noanggota" name="noanggota" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Nama Anggota</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo $tampil_data['anggota'];?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Kembali</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo $tampil_data['tgl_kembali'];?></p>
					</div>
				</div>
				<div class="form-group" align="right">
				<div class="col-sm-4">
				<!-- sengaja dikosongin :D-->
				</div>
				<div class="col-sm-8">
					<a href="data_peminjaman.php" class="btn btn-default">Kembali</a>
					<?php
					if($id_pj == null){
					?>
					<button type="reset" class="btn btn-default">Batal</button>
					<button type="submit" name="btnsubmit" class="btn btn-primary">Simpan</button>
					<?php
					}else{
					?>
					<a href="cetak_bukti.php?id=<?php echo $id_pj;?>" class="btn btn-primary">Cetak</a>
					<?php
					}
					?>
					</div>
				</div>
				<hr>
		</div>
	</div>
	
	<div class="row">
		<div class="col-lg-12">
				<div class="form-group" align="left">
				<?php
					if($id_pj == null){
						//null
					}else{
					?>
				<div class="col-sm-4">
					<button type="button" name="btnmodal" class="btn btn-primary"  data-toggle="modal" data-target="#myModal">+</button>
					</div>
				</div>
				<?php
					}
				?>
				<HR>
				<table width="100%" class="table table-striped table-bordered table-hover">
					<tr>
						<th>No</th>
						<th>Nama Buku</th>
						<th>Penulis</th>
						<th>Qty</th>
						<th>Status</th>
						<th>Harga</th>
						<th>Denda</th>
						<th>Keterangan</th>
						<th colspan="2">Action</th>
					</tr>
					<?php
						$sql = "SELECT * FROM v_detil_pinjam WHERE id_t_peminjaman = $id_pj";
						//echo $sql;
						$result = mysqli_query($db,$sql);
						$no = 1;
						while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
						{
						?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil['nama_buku']; ?></td>
						<td><?php echo $tampil['penulis']; ?></td>
						<td><?php echo $tampil['qty']; ?></td>
						<td><?php echo $tampil['kondisi']; ?></td>
						<td><?php echo $tampil['harga']; ?></td>
						<td><?php echo $tampil['denda']; ?></td>
						<td><?php echo $tampil['keterangan']; ?></td>
						<?php
						if($tampil['kondisi'] == null){
						?>
						<td><a href="edit_detil_pinjam.php?id=<?php echo $tampil['id_t_detil_pinjam'];?>&id_pj=<?php echo $id_pj;?>" class="btn btn-info">Edit</a></td>
						<td><a onclick="if(confirm('Apakah anda yakin ingin menghapus data ini ??')){ location.href='hapus_detil_pinjam.php?id=<?php echo $id_pj;?>&id_buku=<?php echo $tampil['id_t_buku']; ?>' }" class="btn btn-danger">Hapus</a></td>
						<?php
						}else{
						?>
						<td>-</td>
						<td>-</td>
						<?php
						}
						?>
					</tr>
					<?php
				$no++;
			}
				?>
				</table>
			</form>
		</div>
	</div>
</div>
<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Input Buku</h4>
      </div>
	  <form method="post" action="simpan_detil_pinjam.php?id=<?php echo $_GET['id'];?>">
      <div class="modal-body">
			<div class="form-group">
				<label>Nama Buku</label>								
					<select class="form-control" name="idbuku">
						<?php 
							$sql_bk = "SELECT id_t_buku, nama_buku FROM t_buku";
							$result_bk = mysqli_query($db,$sql_bk);
							while($tampil = mysqli_fetch_array($result_bk,MYSQLI_ASSOC)){
							?>	
						<option value="<?php echo $tampil['id_t_buku']; ?>"><?php echo $tampil['nama_buku'] ?></option>
							<?php 
							}
							?>
					</select>
			</div>									
			<div class="form-group">
				<label>Qty</label>
				<input name="qty" type="text" maxlength="2" onkeypress="return isNumber(event)" class="form-control" placeholder="Qty" required/>
			</div>	
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="Simpan">
      </div>
	  </form>
    </div>

  </div>
</div>
<?php 
include "footer.php";
?>
<script language="javascript">
function isNumber(event){
  var charCode = (event.which) ? event.which : event.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57))
   return false;
 return true;
}
</script>
<script type="text/javascript">
$(document).ready(function() {
		/*$("#btnsubmit").click(function() {
			alert("masuk sini");
		var no_anggota = $("#noanggota").val();
		//var no_peminjaman = $("#email").val();
		if (no_anggota == '') {
		alert("kesalahan");
		} else {
		// Returns successful data submission message when the entered information is stored in database.
		$.post("simpan_peminjaman.php", {
		no_anggota1: no_anggota
		}, function(data) {
		alert(data);
		$('#formpinjam')[0].reset(); // To reset form fields
		});
		}
		}); */
	<?php
	$anggota = array();
	$buku = array();
	
	$sql_anggota = mysqli_query($db, "SELECT no_anggota FROM t_anggota");
	while(($data_anggota =  mysqli_fetch_assoc($sql_anggota))) {
		$anggota[] = $data_anggota['no_anggota'];
	}
	
	$sql_buku = mysqli_query($db, "SELECT nama_buku FROM t_buku");
	while(($data_buku =  mysqli_fetch_assoc($sql_buku))) {
		$buku[] = $data_buku['nama_buku'];
	}
	
	$php_anggota = $anggota;
	$php_buku = $buku;
	
	$js_anggota = json_encode($php_anggota);
	$js_buku = json_encode($php_buku);
	
	echo "var ar_anggota = ". $js_anggota . ";\n";
	echo "var ar_buku = ". $js_buku . ";\n";
	?>
	
    $("#noanggota").autocomplete({
      source: ar_anggota
    });
	
	$("#nmbuku").autocomplete({
      source: ar_buku
    });
  });
</script>