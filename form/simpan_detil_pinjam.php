<?php 
	include("../setting/koneksi.php");

	$id = $_GET['id'];
	$idbuku = $_POST['idbuku'];
	$qty = $_POST['qty'];
	$usersession = $_SESSION['login_user'];
	
	//cek stok buku
	$query_cek = "SELECT stok FROM t_buku WHERE id_t_buku = $idbuku";
	$result_cek = mysqli_query($db,$query_cek);
	$data_cek = mysqli_fetch_array($result_cek,MYSQLI_ASSOC);
	$sisa_stock = $data_cek['stok'];
	//echo $query_cek;
	//echo $sisa_stock;
	
	if($qty > $sisa_stock){
		header("location:input_peminjaman.php?id=".$id."&error=stok_kurang");
	}else{
		
		//simpan detil pinjam
		$query = "INSERT INTO t_detil_pinjam(id_t_peminjaman,id_t_buku,qty) 
				  VALUES($id,$idbuku, $qty)";
		//echo $query;
		$db->query($query);
		
		$query_stok = "UPDATE t_buku SET stok = stok-$qty WHERE id_t_buku = $idbuku";
		//echo $query_stok;
		$db->query($query_stok);
		
		$db->close();
		header("location:input_peminjaman.php?id=".$id);
	}
?>