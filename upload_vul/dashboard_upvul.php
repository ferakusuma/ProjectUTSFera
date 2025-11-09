<?php
// dashboard_upvul.php - perbaikan include dan pengecekan login
// Cari config.php di folder yang sama, kalau tidak ada coba parent folder
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
    // Jika tidak ketemu, tampilkan pesan singkat (jangan tampilkan info sensitif)
    http_response_code(500);
    echo '<h1>Server error</h1>';
    echo '<p>File konfigurasi <code>config.php</code> tidak ditemukan. Pastikan file tersebut ada di folder project.</p>';
    echo '<p>Lokasi yang dicari: ' . implode(', ', $config_candidates) . '</p>';
    exit;
}

require_once $config_path;

// Jika fungsi require_login tersedia, gunakan. Jika tidak, fallback sederhana.
if (function_exists('require_login')) {
    require_login();
} else {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html'); // atau login.php sesuai projectmu
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Demo App</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text {
            font-size: 28px;
            color: #2d3748;
            font-weight: 600;
        }

        .username {
            color: #3b82f6;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            padding: 15px 25px;
            text-decoration: none;
            color: #2d3748;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            position: relative;
        }

        .nav-link:hover {
            background: #f7fafc;
            border-bottom-color: #3b82f6;
        }

        .nav-link.footer-link {
            margin-left: auto;
            color: #3b82f6;
        }

        .nav-link.footer-link:hover {
            background: #eff6ff;
            border-bottom-color: #3b82f6;
        }

        /* Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge.danger {
            background: #fed7d7;
            color: #c53030;
        }

        .badge.success {
            background: #c6f6d5;
            color: #22543d;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .dashboard-title {
            color: white;
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%);
        }

        .card.vulnerable::before {
            background: linear-gradient(90deg, #fc8181 0%, #e53e3e 100%);
        }

        .card.safe::before {
            background: linear-gradient(90deg, #68d391 0%, #38a169 100%);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .card-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 22px;
            color: #2d3748;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .card-description {
            color: #718096;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .card-link {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .card-link:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .card.vulnerable .card-link {
            background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%);
        }

        .card.safe .card-link {
            background: linear-gradient(135deg, #68d391 0%, #38a169 100%);
        }

        /* Info Section */
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-left: 5px solid #3b82f6;
        }

        .info-section h3 {
            color: #2d3748;
            font-size: 22px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-section p {
            color: #4a5568;
            line-height: 1.8;
            font-size: 15px;
        }

        .info-section strong {
            color: #e53e3e;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .welcome-text {
                font-size: 22px;
            }

            .navbar-content {
                flex-direction: column;
                gap: 0;
            }

            .nav-link {
                border-bottom: 1px solid #e2e8f0;
                border-left: 3px solid transparent;
            }

            .nav-link:hover {
                border-bottom: 1px solid #e2e8f0;
                border-left-color: #3b82f6;
            }

            .nav-link.footer-link {
                margin-left: 0;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-title {
                font-size: 26px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card, .info-section {
            animation: fadeIn 0.6s ease-out;
        }

        .card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .info-section {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1 class="welcome-text">
                Selamat datang, <span class="username"><?= htmlspecialchars($_SESSION['username'] ?? 'Pengguna') ?></span>! 👋
            </h1>
            <div class="user-info">
                <div class="avatar">👤</div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="artikel_vul.php" class="nav-link vulnerable">
                📝 Artikel (Versi RENTAN)
                <span class="badge danger">VULNERABLE</span>
            </a>
            <a href="artikel_safe.php" class="nav-link safe">
                ✅ Artikel (Versi AMAN)
                <span class="badge success">SECURE</span>
            </a>
            <a href="../beranda.html" class="nav-link footer-link">
                🏠 Kembali ke Beranda
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h2 class="dashboard-title">📊 Menu Utama</h2>
        <div class="cards-grid">
            <div class="card vulnerable">
                <div class="card-icon">⚠️</div>
                <h3 class="card-title">Artikel Versi Rentan</h3>
                <p class="card-description">
                    Contoh implementasi dengan kerentanan XSS (Cross-Site Scripting).
                </p>
                <a href="artikel_vul.php" class="card-link">Lihat Demo →</a>
            </div>
            <div class="card safe">
                <div class="card-icon">🛡️</div>
                <h3 class="card-title">Artikel Versi Aman</h3>
                <p class="card-description">
                    Implementasi yang aman dengan sanitasi input yang tepat.
                </p>
                <a href="artikel_safe.php" class="card-link">Lihat Demo →</a>
            </div>
            <div class="card">
                <div class="card-icon">📚</div>
                <h3 class="card-title">Dokumentasi</h3>
                <p class="card-description">
                    Pelajari lebih lanjut tentang keamanan web, XSS, SQL Injection, dan teknik pengamanan.
                </p>
                <a href="#" class="card-link">Pelajari →</a>
            </div>
        </div>

        <div class="info-section">
            <h3>ℹ️ Tentang Dashboard Ini</h3>
            <p>
                Dashboard ini dibuat untuk mendemonstrasikan perbedaan antara aplikasi web yang rentan dan yang aman.
            </p>
            <p style="margin-top: 10px;">
                <strong>Peringatan:</strong> Jangan gunakan versi rentan di aplikasi produksi!
            </p>
        </div>
    </div>
</body>
</html>