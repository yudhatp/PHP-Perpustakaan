<?php 
	include("../setting/koneksi.php");
	$idnya = $_GET['id'];
	$sql = "DELETE FROM t_staff WHERE id_t_staff = $idnya ";
	$result = mysqli_query($db,$sql);
	//echo $sql;
	header("location:data_staff.php");

?>