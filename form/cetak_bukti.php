<?php
require_once __DIR__ . '/report_helper.php';
$account = require_roles($db, [1, 2, 3]);
$peminjamanId = request_int($_GET, 'id');
if ($peminjamanId < 1) {
    http_response_code(400);
    exit('ID peminjaman tidak valid.');
}

$stmt = mysqli_prepare($db, "SELECT p.id_t_peminjaman, p.no_peminjaman, COALESCE(s.nama, 'Admin') AS staff, p.tgl_pinjam, p.tgl_kembali,
        p.total_denda, a.no_anggota, a.nama AS anggota, a.id_t_account
    FROM t_peminjaman p
    JOIN t_anggota a ON a.id_t_anggota = p.id_t_anggota
    LEFT JOIN t_staff s ON s.id_t_staff = p.id_t_staff
    WHERE p.id_t_peminjaman = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $peminjamanId);
mysqli_stmt_execute($stmt);
$loan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$loan) {
    http_response_code(404);
    exit('Data peminjaman tidak ditemukan.');
}
if ((int) $account['id_p_role'] === 3 && (int) $loan['id_t_account'] !== (int) $account['id_t_account']) {
    http_response_code(403);
    exit('Anda tidak memiliki akses ke bukti peminjaman ini.');
}

$detailStmt = mysqli_prepare($db, 'SELECT b.nama_buku, b.penulis, b.harga, d.qty, d.kondisi, d.denda, d.keterangan, d.tgl_kembali
    FROM t_detil_pinjam d JOIN t_buku b ON b.id_t_buku = d.id_t_buku
    WHERE d.id_t_peminjaman = ? ORDER BY d.id_t_detil_pinjam');
mysqli_stmt_bind_param($detailStmt, 'i', $peminjamanId);
mysqli_stmt_execute($detailStmt);
$details = mysqli_stmt_get_result($detailStmt);

$pdf = report_pdf('Bukti Peminjaman - Perpustakaan 5F', '', 'P');
$pdf->SetFont('helvetica', '', 10);
$summary = '<table cellpadding="3">'
    . '<tr><td width="30%"><b>No Peminjaman</b></td><td width="70%">: ' . pdf_escape($loan['no_peminjaman']) . '</td></tr>'
    . '<tr><td><b>Staff Penginput</b></td><td>: ' . pdf_escape($loan['staff']) . '</td></tr>'
    . '<tr><td><b>Tanggal Pinjam</b></td><td>: ' . pdf_escape($loan['tgl_pinjam']) . '</td></tr>'
    . '<tr><td><b>Anggota</b></td><td>: ' . pdf_escape($loan['no_anggota'] . ' - ' . $loan['anggota']) . '</td></tr>'
    . '<tr><td><b>Tanggal Selesai</b></td><td>: ' . pdf_escape($loan['tgl_kembali'] ?: 'Belum selesai') . '</td></tr>'
    . '<tr><td><b>Total Denda</b></td><td>: ' . pdf_escape(pdf_money($loan['total_denda'])) . '</td></tr>'
    . '</table><br>';
$pdf->writeHTML($summary, true, false, false, false, '');

$html = '<table border="0.5" cellpadding="4"><thead><tr style="font-weight:bold;background-color:#eeeeee">'
    . '<th width="6%">No</th><th width="25%">Buku</th><th width="17%">Penulis</th><th width="7%">Qty</th><th width="12%">Kondisi</th>'
    . '<th width="13%">Harga</th><th width="12%">Denda</th><th width="8%">Ket.</th></tr></thead><tbody>';
$no = 1;
while ($row = mysqli_fetch_assoc($details)) {
    $html .= '<tr><td>' . $no++ . '</td><td>' . pdf_escape($row['nama_buku']) . '</td><td>' . pdf_escape($row['penulis']) . '</td>'
        . '<td>' . (int) $row['qty'] . '</td><td>' . pdf_escape($row['kondisi'] ?: 'Dipinjam') . '</td><td>' . pdf_escape(pdf_money($row['harga'])) . '</td>'
        . '<td>' . pdf_escape(pdf_money($row['denda'])) . '</td><td>' . pdf_escape($row['keterangan']) . '</td></tr>';
}
if ($no === 1) {
    $html .= '<tr><td colspan="8" align="center">Belum ada buku pada transaksi ini.</td></tr>';
}
$html .= '</tbody></table>';
$pdf->SetFont('helvetica', '', 8.5);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output(safe_pdf_filename('Bukti_' . $loan['no_peminjaman']), 'I');
