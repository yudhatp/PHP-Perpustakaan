<?php
   include("setting/koneksi.php");
   $error = "";
   session_start();
   
   if($_SERVER["REQUEST_METHOD"] == "POST") {
      // username and password sent from form 
      $myusername = mysqli_real_escape_string($db,$_POST['username']);
      $mypassword = mysqli_real_escape_string($db,$_POST['password']); 
	  
	  $hash_pass = md5($mypassword);
      
      $sql = "SELECT username FROM t_account WHERE username = '$myusername' and password = '$hash_pass'";
      $result = mysqli_query($db,$sql);
      $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
      
      $count = mysqli_num_rows($result);
		
      if($count == 1) {
         $_SESSION['login_user'] = $myusername;
     
		 header("location:form/dashboard.php");
      }else {
         $error = "Username atau Password anda salah!";
      }
   }
?>
<html>
   
   <head>
		<title>Login | Perpustakaan</title>
		<link href="template/css/bootstrap.min.css" rel="stylesheet" />
		<link href="template/css/sb-admin-2.css" rel="stylesheet" />
		<script src="template/js/bootstrap.min.js"></script>
		<link rel="stylesheet" type="text/css" href="template/alert.css">
   </head>
   <body>
   <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
					<div class="panel-heading">
                        <h3 class="panel-title">Silahkan Login</h3>
                    </div>
                    <div class="panel-body">
						<form method = "post">
						<fieldset>
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
						<div class="form-group">
							<input type="text" class="form-control" placeholder="username, contoh : ARY" name="username" required>
						</div>
						<div class="form-group">
							<input type="password" class="form-control" placeholder="Enter Password" name="password" required>
						</div>	
						<div class="form-group">
							<button type="submit" class="btn btn-md btn-primary btn-block">Login</button>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<span class="psw">belum punya akun <a href="register.php">daftar disini?</a></span>
							</div>
						</div>
						</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	 </div>
   </body>
</html>