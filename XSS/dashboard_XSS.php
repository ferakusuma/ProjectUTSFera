<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XSS Dashboard | Keamanan Data</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #0077b6;
      --accent: #00b4d8;
      --success: #198754;
      --danger: #dc3545;
      --light-bg: #f8fafc;
      --text: #0a2540;
    }

    body {
      font-family: "Poppins", sans-serif;
      background-color: var(--light-bg);
      color: var(--text);
      margin: 0;
      padding: 0;
    }

    header {
      background: linear-gradient(90deg, var(--primary), var(--accent));
      color: white;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      font-size: 1.3rem;
    }

    .brand span {
      background-color: white;
      color: var(--primary);
      font-weight: 600;
      border-radius: 12px;
      padding: 5px 12px;
    }

    .logout-btn {
      background-color: rgba(255,255,255,0.2);
      color: #fff;
      padding: 8px 14px;
      border-radius: 10px;
      border: none;
      text-decoration: none;
      transition: 0.2s;
    }

    .logout-btn:hover {
      background-color: rgba(255,255,255,0.35);
    }

    .container {
      max-width: 1200px;
      margin: 50px auto;
      padding: 0 20px;
    }

    .header-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .header-info h1 {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }

    .card-box {
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
      padding: 25px;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .card-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .card-box h5 {
      font-weight: 700;
      color: var(--text);
      margin-bottom: 10px;
    }

    .card-box p {
      color: #555;
      font-size: .95rem;
      margin-bottom: 20px;
    }

    .btn-danger, .btn-success {
      border: none;
      border-radius: 10px;
      font-weight: 600;
      padding: 8px 16px;
    }

    .tools-info {
      background-color: #eef7ff;
      border-radius: 15px;
      padding: 25px;
      margin-top: 40px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .tools-info .btn {
      margin: 5px;
      border-radius: 10px;
    }

    footer {
      text-align: center;
      font-size: 0.9rem;
      color: #555;
      padding: 20px;
      background-color: #e9f3fb;
      margin-top: 40px;
      border-top: 1px solid #cde0f5;
    }
  </style>
</head>
<body>

  <header>
    <div class="brand">
      <span>LAB</span> Dashboard Demo Keamanan Web
    </div>
    <div>
      <small>Signed in as <strong>FERA</strong></small>
      <a href="../beranda.html" class="logout-btn ms-3">Kembali ke Beranda</a>
    </div>
  </header>

  <div class="container">
    <div class="header-info">
      <h1>Cross-Site Scripting (XSS) Dashboard</h1>
      <div>
        <span class="me-3">Posts: <strong>2</strong></span>
        <span>Comments: <strong>6</strong></span>
      </div>
    </div>

    <div class="card-grid">
      <div class="card-box">
        <h5>post_vul.php</h5>
        <p>Halaman posting komentar raw (Stored XSS). Gunakan untuk demonstrasi celah XSS.</p>
        <a href="post_vul.php" class="btn btn-danger w-100">Buka (VULNERABLE)</a>
      </div>

      <div class="card-box">
        <h5>search_vul.php</h5>
        <p>Pencarian komentar yang rentan terhadap SQL Injection (concatenated query).</p>
        <a href="search_vul.php" class="btn btn-danger w-100">Buka (VULNERABLE)</a>
      </div>

      <div class="card-box">
        <h5>post_safe.php</h5>
        <p>Versi aman: komentar di-escape, CSRF & owner-only delete. Gunakan untuk perbandingan.</p>
        <a href="post_safe.php" class="btn btn-success w-100">Buka (SAFE)</a>
      </div>

      <div class="card-box">
        <h5>search_safe.php</h5>
        <p>Pencarian aman: prepared statements dan hasil di-escape. Bandingkan dengan versi rentan.</p>
        <a href="search_safe.php" class="btn btn-success w-100">Buka (SAFE)</a>
      </div>
    </div>

    <div class="tools-info mt-4">
      <div>
        <h5>Tools & Info</h5>
        <p>Gunakan tombol di bawah untuk membuka halaman demo. Jalankan hanya di lingkungan lab.</p>
        <a href="post_vul.php" class="btn btn-outline-danger">Open vulnerable post (sample)</a>
        <a href="post_safe.php" class="btn btn-outline-success">Open safe post (sample)</a>
      </div>
      <div class="text-end">
        <h5>Users</h5>
        <h2>3</h2>
      </div>
    </div>
  </div>

  <footer>
    © 2025 Keamanan Data — Modul Cross-Site Scripting (XSS)
  </footer>

</body>
</html>
