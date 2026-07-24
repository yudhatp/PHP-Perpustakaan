<?php
require_once __DIR__ . '/report_helper.php';
require_roles($db, [1]);
[$awal, $akhir] = report_request_period();
$result = report_result(
    $db,
    'SELECT nama, alamat, status, create_date FROM t_staff',
    'create_date',
    $awal,
    $akhir
);
$pdf = report_pdf('Laporan Staff - Perpustakaan 5F', report_period_label($awal, $akhir), 'P');
$html = '<table border="0.5" cellpadding="5"><thead><tr style="font-weight:bold;background-color:#eeeeee">'
    . '<th width="7%">No</th><th width="25%">Nama Staff</th><th width="38%">Alamat</th><th width="15%">Status</th><th width="15%">Dibuat</th>'
    . '</tr></thead><tbody>';
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr><td>' . $no++ . '</td><td>' . pdf_escape($row['nama']) . '</td><td>' . pdf_escape($row['alamat']) . '</td>'
        . '<td>' . pdf_escape($row['status']) . '</td><td>' . pdf_escape($row['create_date']) . '</td></tr>';
}
if ($no === 1) {
    $html .= '<tr><td colspan="5" align="center">Tidak ada data.</td></tr>';
}
$html .= '</tbody></table>';
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output(safe_pdf_filename('Laporan_Staff'), 'I');
