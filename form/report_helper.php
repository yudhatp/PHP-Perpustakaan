<?php
require_once __DIR__ . '/../setting/session.php';

function report_request_period(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode permintaan tidak diizinkan.');
    }

    verify_csrf($_POST['csrf_token'] ?? null);
    $start = request_string($_POST, 'tglawal');
    $end = request_string($_POST, 'tglakhir');

    if (!valid_date($start) || !valid_date($end)) {
        http_response_code(422);
        exit('Format tanggal harus YYYY-MM-DD.');
    }

    if ($start !== '' && $end !== '' && $start > $end) {
        http_response_code(422);
        exit('Tanggal awal tidak boleh melewati tanggal akhir.');
    }

    return [$start, $end];
}

function report_period_label(string $start, string $end): string
{
    if ($start === '' && $end === '') {
        return 'Semua periode';
    }
    if ($start !== '' && $end === '') {
        return $start;
    }
    if ($start === '' && $end !== '') {
        return 'Sampai ' . $end;
    }
    return $start . ' s.d. ' . $end;
}

function report_result(mysqli $db, string $baseSql, string $dateColumn, string $start, string $end): mysqli_result
{
    $sql = $baseSql;
    $types = '';
    $values = [];

    if ($start !== '' && $end !== '') {
        $sql .= " WHERE DATE($dateColumn) BETWEEN ? AND ?";
        $types = 'ss';
        $values = [$start, $end];
    } elseif ($start !== '') {
        $sql .= " WHERE DATE($dateColumn) = ?";
        $types = 's';
        $values = [$start];
    } elseif ($end !== '') {
        $sql .= " WHERE DATE($dateColumn) <= ?";
        $types = 's';
        $values = [$end];
    }

    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        http_response_code(500);
        exit('Gagal menyiapkan data laporan.');
    }

    if ($types === 'ss') {
        mysqli_stmt_bind_param($stmt, 'ss', $values[0], $values[1]);
    } elseif ($types === 's') {
        mysqli_stmt_bind_param($stmt, 's', $values[0]);
    }

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        http_response_code(500);
        exit('Gagal mengambil data laporan.');
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        http_response_code(500);
        exit('Driver MySQL tidak mendukung pengambilan hasil laporan.');
    }

    return $result;
}

function load_tcpdf(): void
{
    if (class_exists('TCPDF', false)) {
        return;
    }

    $previous = error_reporting();
    error_reporting($previous & ~E_DEPRECATED & ~E_WARNING);
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
    error_reporting($previous);
}

function report_pdf(string $title, string $period = '', string $orientation = 'L'): TCPDF
{
    // Endpoint PDF tidak boleh mencetak warning/deprecation ke stream dokumen.
    ini_set('display_errors', '0');
    error_reporting(error_reporting() & ~E_DEPRECATED);
    load_tcpdf();
    $pdf = new TCPDF($orientation, PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('PHP Perpustakaan');
    $pdf->SetAuthor('PHP Perpustakaan');
    $pdf->SetTitle($title);
    $pdf->SetSubject($title);
    $pdf->SetMargins(10, 12, 10);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(8);
    $pdf->SetAutoPageBreak(true, 12);
    $pdf->setPrintHeader(false);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 9, $title, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 9);
    if ($period !== '') {
        $pdf->Cell(0, 6, 'Periode: ' . $period, 0, 1, 'L');
    }
    $pdf->Cell(0, 6, 'Dicetak: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
    $pdf->Cell(0, 6, 'Token: ' . strtoupper(bin2hex(random_bytes(8))), 0, 1, 'L');
    $pdf->Ln(2);
    return $pdf;
}

function pdf_escape($value): string
{
    return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pdf_money($value): string
{
    return 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
}

function safe_pdf_filename(string $prefix): string
{
    $clean = preg_replace('/[^A-Za-z0-9_-]+/', '-', $prefix) ?: 'dokumen';
    return trim($clean, '-') . '_' . date('Ymd_His') . '.pdf';
}
