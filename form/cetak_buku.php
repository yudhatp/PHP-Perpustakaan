<?php
require_once __DIR__ . '/report_helper.php';
require_roles($db, [1, 2]);
[$awal, $akhir] = report_request_period();
$result = report_result(
    $db,
    'SELECT nama_buku, jenis, penulis, penerbit, tahun_terbit, harga, kode_rak, stok FROM t_buku',
    'create_date',
    $awal,
    $akhir
);
$pdf = report_pdf('Laporan Buku - Perpustakaan 5F', report_period_label($awal, $akhir));
$html = '<table border="0.5" cellpadding="4"><thead><tr style="font-weight:bold;background-color:#eeeeee">'
    . '<th width="4%">No</th><th width="22%">Nama Buku</th><th width="11%">Jenis</th><th width="15%">Penulis</th>'
    . '<th width="15%">Penerbit</th><th width="8%">Tahun</th><th width="11%">Harga</th><th width="7%">Rak</th><th width="7%">Stok</th>'
    . '</tr></thead><tbody>';
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr><td>' . $no++ . '</td><td>' . pdf_escape($row['nama_buku']) . '</td><td>' . pdf_escape($row['jenis']) . '</td>'
        . '<td>' . pdf_escape($row['penulis']) . '</td><td>' . pdf_escape($row['penerbit']) . '</td><td>' . pdf_escape($row['tahun_terbit']) . '</td>'
        . '<td>' . pdf_escape(pdf_money($row['harga'])) . '</td><td>' . pdf_escape($row['kode_rak']) . '</td><td>' . (int) $row['stok'] . '</td></tr>';
}
if ($no === 1) {
    $html .= '<tr><td colspan="9" align="center">Tidak ada data.</td></tr>';
}
$html .= '</tbody></table>';
$pdf->SetFont('helvetica', '', 8);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output(safe_pdf_filename('Laporan_Buku'), 'I');
