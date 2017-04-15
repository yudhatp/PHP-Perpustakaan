<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';
$id_st = mysqli_real_escape_string($db,$_GET['id']);
	
	$usersession = $_SESSION['login_user'];
	
	$sql_cek = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	//echo $sql_cek;
	$result_cek = mysqli_query($db,$sql_cek);
	$row_cek = mysqli_fetch_array($result_cek,MYSQLI_ASSOC);
	$id_akun = $row_cek['id_t_account'];
	
//jika yg buka anggota redirect ke dashboard
if($row_cek['id_p_role']==3){
	header('location:dashboard.php');
}

if ($id_st <> null){
	$judul = "Edit Staff";
	
	if($row_cek['id_p_role']==1){
		$sql_data = "SELECT * FROM t_staff WHERE id_t_staff = $id_st ";
	}else if($row_cek['id_p_role']==2){
		$sql_data = "SELECT * FROM t_staff WHERE id_t_account = $id_akun ";
	}else{
		header('location:dashboard.php');
	}
	
	//echo $sql_data;
	$result_data = mysqli_query($db,$sql_data);
	$tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);
}else{
	$judul = "Input Staff";
}

if(isset($_POST['btnsubmit'])) {
	$nama = $_POST['nama'];
	$alamat = $_POST['alamat'];
	$statusnya = $_POST['statusnya'];
	$nmuser = $_POST['user'];
	$passnya = $_POST['pass'];
	
	$hashed_password = md5($passnya);
	
	if ($id_st <> ''){
		if($row_cek['id_p_role']==1){
			$query = "UPDATE t_staff SET nama = '$nama', alamat = '$alamat',status = '$statusnya',update_date = curdate(),update_by = '$usersession' 
				  WHERE id_t_staff = $id_st";
		}else if($row_cek['id_p_role']==2){
			$query = "UPDATE t_staff SET nama = '$nama', alamat = '$alamat',status = '$statusnya',update_date = curdate(),update_by = '$usersession' 
				  WHERE id_t_account = $id_akun ";
		}
	}else{
		//insert data ke tabel account dulu
		$sql_akun = "INSERT INTO t_account(id_p_role,username, password, create_date, create_by)
					 VALUES(2,'$nmuser','$hashed_password',curdate(),'adm')";
		$resultnya = mysqli_query($db,$sql_akun);
		
		//dapetin id terbaru
		$sql_id = "SELECT MAX(id_t_account) AS id FROM t_account";
		//echo $sql_id;
		$result_id = mysqli_query($db,$sql_id);
		$tampil_id = mysqli_fetch_array($result_id,MYSQLI_ASSOC);
		$id_now = $tampil_id['id'];
	
		
		$query = "INSERT INTO t_staff(id_t_account,nama,alamat,status,create_date,create_by) 
				  VALUES($id_now,'$nama','$alamat','$statusnya',curdate(),'$usersession')";
	}
	//echo $query;
	
	/*$sql_cek = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	//echo $sql_cek;
	$result_cek = mysqli_query($db,$sql_cek);
	$row_cek = mysqli_fetch_array($result_cek,MYSQLI_ASSOC);*/
	
	if ($db->query($query)) {
		if($row_cek['id_p_role']==1){
			header('location:data_staff.php');
		}else if($row_cek['id_p_role']==2){
			header('location:dashboard.php');
		}
	}
	 $db->close();
}
include("header.php");	
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
					<label class="control-label col-sm-4">Nama Staff</label>
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
					<label class="control-label col-sm-4">Alamat</label>
					<div class="col-sm-8">
					<textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($tampil_data['alamat']); ?></textarea>
					</div>
				</div>
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
				if ($id_st == null){
				?>
				<div class="form-group">
					<label class="control-label col-sm-4">Username</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="user" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Password</label>
					<div class="col-sm-8">
					<input type="password" maxlength="10" class="form-control" name="pass" required  />
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
					<?php
					if($row_cek['id_p_role']==1){
					?>
					<a href="data_staff.php" class="btn btn-primary">Batal</a>
					<?php
					}else if($row_cek['id_p_role']==2){
					?>
					<a href="dashboard.php" class="btn btn-primary">Batal</a>
					<?php
					}
					?>
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