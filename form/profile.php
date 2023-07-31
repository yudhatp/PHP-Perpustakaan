<?php
require_once '../setting/session.php';
include("header.php");	

	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	$roleid = $row['id_p_role'];
	//echo $sql;
	
	if($roleid==1){
		//admin
		$sql_profile ="SELECT 'Admin' as nama, C.nama_role, A.create_date AS tgl_register, 
						IFNULL(A.update_date,'-') AS last_change_pass,
						'-' AS last_change_profile
						FROM t_account A 
						JOIN P_ROLE C ON A.id_p_role = C.id_p_role
						WHERE A.username = 'adm'";
	}else{
	
		if($roleid==2){
			$sql_profile = "SELECT B.nama, C.nama_role, A.create_date AS tgl_register, 
						IFNULL(A.update_date,'-') AS last_change_pass,
						IFNULL(B.update_date,'-') AS last_change_profile
						FROM t_account A 
						JOIN t_staff B ON A.id_t_account = B.id_t_account 
						JOIN P_ROLE C ON A.id_p_role = C.id_p_role
						WHERE A.username = '$usersession'";
			$url_edit = "input_staff.php?id=$idnya";
		}else{
			$sql_profile = "SELECT B.no_anggota, B.nama, C.nama_role, A.create_date AS tgl_register, 
						IFNULL(A.update_date,'-') AS last_change_pass,
						IFNULL(B.update_date,'-') AS last_change_profile
						FROM t_account A 
						JOIN t_anggota B ON A.id_t_account = B.id_t_account 
						JOIN P_ROLE C ON A.id_p_role = C.id_p_role
						WHERE A.username = '$usersession'";
			$url_edit = "input_anggota.php?id=$idnya";
		}
		//echo $sql_profile;
	}
	
	$result = mysqli_query($db,$sql_profile);
	$tampil = mysqli_fetch_array($result,MYSQLI_ASSOC);
	

?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Profile</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-12">
			<div class="row">
				<label class="col-sm-2 col-form-label">Nama</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['nama'] ?? ''; ?></p>
				</div>
			</div>

			<?php if($row['id_p_role']==3){ ?>
			<div class="row">
				<label class="col-sm-2 col-form-label">No Anggota</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['no_anggota'] ?? ''; ?></p>
				</div>
			</div>

			<?php }else{ ?>

			<div class="row">
				<label class="col-sm-2 col-form-label">Role</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['nama_role'] ?? ''; ?></p>
				</div>
			</div>

			<?php } ?>
			
			<div class="row">
				<label class="col-sm-2 col-form-label">Tanggal Register</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['tgl_register'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Last Changed Password</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['last_change_pass'] ?? ''; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Last Changed Profile</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['last_change_profile'] ?? ''; ?></p>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-8">
			<?php
			if($roleid==1){
			?>
			<a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
			<?php
			}else if($roleid==2){
			?>
			<a href="<?php echo $url_edit;?>" class="btn btn-primary">Ubah Profile</a>
			<a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
			<?php
			}else{
			?>
			<a href="<?php echo $url_edit;?>" class="btn btn-primary">Ubah Profile</a>
			<a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
			<a href="cetak_kartu_anggota.php?id=<?php echo $idnya;?>" class="btn btn-primary">Cetak Kartu Anggota</a>
			<?php
			}
			?>
		</div>
	</div>
</div>
<?php
include("footer.php");
?>