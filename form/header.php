<?php 
	require_once '../setting/koneksi.php';
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	
	if(!isset($_SESSION['login_user'])){
		header("location:../index.php");
	}

	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	
	if($row['id_p_role']==1){
		include("header_admin.php");
	}else if($row['id_p_role']==2){
			include("header_staff.php");
			}else if($row['id_p_role']==3){
					include("header_anggota.php");
					}else{
		header("location:../index.php");
	}
?>