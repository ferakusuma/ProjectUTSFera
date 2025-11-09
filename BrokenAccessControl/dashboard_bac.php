<?php
// dashboard_bac.php — Dashboard Broken Access Control Demo

// --- 1️⃣ Cari dan muat file konfigurasi ---
$config_candidates = [
    __DIR__ . '/config.php',
    __DIR__ . '/../config.php',
];

$config_path = null;
foreach ($config_candidates as $c) {
    if (file_exists($c)) {
        $config_path = $c;
        break;
    }
}

if ($config_path === null) {
    http_response_code(500);
    echo "<h1>Server Error</h1>";
    echo "<p>File konfigurasi <code>config.php</code> tidak ditemukan.</p>";
    echo "<p>Dicari di: " . implode(', ', $config_candidates) . "</p>";
    exit;
}

require_once $config_path;

// --- 2️⃣ Pastikan session sudah dimulai ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 3️⃣ Cek login (gunakan require_login jika tersedia) ---
if (function_exists('require_login')) {
    require_login();
} else {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

// --- 4️⃣ Ambil data user ---
$user = $_SESSION['user'] ?? ['username' => 'Pengguna'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Security Demo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            min-height: 100vh; padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 20px; animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header h1 {
            color: #333; font-size: 2em; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .user-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white; padding: 8px 20px; border-radius: 25px; font-weight: 600;
            box-shadow: 0 4px 10px rgba(59,130,246,0.3);
        }
        .logout-btn {
            background: #ff4757; color: white; padding: 12px 30px;
            border-radius: 25px; text-decoration: none; font-weight: 600;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: #ff3838; transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,71,87,0.4);
        }
        .cards-container {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px; margin-bottom: 30px;
        }
        .card {
            background: white; border-radius: 15px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease; position: relative; overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px;
        }
        .card.vulnerable::before { background: linear-gradient(90deg, #ff4757, #ff6348); }
        .card.safe::before { background: linear-gradient(90deg, #1dd1a1, #10ac84); }
        .card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .card-icon {
            width: 60px; height: 60px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; margin-bottom: 20px;
        }
        .vulnerable .card-icon { background: linear-gradient(135deg, #ff4757, #ff6348); color: white; }
        .safe .card-icon { background: linear-gradient(135deg, #1dd1a1, #10ac84); color: white; }
        .card h3 { color: #333; margin-bottom: 15px; font-size: 1.5em; }
        .card p { color: #666; line-height: 1.6; margin-bottom: 25px; }
        .card-btn {
            display: inline-block; padding: 12px 30px; border-radius: 25px;
            text-decoration: none; font-weight: 600; transition: all 0.3s ease;
        }
        .vulnerable .card-btn {
            background: linear-gradient(135deg, #ff4757, #ff6348); color: white;
        }
        .vulnerable .card-btn:hover {
            background: linear-gradient(135deg, #ff6348, #ff4757);
            transform: translateX(5px); box-shadow: 0 4px 15px rgba(255,71,87,0.4);
        }
        .safe .card-btn {
            background: linear-gradient(135deg, #1dd1a1, #10ac84); color: white;
        }
        .safe .card-btn:hover {
            background: linear-gradient(135deg, #10ac84, #1dd1a1);
            transform: translateX(5px); box-shadow: 0 4px 15px rgba(16,172,132,0.4);
        }
        .badge {
            display: inline-block; padding: 5px 15px; border-radius: 20px;
            font-size: 0.85em; font-weight: 600; margin-bottom: 15px;
        }
        .badge.danger { background: #ffe0e0; color: #ff4757; }
        .badge.success { background: #d4f4e7; color: #10ac84; }
        .info-section {
            background: white; border-radius: 15px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px; border-left: 5px solid #3b82f6;
            animation: fadeInUp 0.7s ease-out;
        }
        .info-section h3 {
            color: #2d3748; font-size: 22px; margin-bottom: 15px;
            display: flex; align-items: center; gap: 10px;
        }
        .info-section p { color: #4a5568; line-height: 1.8; font-size: 15px; }
        .back-link {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; background: white; color: #2d3748;
            text-decoration: none; border-radius: 25px; font-weight: 600;
            transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .back-link:hover {
            transform: translateX(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            background: #f7fafc;
        }
        @media (max-width: 768px) {
            .cards-container { grid-template-columns: 1fr; }
            .logout-btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Security Dashboard 
                <span class="user-badge"><?= htmlspecialchars($user['username']) ?></span>
            </h1>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>

        <div class="cards-container">
            <div class="card vulnerable">
                <div class="card-icon">⚠️</div>
                <span class="badge danger">VULNERABLE</span>
                <h3>Area Rentan</h3>
                <p>Demonstrasi Broken Access Control (IDOR) tanpa validasi kepemilikan data.</p>
                <a href="vuln/list.php" class="card-btn">Masuk Area Vuln →</a>
            </div>

            <div class="card safe">
                <div class="card-icon">✅</div>
                <span class="badge success">SAFE</span>
                <h3>Area Aman</h3>
                <p>Implementasi keamanan dengan UUID, Token CSRF, dan validasi kepemilikan data.</p>
                <a href="safe/list.php" class="card-btn">Masuk Area Safe →</a>
            </div>
        </div>

        <div class="info-section">
            <h3>ℹ️ Tentang Dashboard Ini</h3>
            <p>Dashboard ini mendemonstrasikan perbedaan antara aplikasi rentan Broken Access Control dan versi aman.</p>
            <p style="margin-top:10px"><strong>Peringatan:</strong> Jangan gunakan kode area rentan di aplikasi produksi!</p>
        </div>

        <a href="../beranda.html" class="back-link">← Kembali ke Beranda</a>
    </div>
</body>
</html>
