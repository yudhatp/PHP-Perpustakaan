<?php
require_once __DIR__ . '/report_helper.php';
$account = require_roles($db, [1, 2, 3]);
$anggotaId = request_int($_GET, 'id');
if ($anggotaId < 1) {
    http_response_code(400);
    exit('ID anggota tidak valid.');
}

$stmt = mysqli_prepare($db, 'SELECT a.id_t_anggota, a.id_t_account, a.no_anggota, a.nama, a.tgl_daftar, a.tgl_lahir, a.jenis_kelamin,
        a.no_telp, a.alamat, a.status, r.nama_role
    FROM t_anggota a
    JOIN t_account ac ON ac.id_t_account = a.id_t_account
    JOIN p_role r ON r.id_p_role = ac.id_p_role
    WHERE a.id_t_anggota = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $anggotaId);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$member) {
    http_response_code(404);
    exit('Data anggota tidak ditemukan.');
}
if ((int) $account['id_p_role'] === 3 && (int) $member['id_t_account'] !== (int) $account['id_t_account']) {
    http_response_code(403);
    exit('Anda tidak memiliki akses ke kartu anggota ini.');
}

$pdf = report_pdf('Kartu Anggota - Perpustakaan 5F', '', 'P');
$pdf->SetFont('helvetica', '', 11);
$status = strtolower((string) $member['status']) === 'aktif' ? 'AKTIF' : strtoupper((string) $member['status']);
$html = '<div style="border:1px solid #333;padding:18px">'
    . '<h2 style="text-align:center">KARTU ANGGOTA</h2>'
    . '<p style="text-align:center;font-size:15px"><b>' . pdf_escape($member['no_anggota']) . '</b></p>'
    . '<table cellpadding="5">'
    . '<tr><td width="32%"><b>Nama</b></td><td width="68%">: ' . pdf_escape($member['nama']) . '</td></tr>'
    . '<tr><td><b>Tanggal Daftar</b></td><td>: ' . pdf_escape($member['tgl_daftar']) . '</td></tr>'
    . '<tr><td><b>Tanggal Lahir</b></td><td>: ' . pdf_escape($member['tgl_lahir']) . '</td></tr>'
    . '<tr><td><b>Jenis Kelamin</b></td><td>: ' . pdf_escape($member['jenis_kelamin']) . '</td></tr>'
    . '<tr><td><b>No. Telepon</b></td><td>: ' . pdf_escape($member['no_telp']) . '</td></tr>'
    . '<tr><td><b>Alamat</b></td><td>: ' . pdf_escape($member['alamat']) . '</td></tr>'
    . '<tr><td><b>Status</b></td><td>: <b>' . pdf_escape($status) . '</b></td></tr>'
    . '</table></div>';
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output(safe_pdf_filename('Kartu_' . $member['no_anggota']), 'I');
