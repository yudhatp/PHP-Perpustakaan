<?php
include("header.php");
$error = "";
$id_bk = mysqli_real_escape_string($db,$_GET['id']);

if ($id_bk != null){
	$judul = "Edit Buku";
	$sql_data = "SELECT * FROM t_buku WHERE id_t_buku = $id_bk ";
	//echo $sql_data;
	$result_data = mysqli_query($db,$sql_data);
	$tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);
}else{
	$judul = "Input Buku";
}
	
if(isset($_POST['btnsubmit'])) {
	$nama = $_POST['nama'];
	$penulis = $_POST['penulis'];
	$jb = $_POST['jenisnya'];
	$tahun = $_POST['tahun'];
	$penerbit = $_POST['penerbit'];
	$rak = $_POST['koderak'];
	$stok = $_POST['stok'];
	$harga = $_POST['harga'];
	$sinopsis = $_POST['sinopsis'];
	
	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	
	if ($id_bk == null){
		//mulai proses upload cover
		$target_dir = "../image/buku/";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		
		// Check if image file is a actual image or fake image
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				//echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				$error = "File bukan file gambar.";
				$uploadOk = 0;
			}
			
		// Check if file already exists
		if (file_exists($target_file)) {
			$error = "File sudah ada.";
			$uploadOk = 0;
		}
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			$error = "File terlalu besar, harus dibawah 500 kb.";
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
			$error = "File harus ber-ektensi JPG, JPEG atau PNG";
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			$error = "Gagal upload file cover.";
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
				$cover = basename( $_FILES["fileToUpload"]["name"]);
			
				//insert buku
				$query = "INSERT INTO t_buku(nama_buku,jenis,penulis,penerbit,tahun_terbit,harga,kode_rak,stok,sinopsis,gambar,create_date,create_by) 
						VALUES('$nama','$jb','$penulis', '$penerbit','$tahun',$harga,'$rak',$stok,'$sinopsis','$cover',curdate(),'$usersession')";
				
				if ($db->query($query)) {
					$db->close();
					header('location:data_buku.php');
				}
		} else {
			$error = "Terjadi kesalahan saat upload file.";
			}
		}
		//selesai upload cover
	}else{
		//update buku
			$cover = $tampil_data['gambar'];
			$query = "UPDATE t_buku SET nama_buku ='$nama', jenis = '$jb',penulis = '$penulis',penerbit = '$penerbit', tahun_terbit ='$tahun',harga = $harga,
					kode_rak = '$rak',stok = $stok,sinopsis = '$sinopsis',gambar = '$cover',update_date = curdate(),update_by = '$usersession'
					WHERE id_t_buku = $id_bk";
	
			if ($db->query($query)) {
				$db->close();
				//notif success
				echo "<script>
						alert('Data berhasil diubah');
						window.location.href='data_buku.php';
					</script>";

				//header('location:data_buku.php');
			}
	}
}

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
			<form class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
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
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Nama Buku</label>
					<div class="col-sm-8">
					<input type="text" maxlength="128" class="form-control" name="nama" value="<?php echo $tampil_data['nama_buku'];?>" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Penulis</label>
					<div class="col-sm-8">
					<input type="text" maxlength="64" class="form-control" name="penulis" value="<?php echo $tampil_data['penulis'];?>" required  />
					</div>
				</div>
				<?php
					if($tampil_data['jenis'] == "Umum") {
					 $um = "selected=selected";
					}else if($tampil_data['jenis'] == "Komputer") {
						$kom = "selected=selected";
						}else if($tampil_data['jenis'] == "Novel") {
							$nov = "selected=selected";
							}else if($tampil_data['jenis'] == "Pengembangan Diri") {
								$pd = "selected=selected";
								}else if($tampil_data['jenis'] == "Komik") {
									$kk = "selected=selected";
								}
				?>
				<div class="form-group">
					<label class="control-label col-sm-4">Jenis Buku</label>
					<div class="col-sm-8">
						<select name="jenisnya" class="form-control">
							<option <?php echo $um;?> >Umum</option>
                            <option <?php echo $kom;?> >Komputer</option>
                            <option <?php echo $nov;?> >Novel</option>
							<option <?php echo $pd;?> >Pengembangan Diri</option>
							<option <?php echo $kk;?> >Komik</option>
                        </select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tahun Terbit</label>
					<div class="col-sm-8">
					<input type="text" maxlength="4" class="form-control" name="tahun" value="<?php echo $tampil_data['tahun_terbit'];?>" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Penerbit</label>
					<div class="col-sm-8">
					<input type="text" maxlength="64" class="form-control" name="penerbit" value="<?php echo $tampil_data['penerbit'];?>" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Kode Rak</label>
					<div class="col-sm-8">
					<input type="text" maxlength="3" class="form-control" value="<?php echo $tampil_data['kode_rak'];?>" name="koderak" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Stok</label>
					<div class="col-sm-8">
					<input type="text" maxlength="11" class="form-control" value="<?php echo $tampil_data['stok'];?>" name="stok" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Harga</label>
					<div class="col-sm-8">
					<input type="text" maxlength="8" class="form-control" value="<?php echo $tampil_data['harga'];?>" name="harga" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Sinopsis</label>
					<div class="col-sm-8">
					<textarea name="sinopsis" class="form-control" rows="3" required><?php echo htmlspecialchars($tampil_data['sinopsis']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Cover Buku</label>
					<div class="col-sm-8">
					<input type="file" name="fileToUpload" id="fileToUpload">
					</div>
				</div>
				<div class="form-group" align="right">
				<div class="col-sm-4">
				<!-- sengaja dikosongin :D-->
				</div>
				<div class="col-sm-8">
					<a href="data_buku.php" class="btn btn-default">Batal</a>
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