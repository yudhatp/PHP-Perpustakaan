<?php
require_once __DIR__ . '/report_helper.php';
require_roles($db, [1, 2]);
[$awal, $akhir] = report_request_period();
$baseSql = "SELECT p.no_peminjaman, COALESCE(s.nama, 'Admin') AS staff, p.tgl_pinjam, p.tgl_kembali, a.no_anggota, a.nama AS anggota, COALESCE(SUM(d.qty), 0) AS jumlah_buku, COALESCE(p.total_denda, 0) AS total_denda
    FROM t_peminjaman p
    JOIN t_anggota a ON a.id_t_anggota = p.id_t_anggota
    LEFT JOIN t_staff s ON s.id_t_staff = p.id_t_staff
    LEFT JOIN t_detil_pinjam d ON d.id_t_peminjaman = p.id_t_peminjaman";
$conditions = [];
$params = [];
$types = '';
if ($awal !== '' && $akhir !== '') {
    $conditions[] = 'p.tgl_pinjam BETWEEN ? AND ?';
    $types = 'ss';
    $params = [$awal, $akhir];
} elseif ($awal !== '') {
    $conditions[] = 'p.tgl_pinjam = ?';
    $types = 's';
    $params = [$awal];
} elseif ($akhir !== '') {
    $conditions[] = 'p.tgl_pinjam <= ?';
    $types = 's';
    $params = [$akhir];
}
$sql = $baseSql . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '')
    . ' GROUP BY p.id_t_peminjaman, p.no_peminjaman, s.nama, p.tgl_pinjam, p.tgl_kembali, a.no_anggota, a.nama, p.total_denda ORDER BY p.tgl_pinjam DESC, p.id_t_peminjaman DESC';
$stmt = mysqli_prepare($db, $sql);
if (!$stmt) {
    http_response_code(500);
    exit('Gagal menyiapkan laporan peminjaman.');
}
if ($types === 'ss') {
    mysqli_stmt_bind_param($stmt, 'ss', $params[0], $params[1]);
} elseif ($types === 's') {
    mysqli_stmt_bind_param($stmt, 's', $params[0]);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pdf = report_pdf('Laporan Peminjaman - Perpustakaan 5F', report_period_label($awal, $akhir));
$html = '<table border="0.5" cellpadding="4"><thead><tr style="font-weight:bold;background-color:#eeeeee">'
    . '<th width="5%">No</th><th width="14%">No Peminjaman</th><th width="12%">Staff</th><th width="10%">Pinjam</th><th width="10%">Kembali</th>'
    . '<th width="12%">No Anggota</th><th width="17%">Anggota</th><th width="8%">Buku</th><th width="12%">Denda</th>'
    . '</tr></thead><tbody>';
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr><td>' . $no++ . '</td><td>' . pdf_escape($row['no_peminjaman']) . '</td><td>' . pdf_escape($row['staff']) . '</td>'
        . '<td>' . pdf_escape($row['tgl_pinjam']) . '</td><td>' . pdf_escape($row['tgl_kembali']) . '</td><td>' . pdf_escape($row['no_anggota']) . '</td>'
        . '<td>' . pdf_escape($row['anggota']) . '</td><td>' . (int) $row['jumlah_buku'] . '</td><td>' . pdf_escape(pdf_money($row['total_denda'])) . '</td></tr>';
}
if ($no === 1) {
    $html .= '<tr><td colspan="9" align="center">Tidak ada data.</td></tr>';
}
$html .= '</tbody></table>';
$pdf->SetFont('helvetica', '', 8);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output(safe_pdf_filename('Laporan_Peminjaman'), 'I');
