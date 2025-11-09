<?php
// config.php - contoh minimal untuk demo
// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Contoh fungsi require_login yang dipanggil oleh dashboard
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html'); // ubah sesuai file login di proyekmu
        exit;
    }
}

// Contoh set username default (jika belum ada, misal untuk demo)
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'DemoUser';
}

// (Opsional) koneksi database:
// $db = new PDO('mysql:host=localhost;dbname=nama_db;charset=utf8', 'db_user', 'db_pass');
// error handling, dsb.
