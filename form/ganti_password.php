<?php
require_once '../setting/koneksi.php';
include("header.php");	
$error = "";

if(isset($_POST['btnsubmit'])) {
	$temp_lama = $_POST['password_lama'];
	$temp_baru = $_POST['password_baru'];
	$temp_confirm = $_POST['password_conf'];
	
	$lama = $db->real_escape_string($temp_lama);
	$baru = $db->real_escape_string($temp_baru);
	$confirm = $db->real_escape_string($temp_confirm);
 
	$hashed_lama = md5($lama);
	$hashed_baru = md5($baru);
 
	$usersession = $_SESSION['login_user'];
	
	$check_account = $db->query("SELECT username FROM t_account WHERE password='$hashed_lama'");
	$count=$check_account->num_rows;

	if($baru != $confirm){
		$error = "password baru tidak sama !";
	}else if($count==0) {
		$error = "password lama tidak sesuai !";
	}else{
		$query_update = "UPDATE t_account SET password = '$hashed_baru', update_date = curdate(), update_by = '$usersession' 
						WHERE username = '$usersession'";
		//echo $query_update;
		if ($db->query($query_update)) {
			header('location:profile.php');
			}
	
		$db->close();
	}
}

?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Ganti Password</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-6">
			<div class="panel-body">
						<form method="post">
							<?php
							if ($error != ""){
							?>
								<div class="alert">
								<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
									<?php echo $error; ?>
								</div>
							<?php
							}
							?>
							<br>
							<div class="form-group row">
								<label class="control-label col-sm-4">Password Lama</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" name="password_lama" required  /><br>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-sm-4">Password Baru</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password_baru" placeholder="Password Baru" name="password_baru" required  />
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-sm-4">Konfirm Password Baru</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password_conf" placeholder="Konfirm Password" name="password_conf" required  />
								</div>
							</div>
							<div class="form-group row" align="right">
							<div class="col-sm-4">
							<!-- sengaja dikosongin :D-->
							</div>
								<div class="col-sm-8">
									<button type="reset" class="btn btn-default">Batal</button>
									<button type="submit" name="btnsubmit" class="btn btn-primary">Ganti Password</button>
								</div>
							</div>
						</form>
					</div>
		</div>
	</div>
</div>
<?php 
include "footer.php";
?>