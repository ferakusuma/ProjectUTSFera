<?php
// dashboard.php - versi dengan pilihan sebagai cards (mirip gambar contoh)
declare(strict_types=1);

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Update activity timestamp (opsional)
$_SESSION['last_activity'] = time();

$displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Pengunjung';
$displayNameEsc = htmlspecialchars($displayName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$demoModeEsc = htmlspecialchars((string)($_SESSION['demo_mode'] ?? 'unknown'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$lastLogin = date('d M Y, H:i');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    :root{
      --primary: #1e3a8a;
      --primary-light: #2563eb;
      --primary-dark: #1e40af;
      --secondary: #3b82f6;
      --accent: #60a5fa;
      --bg-gradient-1: #f8fafc;
      --bg-gradient-2: #f1f5f9;
      --card-bg: #ffffff;
      --glass-bg: rgba(255,255,255,0.95);
      --glass-border: rgba(30,58,138,0.1);
      --text-dark: #1e293b;
      --text-light: #1e293b;
      --text-muted: #64748b;
      --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
      --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
      --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
      --border-radius: 12px;
    }
    
    *{
      box-sizing:border-box;
      margin:0;
      padding:0;
    }
    
    body{
      font-family:"Inter",sans-serif;
      background: #f1f5f9;
      min-height:100vh;
      color:var(--text-light);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
      position:relative;
      overflow-x:hidden;
    }

    .wrap{
      width:100%;
      max-width:1200px;
      display:grid;
      grid-template-columns: 1fr 380px;
      gap:24px;
      align-items:start;
      position:relative;
      z-index:1;
    }

    /* Left main panel: cards grid */
    .main-panel{
      background: #ffffff;
      padding:32px;
      border-radius:var(--border-radius);
      border:1px solid #e2e8f0;
      box-shadow: var(--shadow-md);
    }

    .header{
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:16px;
      margin-bottom:28px;
      padding-bottom:20px;
      border-bottom:1px solid #e2e8f0;
    }
    
    .header h1{
      font-size:2rem;
      color:var(--primary);
      font-weight:700;
      margin-bottom:6px;
      letter-spacing:-0.5px;
    }
    
    .meta { 
      color:#64748b; 
      font-size:0.95rem;
      font-weight:400;
    }
    
    .meta strong{
      color:var(--primary);
      font-weight:600;
    }

    .header-right{
      text-align:right;
      font-size:0.9rem;
      color:#64748b;
    }

    /* grid cards */
    .cards {
      display:grid;
      grid-template-columns: repeat(2, 1fr);
      gap:20px;
      margin-top:16px;
    }

    .card {
      background: var(--card-bg);
      color: var(--text-dark);
      border-radius:14px;
      padding:24px;
      box-shadow: var(--shadow-md);
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      min-height:140px;
      text-decoration:none;
      transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor:pointer;
      position:relative;
      overflow:hidden;
    }

    .card::before{
      content:'';
      position:absolute;
      top:0;
      left:0;
      right:0;
      height:4px;
      background: linear-gradient(90deg, var(--primary-light), var(--secondary));
      opacity:0;
      transition: opacity .3s ease;
    }

    .card:hover{ 
      transform: translateY(-8px) scale(1.02); 
      box-shadow: 0 16px 48px rgba(30,58,138,0.15);
    }

    .card:hover::before{
      opacity:1;
    }

    .card-top {
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:12px;
      margin-bottom:16px;
    }

    .card .label {
      font-size:0.75rem;
      font-weight:700;
      text-transform:uppercase;
      color:var(--text-muted);
      letter-spacing:1px;
    }

    .card .value {
      font-size:1.15rem;
      font-weight:600;
      color:var(--text-dark);
      display:flex;
      flex-direction:column;
      gap:6px;
    }

    .card .value .main-text{
      font-size:1.1rem;
      font-weight:600;
    }

    .card .value .sub-text{
      font-size:0.85rem;
      color:var(--text-muted);
      font-weight:400;
    }

    .icon-wrap {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color:#fff;
      width:48px;
      height:48px;
      border-radius:12px;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:22px;
      box-shadow: 0 4px 16px rgba(30,58,138,0.25);
      flex-shrink:0;
    }

    /* Variant colors for different cards */
    .card.safe .icon-wrap{
      background: linear-gradient(135deg, #10b981, #059669);
      box-shadow: 0 4px 16px rgba(16,185,129,0.3);
    }

    .card.vulnerable .icon-wrap{
      background: linear-gradient(135deg, #ef4444, #dc2626);
      box-shadow: 0 4px 16px rgba(239,68,68,0.3);
    }

    .card.vulnerable .label{
      color:#dc2626;
    }

    /* Right column */
    .side-panel{
      display:flex;
      flex-direction:column;
      gap:20px;
    }
    
    .panel {
      background: #ffffff;
      padding:24px;
      border-radius:var(--border-radius);
      color:var(--text-dark);
      border:1px solid #e2e8f0;
      box-shadow: var(--shadow-md);
    }

    .panel h3{
      font-size:1.15rem;
      margin-bottom:16px;
      font-weight:600;
      color:var(--primary);
    }
    
    .links { 
      display:flex; 
      flex-direction:column; 
      gap:10px;
    }
    
    .links a {
      padding:12px 16px;
      background: #f1f5f9;
      color:var(--primary);
      border-radius:10px;
      text-decoration:none;
      font-weight:500;
      font-size:0.95rem;
      transition: all .25s ease;
      border:1px solid transparent;
    }
    
    .links a:hover { 
      background: #e0e7ff; 
      transform: translateX(6px);
      border-color: var(--primary-light);
    }

    .info-text{
      color:#64748b;
      font-size:0.9rem;
      line-height:1.6;
    }

    .info-text strong{
      color:var(--primary);
      font-weight:600;
    }

    footer.small { 
      font-size:0.85rem; 
      color:#64748b; 
      margin-top:16px;
      padding-top:16px;
      border-top:1px solid #e2e8f0;
      line-height:1.5;
    }

    /* responsive */
    @media (max-width:1024px){
      .wrap{ 
        grid-template-columns: 1fr;
        max-width:800px;
      }
      .cards{ 
        grid-template-columns: repeat(2, 1fr); 
      }
    }
    
    @media (max-width:640px){
      body{
        padding:16px;
      }
      .main-panel{
        padding:20px;
      }
      .panel{
        padding:20px;
      }
      .header{
        flex-direction:column;
        align-items:flex-start;
      }
      .header h1{
        font-size:1.5rem;
      }
      .header-right{
        text-align:left;
      }
      .cards{ 
        grid-template-columns: 1fr;
        gap:16px;
      }
      .card{
        min-height:120px;
        padding:20px;
      }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="main-panel">
      <div class="header">
        <div>
          <h1>Dashboard</h1>
          <div class="meta">Selamat datang, <strong><?= $displayNameEsc ?></strong> • Mode: <?= $demoModeEsc ?></div>
        </div>
        <div class="header-right">
          <div class="meta">Login terakhir:<br><strong><?= htmlspecialchars($lastLogin, ENT_QUOTES) ?></strong></div>
        </div>
      </div>

      <div class="cards" role="list">
        <!-- Card 1: Login Safe -->
        <a class="card safe" href="login_safe.php" role="listitem" aria-label="Login Safe">
          <div class="card-top">
            <div class="label">Login Safe</div>
            <div class="icon-wrap">🔒</div>
          </div>
          <div class="value">
            <span class="main-text">Login Aman</span>
            <span class="sub-text">Autentikasi dengan prepared statements</span>
          </div>
        </a>

        <!-- Card 2: Login Vulnerable -->
        <a class="card vulnerable" href="login_vul.php" role="listitem" aria-label="Login Vulnerable">
          <div class="card-top">
            <div class="label">Login Vulnerable</div>
            <div class="icon-wrap">⚠️</div>
          </div>
          <div class="value">
            <span class="main-text">Demo SQL Injection</span>
            <span class="sub-text">Contoh kerentanan untuk pembelajaran</span>
          </div>
        </a>

        <!-- Card 3: Create User Safe -->
        <a class="card safe" href="create_user_safe.php" role="listitem" aria-label="Create User Safe">
          <div class="card-top">
            <div class="label">Create User Safe</div>
            <div class="icon-wrap">✅</div>
          </div>
          <div class="value">
            <span class="main-text">Buat User Aman</span>
            <span class="sub-text">Registrasi dengan validasi keamanan</span>
          </div>
        </a>

        <!-- Card 4: Create User Vulnerable -->
        <a class="card vulnerable" href="create_user_vul.php" role="listitem" aria-label="Create User Vulnerable">
          <div class="card-top">
            <div class="label">Create User Vul</div>
            <div class="icon-wrap">🧪</div>
          </div>
          <div class="value">
            <span class="main-text">Buat User (Vulnerable)</span>
            <span class="sub-text">Eksperimen dengan kode tidak aman</span>
          </div>
        </a>
      </div>
    </div>

    <div class="side-panel">
      <div class="panel">
        <h3>Kontrol Cepat</h3>
        <div class="links">
          <a href="profile.php">👤 Lihat Profil</a>
          <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
          <a href="../beranda.html">🏠 Kembali ke Beranda</a>
        </div>
        <footer class="small">⚠️ Gunakan halaman "Vulnerable" hanya di lingkungan terisolasi untuk pembelajaran keamanan.</footer>
      </div>

      <div class="panel">
        <h3>Informasi Keamanan</h3>
        <p class="info-text">
          <strong>Safe:</strong> Menggunakan PDO dengan prepared statements untuk mencegah SQL Injection.<br><br>
          <strong>Vulnerable:</strong> Contoh kode dengan kerentanan untuk tujuan edukasi saja.
        </p>
      </div>
    </div>
  </div>
</body>
</html>