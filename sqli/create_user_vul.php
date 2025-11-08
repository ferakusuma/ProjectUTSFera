<?php
// create_user_vul.php
// DEMO ONLY: VULNERABLE user creation form — gunakan hanya di lab lokal/VM

$dsn = 'mysql:host=127.0.0.1;dbname=praktek_sqli;charset=utf8mb4';
$dbUser = 'root';
$dbPass = ''; // sesuaikan jika perlu

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $fullname = $_POST['full_name'] ?? '';

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // VULNERABLE: menyimpan password plaintext and concatenation query
        $sql = "INSERT INTO users_vul (username, password, full_name) VALUES ('" 
                . $username . "', '" . $password . "', '" . $fullname . "')";
        $pdo->exec($sql);

        $message = "User rentan berhasil dibuat: " . htmlspecialchars($username);
        $messageType = 'success';

    } catch (PDOException $e) {
        $message = "Terjadi kesalahan server (demo).";
        $messageType = 'error';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create User Vulnerable — Demo</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    :root{
      --primary: #1e3a8a;
      --primary-light: #2563eb;
      --success: #10b981;
      --bg-page: #f1f5f9;
      --card-bg: #ffffff;
      --text-dark: #1e293b;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
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
      max-width: 520px;
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
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.9rem;
    }
    
    .alert.success {
      background: #d1fae5;
      border: 1px solid #6ee7b7;
      color: #065f46;
    }
    
    .alert.error {
      background: #dbeafe;
      border: 1px solid #93c5fd;
      color: var(--primary);
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
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(30,58,138,0.2);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(30,58,138,0.3);
    }
    
    .btn-primary:active {
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
        <div class="icon-badge">🧪</div>
        <h1>Create User Vulnerable</h1>
        <p class="subtitle">Demo Pembuatan User Tidak Aman</p>
      </div>
      
      <?php if ($message): ?>
      <div class="alert <?= $messageType ?>">
        <span class="alert-icon"><?= $messageType === 'success' ? '✅' : '❌' ?></span>
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
            type="text" 
            name="password" 
            class="form-input" 
            placeholder="Masukkan password (plaintext)"
            required
          >
        </div>
        
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input 
            type="text" 
            name="full_name" 
            class="form-input" 
            placeholder="Masukkan nama lengkap (opsional)"
          >
        </div>
        
        <button type="submit" class="btn btn-primary">
          🧪 Buat User (Vulnerable)
        </button>
      </form>
      
      <div class="warning-box">
        <div class="warning-title">
          <span>⚠️</span>
          <span>Peringatan Keamanan</span>
        </div>
        <p class="warning-text">
          Form ini <strong>sengaja dibuat rentan</strong> untuk demonstrasi. 
          Menggunakan concatenation query langsung dan menyimpan password dalam plaintext. 
          <strong>Jangan gunakan di lingkungan produksi!</strong>
        </p>
      </div>
      
      <div class="divider"></div>
      
      <div class="footer">
        <div class="footer-links">
          <a href="create_user_safe.php" class="footer-link">✅ Ke Create User Safe</a>
          <a href="dashboard_sqli.php" class="footer-link">🏠 Kembali ke Dashboard</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>