<?php
/**
 * Pemeriksaan lingkungan untuk PHP 8.3.
 * Jalankan dari terminal: php tools/php83_check.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya boleh dijalankan melalui CLI.');
}

$checks = [];
$checks[] = [
    'label' => 'Versi PHP minimal 8.3',
    'ok' => PHP_VERSION_ID >= 80300,
    'detail' => PHP_VERSION,
];

foreach (['mysqli', 'fileinfo', 'session'] as $extension) {
    $checks[] = [
        'label' => 'Ekstensi ' . $extension,
        'ok' => extension_loaded($extension),
        'detail' => extension_loaded($extension) ? 'aktif' : 'tidak aktif',
    ];
}

$checks[] = [
    'label' => 'Driver mysqlnd / mysqli_stmt_get_result',
    'ok' => function_exists('mysqli_stmt_get_result'),
    'detail' => function_exists('mysqli_stmt_get_result') ? 'tersedia' : 'tidak tersedia',
];

$checks[] = [
    'label' => 'Fungsi password_hash',
    'ok' => function_exists('password_hash'),
    'detail' => function_exists('password_hash') ? 'tersedia' : 'tidak tersedia',
];

$checks[] = [
    'label' => 'Fungsi random_bytes',
    'ok' => function_exists('random_bytes'),
    'detail' => function_exists('random_bytes') ? 'tersedia' : 'tidak tersedia',
];

$coverDirectory = dirname(__DIR__) . '/image/buku';
$checks[] = [
    'label' => 'Folder image/buku dapat ditulis',
    'ok' => is_dir($coverDirectory) && is_writable($coverDirectory),
    'detail' => $coverDirectory,
];

$failed = 0;
echo "PHP Perpustakaan - Pemeriksaan PHP 8.3\n";
echo str_repeat('=', 48) . "\n";

foreach ($checks as $check) {
    $status = $check['ok'] ? '[OK]  ' : '[FAIL]';
    echo sprintf("%-7s %-39s %s\n", $status, $check['label'], $check['detail']);
    if (!$check['ok']) {
        $failed++;
    }
}

echo str_repeat('-', 48) . "\n";
if ($failed > 0) {
    echo $failed . " pemeriksaan gagal. Perbaiki konfigurasi sebelum menjalankan aplikasi.\n";
    exit(1);
}

echo "Semua pemeriksaan utama berhasil.\n";
exit(0);
