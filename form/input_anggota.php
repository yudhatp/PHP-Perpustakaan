<?php
require_once '../setting/session.php';
include("header.php");	
$status_w = "";

$id_ag = mysqli_real_escape_string($db,$_GET['id']);

if ($id_ag <> ''){
	$judul = "Edit Anggota";
	
	$sql_data = "SELECT * FROM t_anggota WHERE id_t_anggota = $id_ag ";
	//echo $sql_data;
	$result_data = mysqli_query($db,$sql_data);
	$tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);
	
	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	$role_id = $row['id_p_role'];
	
}else{
	//$judul = "Input Anggota";
	header('location:dashboard.php');
}

if(isset($_POST['btnsubmit'])) {
	$nama = $_POST['nama'];
	$tgllahir = $_POST['tgllahir'];
	$jekel = $_POST['optjk'];
	$notelp = $_POST['notelp'];
	$alamat = $_POST['alamat'];
	$ket = $_POST['ket'];
	$statusnya = $_POST['statusnya'];
	$avatar = $_POST[''];
		
	//generate no anggota
	$awal = "ANG"; //ANG10022016
	$temp = str_replace("-","",$tgllahir);
	$lahir = substr($temp,4,4);
	$tahun = date("Y");
	
	$no_anggota = $awal.$lahir.$tahun;

	$sql_up = "SELECT COUNT(*) AS JUM FROM t_anggota A JOIN t_account B ON A.id_t_account = B.id_t_account WHERE A.id_t_anggota = $id_ag ";
	$result_up = mysqli_query($db,$sql_up);
	$row_up = mysqli_fetch_array($result_up,MYSQLI_ASSOC);
	$is_update = $row_up['JUM'];
	
	if ($is_update == 1){
		$query = "UPDATE t_anggota SET nama = '$nama' , tgl_lahir = '$tgllahir', jenis_kelamin = '$jekel',no_telp = '$notelp',
				  alamat = '$alamat',keterangan = '$ket',status = '$statusnya',update_date = curdate(),update_by = '$usersession' 
				  WHERE id_t_anggota = $id_ag";
	}else{
		$query = "INSERT INTO t_anggota(id_t_account,no_anggota,nama,tgl_daftar,tgl_lahir,jenis_kelamin,no_telp,alamat,keterangan,
				  status,create_date,create_by) 
				  VALUES($idnya,'$no_anggota','$nama', curdate(),'$tgllahir','$jekel','$notelp','$alamat','$ket',
				  'Aktif',curdate(),'$usersession')";
	}
	//echo $query;
	
	if ($db->query($query)) {
		if($row['id_p_role']==1 || $row['id_p_role']==2){
			header('location:data_anggota.php');
		}else{
			header('location:dashboard.php');
		}
	}
	 $db->close();
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
			<form class="form-horizontal" method="post">
				<div class="form-group">
					<label class="control-label col-sm-4">No Anggota</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo $tampil_data['no_anggota'];?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Nama Anggota</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="nama" value="<?php echo $tampil_data['nama'];?>" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Daftar</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo date("Y-m-d");?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Lahir</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="tgllahir" 
							value="<?php echo $tampil_data['tgl_lahir'];?>" id="tgllahir" required  />
					</div>
				</div>
				<?php
					if($tampil_data['jenis_kelamin'] == "Pria"){
						$status_p = "checked";
					}else if($tampil_data['jenis_kelamin'] == "Wanita"){
						$status_w = "checked";
					}
				?>
				<div class="form-group">
					<label class="control-label col-sm-4">Jenis Kelamin</label>
					<div class="col-sm-8">
						<label class="radio-inline">
							<input type="radio" name="optjk" value="Pria" <?php echo $status_p;?> >Pria
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="optjk" value="Wanita" <?php echo $status_w;?>>Wanita
                        </label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">No Telepon</label>
					<div class="col-sm-8">
					<input type="text" maxlength="15" class="form-control" onkeypress="return isNumber(event)" value="<?php echo $tampil_data['no_telp'];?>" name="notelp" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Alamat</label>
					<div class="col-sm-8">
					<textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($tampil_data['alamat']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Keterangan</label>
					<div class="col-sm-8">
					<textarea name="ket" class="form-control" rows="3"><?php echo htmlspecialchars($tampil_data['keterangan']); ?></textarea>
					</div>
				</div>
				<?php 
				if ($role_id == 3){
				?>
				<div class="form-group">
					<label class="control-label col-sm-4">Status</label>
					<div class="col-sm-8">
						<select disabled name="statusnya" class="form-control">
                            <option>Aktif</option>
                            <option>Tidak Aktif</option>
                        </select>
					</div>
				</div>
				<?php				
				}else{
				?>
				<?php
					if($tampil_data['status'] == "Aktif") {
						$aktif = "selected=selected";
					}else if($tampil_data['status'] == "Tidak Aktif") {
						$tidak = "selected=selected";
					}
				?>
				<div class="form-group">
					<label class="control-label col-sm-4">Status</label>
					<div class="col-sm-8">
						<select name="statusnya" class="form-control">
                            <option <?php echo $aktif;?> >Aktif</option>
                            <option <?php echo $tidak;?> >Tidak Aktif</option>
                        </select>
					</div>
				</div>
				<?php
				}
				?>
				<div class="form-group" align="right">
				<div class="col-sm-4">
				<!-- sengaja dikosongin :D-->
				</div>
				<div class="col-sm-8">
					<button type="button" id="btnCancel" class="btn btn-default">Batal</button>
					<button type="submit" name="btnsubmit" class="btn btn-primary">Simpan</button>
					</div>
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
		$(document).ready(function(e) {
		$("#tgllahir").datepicker({dateFormat: "yy-mm-dd"});

		$("#btnCancel").click(function(){
			window.location.href = "dashboard.php";
		});
	});
</script>