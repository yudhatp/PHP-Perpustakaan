<?php
	require_once '../setting/koneksi.php';
	require_once '../setting/session.php';
	require('../lib/tcpdf/tcpdf.php');

	$usersession = $_SESSION['login_user'];
	$id = mysqli_real_escape_string($db,$_GET['id']);
	$print_token = md5(uniqid($usersession, true));

	$tgl_cetak = date("Y-m-d h:i:sa");
	// create new PDF document
	$pdf = new TCPDF('L', PDF_UNIT, 'A3', true, 'UTF-8', false);
			
	// set document information
	$pdf->SetTitle('Cetak Kartu Anggota');
	$pdf->SetSubject('Cetak Kartu Anggota');
			
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // add a page
	$pdf->AddPage();

	$pdf->SetFont('helvetica', 'B', 26);
	$pdf->Write(0, 'Kartu Anggota - Perpustakaan 5F', '', 0, 'C', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
			
	$pdf->SetFont('helvetica', '', 20);
	$pdf->Write(0, 'Tanggal cetak  : '.$tgl_cetak.'', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, 'Security Print  : '.$print_token.'', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
			
	$pdf->SetFont('helvetica', '', 18);
			
	$sql = "SELECT B.no_anggota, B.nama, C.nama_role, A.create_date AS tgl_register, 
						IFNULL(A.update_date,'-') AS last_change_pass,
						IFNULL(B.update_date,'-') AS last_change_profile
						FROM t_account A 
						JOIN t_anggota B ON A.id_t_account = B.id_t_account 
						JOIN P_ROLE C ON A.id_p_role = C.id_p_role
						WHERE B.id_t_anggota =".$id."";
			
	$result = mysqli_query($db,$sql);
	$tampil = mysqli_fetch_array($result,MYSQLI_ASSOC);
	
	$isi = "No Anggota : ".$tampil['no_anggota']." <br>";
	$isi .= "Nama Anggota : ".$tampil['nama']." <br>";
	$isi .= "Tanggal Daftar : ".$tampil['tgl_register']." <br><br>";
	
	$pdf->writeHTML($isi, true, false, false, false, '');
					
	$namaPDF = 'Kartu Anggota_'.$usersession.'.pdf';
	$pdf->Output($namaPDF,'I');
?>