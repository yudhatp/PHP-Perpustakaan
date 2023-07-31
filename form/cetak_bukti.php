<?php
	require_once '../setting/session.php';
	require('../lib/tcpdf/tcpdf.php');

	$usersession = $_SESSION['login_user'];
	$id = mysqli_real_escape_string($db,$_GET['id']);
	$print_token = md5(uniqid($usersession, true));

	$tgl_cetak = date("Y-m-d h:i:sa");
	// create new PDF document
	$pdf = new TCPDF('L', PDF_UNIT, 'A3', true, 'UTF-8', false);
			
	// set document information
	$pdf->SetTitle('Bukti Peminjaman');
	$pdf->SetSubject('Bukti Peminjaman');
			
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // add a page
	$pdf->AddPage();

	$pdf->SetFont('helvetica', 'B', 26);
	$pdf->Write(0, 'Bukti Peminjaman - Perpustakaan 5F', '', 0, 'C', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
			
	$pdf->SetFont('helvetica', '', 20);
	$pdf->Write(0, 'Tanggal cetak  : '.$tgl_cetak.'', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, 'Security Print  : '.$print_token.'', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
			
	$pdf->SetFont('helvetica', '', 18);
			
	$sql = "SELECT * FROM v_peminjaman WHERE id_t_peminjaman =".$id."";
			
	$result = mysqli_query($db,$sql);
	$tampil = mysqli_fetch_array($result,MYSQLI_ASSOC);
	
	$no_peminjaman = $tampil['no_peminjaman'];
	
	$isi = "No Peminjaman : ".$tampil['no_peminjaman']." <br>";
	$isi .= "Staff Penginput : ".$tampil['staff']." <br>";
	$isi .= "Tanggal Pinjam : ".$tampil['tgl_pinjam']." <br>";
	$isi .= "No Anggota : ".$tampil['no_anggota']." <br>";
	$isi .= "Nama Peminjam : ".$tampil['anggota']." <br>";
	$isi .= "Tanggal Kembali : ".$tampil['tgl_kembali']." <br><br>";
	
	$sql_detil = "SELECT * FROM v_detil_pinjam WHERE id_t_peminjaman =".$id;
	$result_det = mysqli_query($db,$sql_detil);
	
	$isi .= <<<EOD
				<table border="0.5">
				<tr>
					<th><b>No</b></th>
					<th><b>Nama Buku</b></th>
					<th><b>Penulis</b></th>
					<th><b>Qty</b></th>
					<th><b>Status</b></th>
					<th><b>Harga</b></th>
					<th><b>Denda</b></th>
					<th><b>Keterangan</b></th>
				</tr>
EOD;

	$no = 1;
			while ($tampil_detil = mysqli_fetch_array($result_det,MYSQLI_ASSOC)) {
				$isi.="	 	 	
					<tr>
						<td>".$no."</td>
						<td>".$tampil_detil['nama_buku']."</td>
						<td>".$tampil_detil['penulis']."</td>
						<td>".$tampil_detil['qty']."</td>
						<td>".$tampil_detil['kondisi']."</td>
						<td>".$tampil_detil['harga']."</td>
						<td>".$tampil_detil['denda']."</td>
						<td>".$tampil_detil['keterangan']."</td>";
					$isi .="</tr>";
					$no++;
				}
	$isi.="</table>";
	
	$pdf->writeHTML($isi, true, false, false, false, '');
					
	$namaPDF = 'Bukti Peminjaman_'.$no_peminjaman.'.pdf';
	$pdf->Output($namaPDF,'I');
?>