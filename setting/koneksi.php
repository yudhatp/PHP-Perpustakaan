<?php
/**
 * Konfigurasi database dan helper dasar aplikasi.
 *
 * Environment variables yang didukung:
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, APP_DEBUG, APP_TIMEZONE
 */

if (!ob_get_level()) {
    ob_start();
}

function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

load_env_file(__DIR__ . '/../.env');

$debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Jakarta');

if (!defined('DB_SERVER')) {
    define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', (int) (getenv('DB_PORT') ?: 3306));
    define('DB_USERNAME', getenv('DB_USER') ?: 'root');
    define('DB_PASSWORD', getenv('DB_PASS') ?: '');
    define('DB_DATABASE', getenv('DB_NAME') ?: 'yudhatpm_perpustakaan');
}

mysqli_report(MYSQLI_REPORT_OFF);
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

if (!$db) {
    http_response_code(500);
    $message = $debug ? mysqli_connect_error() : 'Koneksi database gagal. Periksa konfigurasi database.';
    exit(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
}

mysqli_set_charset($db, 'utf8mb4');

function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function redirect_to(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function request_int(array $source, string $key, int $default = 0): int
{
    if (!isset($source[$key]) || is_array($source[$key])) {
        return $default;
    }

    $value = filter_var($source[$key], FILTER_VALIDATE_INT);
    return $value === false ? $default : (int) $value;
}

function request_string(array $source, string $key, string $default = ''): string
{
    if (!isset($source[$key]) || is_array($source[$key])) {
        return $default;
    }

    return trim((string) $source[$key]);
}


function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function starts_with(string $value, string $prefix): bool
{
    return $prefix === '' || strpos($value, $prefix) === 0;
}

function valid_date(string $value): bool
{
    if ($value === '') {
        return true;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function csrf_token(): string
{
    app_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): void
{
    app_session_start();
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(419);
        exit('Permintaan tidak valid atau sesi telah berakhir. Silakan kembali dan coba lagi.');
    }
}

function password_matches(string $plainPassword, string $storedHash): bool
{
    if ($storedHash === '') {
        return false;
    }

    $info = password_get_info($storedHash);
    if (!empty($info['algo'])) {
        return password_verify($plainPassword, $storedHash);
    }

    // Kompatibilitas akun lama yang masih menggunakan MD5.
    return hash_equals(strtolower($storedHash), md5($plainPassword));
}

function current_account(mysqli $db): ?array
{
    app_session_start();
    if (empty($_SESSION['login_user'])) {
        return null;
    }

    $username = (string) $_SESSION['login_user'];
    $stmt = mysqli_prepare($db, 'SELECT id_t_account, id_p_role, username FROM t_account WHERE username = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $account = mysqli_fetch_assoc($result) ?: null;
    mysqli_stmt_close($stmt);

    return $account;
}

function require_login(mysqli $db, string $redirect = '../index.php'): array
{
    $account = current_account($db);
    if (!$account) {
        app_session_start();
        $_SESSION = [];
        redirect_to($redirect);
    }

    return $account;
}

function require_roles(mysqli $db, array $roles, string $redirect = 'dashboard.php'): array
{
    $account = require_login($db);
    if (!in_array((int) $account['id_p_role'], $roles, true)) {
        redirect_to($redirect);
    }

    return $account;
}
