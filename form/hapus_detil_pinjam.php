<?php 
	include("../setting/koneksi.php");
	require_once '../setting/session.php';
	
	$usersession = $_SESSION['login_user'];
	
	$id = mysqli_real_escape_string($db,$_GET['id']);
	$id_buku = mysqli_real_escape_string($db,$_GET['id_buku']);
	
	//dapetin qty buku
	$sql = "SELECT qty FROM t_detil_pinjam WHERE id_t_buku = $id_buku AND id_t_peminjaman = $id";
	//echo $sql;
	$result = mysqli_query($db,$sql);
	$tampil = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$qty = $tampil['qty'];
	
	//update stok buku
	$query_stok = "UPDATE t_buku SET stok = stok+$qty WHERE id_t_buku = $id_buku";
	//echo $query_stok;
	$db->query($query_stok);
	
	$query_del = "DELETE FROM t_detil_pinjam WHERE id_t_buku = $id_buku AND id_t_peminjaman = $id";
	//echo $query_del;
	$db->query($query_del);
	
	$query_pj = "UPDATE t_peminjaman SET update_date = curdate(), update_by='$usersession' WHERE id_t_peminjaman = $id";
	//echo $query_pj;
	$db->query($query_pj);
	
	$db->close();
	header("location:input_peminjaman.php?id=".$id);
?>