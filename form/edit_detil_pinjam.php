<?php
	require_once '../setting/koneksi.php';
	require_once '../setting/session.php';
	$id_dp = mysqli_real_escape_string($db,$_GET['id']);
	$id_pj = mysqli_real_escape_string($db,$_GET['id_pj']);
	
	$usersession = $_SESSION['login_user'];

if ($id_dp != null){
	
	$sql_data = "SELECT * FROM v_detil_pinjam WHERE id_t_detil_pinjam = $id_dp ";
	//echo $sql_data;
	$result_data = mysqli_query($db,$sql_data);
	$tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);

	if($tampil_data['kondisi'] == "Rusak") {
		$rusak = "selected=selected";
		}else if($tampil_data['kondisi'] == "Bagus") {
				$bagus = "selected=selected";
			}else if($tampil_data['kondisi'] == "Hilang") {
				$hilang = "selected=selected";
			}
}else{
	header('location:data_peminjaman.php');
}

if(isset($_POST['btnsubmit'])) {
	//$tgl_kembali = $_POST['tglkembali'];
	$kondisi = $_POST['kondisi'];
	$keterangan = $_POST['keterangan'];
	
	if($kondisi == "Bagus"){
		$denda = 0;
	}else if($kondisi == "Rusak"){
			$denda = $tampil_data['harga']*30/100;
		}else if($kondisi == "Hilang"){
				$denda = $tampil_data['harga'];
	}
	
	if ($id_dp == null){
		header('location:data_peminjaman.php');
	}else{

		$query = "UPDATE t_detil_pinjam SET tgl_kembali = curdate(), kondisi = '$kondisi',keterangan = '$keterangan',
				  denda = $denda, update_date = curdate(),update_by = '$usersession' 
				  WHERE id_t_detil_pinjam= $id_dp";
		$db->query($query);
		
		$query_pj = "UPDATE t_peminjaman SET tgl_kembali = curdate(), update_date = curdate(),update_by = '$usersession' 
				  WHERE id_t_peminjaman = $id_pj";
		$db->query($query_pj);
		
		
		//update stok buku
		$qty = $tampil_data['qty'];
		$idbuku = $tampil_data['id_t_buku'];
		
		if($kondisi == "Bagus" || $kondisi == "Rusak"){
			$query_stok = "UPDATE t_buku SET stok = stok+$qty WHERE id_t_buku = $idbuku";
		}else if($kondisi == "Hilang"){
			$query_stok = "UPDATE t_buku SET stok = stok-$qty WHERE id_t_buku = $idbuku";
		}
		//echo $query_stok;
		$db->query($query_stok);
		//echo $query;
	
		$db->close();
		header('location:input_peminjaman.php?id='.$id_pj);
	}
}
include("header.php");	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Edit Detil Peminjaman</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-6">
			<form class="form-horizontal" method="post">
				<div class="form-group">
					<label class="control-label col-sm-4">Nama Buku</label>
					<div class="col-sm-8">
					<label class="control-label col-sm-4"><?php echo $tampil_data['nama_buku'];?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Penulis</label>
					<div class="col-sm-8">
					<label class="control-label col-sm-4"><?php echo $tampil_data['penulis'];?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Harga</label>
					<div class="col-sm-8">
					<label class="control-label col-sm-4"><?php echo $tampil_data['harga'];?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Qty</label>
					<div class="col-sm-8">
					<label class="control-label col-sm-4"><?php echo $tampil_data['qty'];?></label>
					</div>
				</div>
				<!--<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Kembali</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="tglkembali" 
							value="<?php //echo $tampil_data['tgl_kembali'];?>" id="tglkembali" required  />
					</div>
				</div> -->
				<div class="form-group">
					<label class="control-label col-sm-4">Kondisi</label>
					<div class="col-sm-8">
						<select name="kondisi" class="form-control">
                            <option <?php echo $rusak;?> >Rusak</option>
                            <option <?php echo $bagus;?> >Bagus</option>
							<option <?php echo $hilang;?> >Hilang</option>
                        </select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Keterangan</label>
					<div class="col-sm-8">
					<textarea name="keterangan" class="form-control" rows="3"><?php echo htmlspecialchars($tampil_data['keterangan']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Denda</label>
					<div class="col-sm-8">
					<label class="control-label col-sm-4"><?php echo $tampil_data['denda'];?></label>
					</div>
				</div>
				<div class="form-group" align="right">
				<div class="col-sm-4">
				<!-- sengaja dikosongin :D-->
				</div>
				<div class="col-sm-8">
					<a href="input_peminjaman.php?id=<?php echo $id_pj;?>" class="btn btn-default">Kembali</a>
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
<script type="text/javascript">
		$(document).ready(function(e) {
		$("#tglkembali").datepicker({dateFormat: "yy-mm-dd"});
	});
</script>