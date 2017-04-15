<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

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
	
include("header.php");	
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
			<table>
						<tr>
							<td>Nama :</td>
							<td><?php echo $tampil['nama']; ?></td>
						</tr>
						<?php
						if($row['id_p_role']==3){
						?>
						<tr>
							<td>No Anggota :</td>
							<td><?php echo $tampil['no_anggota']; ?></td>
						</tr>
						<?php
						}else{
						?>
						<tr>
							<td>Role :</td>
							<td><?php echo $tampil['nama_role']; ?></td>
						</tr>
						<?php
						}
						?>
						<tr>
							<td>Tanggal Register:</td>
							<td><?php echo $tampil['tgl_register']; ?></td>
						</tr>
						<tr>
							<td>Last Changed Password :</td>
							<td><?php echo $tampil['last_change_pass']; ?></td>
						</tr>
						<tr>
							<td>Last Changed Profile :</td>
							<td><?php echo $tampil['last_change_profile']; ?></td>
						</tr>
			</table>
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