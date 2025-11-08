<?php
// login_vul.php  (VERSI RENTAN — DEMO)
session_start();

$dsn = 'mysql:host=127.0.0.1;dbname=praktek_sqli;charset=utf8mb4';
$dbUser = 'root';
$dbPass = '';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // --- pola rentan: concatenation langsung dengan input user ---
        $sql = "SELECT id, username, password, full_name FROM users_vul
                WHERE username = '" . $username . "' AND password = '" . $password . "'";
        $stmt = $pdo->query($sql);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['demo_mode'] = 'vul';
            header('Location: dashboard.php');
            exit;
        } else {
            $message = 'Username atau password salah.';
        }
    } catch (PDOException $e) {
        $message = 'Terjadi kesalahan server.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login Vulnerable — Demo SQL Injection</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    :root{
      --primary: #1e3a8a;
      --primary-light: #2563eb;
      --danger: #4a26dcff;
      --danger-light: #4d44efff;
      --warning: #f59e0b;
      --warning-light: #fbbf24;
      --bg-page: #f1f5f9;
      --card-bg: #ffffff;
      --text-dark: #1e293b;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
      --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: "Inter", sans-serif;
      background: var(--bg-page);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      color: var(--text-dark);
    }
    
    .container {
      width: 100%;
      max-width: 480px;
    }
    
    .card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 40px;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border);
    }
    
    .header {
      text-align: center;
      margin-bottom: 32px;
    }
    
    .icon-badge {
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      margin: 0 auto 16px;
      box-shadow: 0 4px 16px rgba(30,58,138,0.25);
    }
    
    .header h1 {
      font-size: 1.75rem;
      color: var(--text-dark);
      font-weight: 700;
      margin-bottom: 8px;
    }
    
    .header .subtitle {
      color: var(--text-muted);
      font-size: 0.95rem;
    }
    
    .alert {
      background: #dbeafe;
      border: 1px solid #93c5fd;
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--primary);
      font-size: 0.9rem;
    }
    
    .alert-icon {
      font-size: 20px;
      flex-shrink: 0;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      display: block;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
      font-size: 0.95rem;
    }
    
    .form-input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 0.95rem;
      font-family: inherit;
      transition: all 0.2s ease;
      background: #ffffff;
    }
    
    .form-input:focus {
      outline: none;
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .btn {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    .btn-danger {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(30,58,138,0.2);
    }
    
    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(30,58,138,0.3);
    }
    
    .btn-danger:active {
      transform: translateY(0);
    }
    
    .warning-box {
      background: #dbeafe;
      border: 1px solid #93c5fd;
      border-left: 4px solid var(--primary-light);
      border-radius: 8px;
      padding: 16px;
      margin-top: 24px;
    }
    
    .warning-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 8px;
      font-size: 0.95rem;
    }
    
    .warning-text {
      color: var(--text-dark);
      font-size: 0.85rem;
      line-height: 1.5;
    }
    
    .footer {
      margin-top: 24px;
      text-align: center;
    }
    
    .footer-links {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    
    .footer-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      padding: 8px;
      border-radius: 6px;
      transition: all 0.2s ease;
    }
    
    .footer-link:hover {
      background: #f1f5f9;
      color: var(--primary-light);
    }
    
    .divider {
      height: 1px;
      background: var(--border);
      margin: 24px 0;
    }
    
    @media (max-width: 640px) {
      .card {
        padding: 28px 24px;
      }
      
      .header h1 {
        font-size: 1.5rem;
      }
      
      body {
        padding: 16px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="header">
        <div class="icon-badge">⚠️</div>
        <h1>Login Vulnerable</h1>
        <p class="subtitle">Demo SQL Injection untuk Pembelajaran</p>
      </div>
      
      <?php if ($message): ?>
      <div class="alert">
        <span class="alert-icon">❌</span>
        <span><?= htmlspecialchars($message) ?></span>
      </div>
      <?php endif; ?>
      
      <form method="post" action="">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input 
            type="text" 
            name="username" 
            class="form-input" 
            placeholder="Masukkan username"
            required
          >
        </div>
        
        <div class="form-group">
          <label class="form-label">Password</label>
          <input 
            type="password" 
            name="password" 
            class="form-input" 
            placeholder="Masukkan password"
            required
          >
        </div>
        
        <button type="submit" class="btn btn-danger">
          🔓 Login (Vulnerable Mode)
        </button>
      </form>
      
      <div class="warning-box">
        <div class="warning-title">
          <span>⚡</span>
          <span>Peringatan Keamanan</span>
        </div>
        <p class="warning-text">
          Halaman ini <strong>sengaja dibuat rentan</strong> untuk demonstrasi SQL Injection. 
          Kode menggunakan concatenation langsung tanpa prepared statements dan password disimpan dalam plaintext. 
          <strong>Hanya gunakan di lingkungan lokal yang terisolasi!</strong>
        </p>
      </div>
      
      <div class="divider"></div>
      
      <div class="footer">
        <div class="footer-links">
          <a href="login_safe.php" class="footer-link">🔒 Ke Login Aman (Safe)</a>
          <a href="dashboard_sqli.php" class="footer-link">🏠 Kembali ke Dashboard</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>