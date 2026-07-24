<?php
require_once __DIR__ . '/report_helper.php';
require_roles($db, [1, 2]);
[$awal, $akhir] = report_request_period();
$result = report_result(
    $db,
    'SELECT no_anggota, nama, tgl_daftar, tgl_lahir, jenis_kelamin, no_telp, alamat, keterangan, status FROM t_anggota',
    'tgl_daftar',
    $awal,
    $akhir
);
$pdf = report_pdf('Laporan Anggota - Perpustakaan 5F', report_period_label($awal, $akhir));
$html = '<table border="0.5" cellpadding="4"><thead><tr style="font-weight:bold;background-color:#eeeeee">'
    . '<th width="4%">No</th><th width="11%">No Anggota</th><th width="15%">Nama</th><th width="9%">Daftar</th>'
    . '<th width="9%">Lahir</th><th width="8%">JK</th><th width="11%">Telepon</th><th width="16%">Alamat</th><th width="10%">Ket.</th><th width="7%">Status</th>'
    . '</tr></thead><tbody>';
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr><td>' . $no++ . '</td><td>' . pdf_escape($row['no_anggota']) . '</td><td>' . pdf_escape($row['nama']) . '</td>'
        . '<td>' . pdf_escape($row['tgl_daftar']) . '</td><td>' . pdf_escape($row['tgl_lahir']) . '</td><td>' . pdf_escape($row['jenis_kelamin']) . '</td>'
        . '<td>' . pdf_escape($row['no_telp']) . '</td><td>' . pdf_escape($row['alamat']) . '</td><td>' . pdf_escape($row['keterangan']) . '</td><td>' . pdf_escape($row['status']) . '</td></tr>';
}
if ($no === 1) {
    $html .= '<tr><td colspan="10" align="center">Tidak ada data.</td></tr>';
}
$html .= '</tbody></table>';
$pdf->SetFont('helvetica', '', 7.5);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output(safe_pdf_filename('Laporan_Anggota'), 'I');
