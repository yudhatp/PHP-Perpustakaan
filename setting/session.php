<?php
require_once __DIR__ . '/koneksi.php';

app_session_start();
$current_account = require_login($db, '../index.php');
$login_session = $current_account['username'];
