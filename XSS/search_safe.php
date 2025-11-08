<?php
// search_safe.php — versi aman (prepared statements + escaping)
// Semua dalam satu file (tidak butuh config.php)

function pdo_connect() {
    $host = 'localhost';
    $db   = 'projectuts'; // ganti sesuai nama database kamu
    $user = 'root';       // user default XAMPP
    $pass = '';           // kosongkan jika tanpa password
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die('❌ Gagal koneksi DB: ' . $e->getMessage());
    }
}

$pdo = pdo_connect();

$q = trim((string)($_GET['q'] ?? ''));
$results = [];
$error = null;

if ($q !== '') {
    try {
        $sql = "SELECT c.id, u.username, c.comment, c.created_at
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE LOWER(c.comment) LIKE :q OR LOWER(u.username) LIKE :q
                ORDER BY c.created_at DESC
                LIMIT 200";
        $stmt = $pdo->prepare($sql);
        $like = '%' . mb_strtolower($q, 'UTF-8') . '%';
        $stmt->execute([':q' => $like]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan saat mencari. Coba lagi.';
    }
}

function safe_highlight(string $text, string $query): string {
    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if ($query === '') return nl2br($escaped);
    $safe_q = htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $pattern = '/' . preg_quote($safe_q, '/') . '/iu';
    $highlighted = preg_replace($pattern, '<mark>$0</mark>', $escaped);
    return nl2br($highlighted);
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Search Komentar — SAFE</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(120deg,#f8fafc 0%, #eef6ff 100%); min-height:100vh; }
    .search-card { max-width:980px; margin:42px auto; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.06); }
    .brand { width:56px; height:56px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:#fff; box-shadow:0 4px 12px rgba(16,24,40,0.06); font-weight:700; color:#0d6efd; }
    .comment { padding:12px; border-radius:8px; background:#fff; box-shadow:0 6px 18px rgba(15,23,42,0.03); margin-bottom:12px; }
    .meta { color:#6c757d; font-size:.9rem; }
    .note { font-size:.85rem; color:#6c757d; }
    .safe-badge { font-size:.75rem; background:#e6f7ff; color:#055160; padding:4px 8px; border-radius:999px; }
    mark { background:#ffe58f; padding:0 .15rem; border-radius:.15rem; }
    .count-badge { font-weight:600; color:#495057; }
  </style>
</head>
<body>
  <div class="card search-card">
    <div class="card-body p-4">
      <div class="d-flex align-items-center mb-3">
        <div class="brand me-3">SAFE</div>
        <div>
          <h4 class="mb-0">Search Komentar (SAFE)</h4>
          <div class="note">Versi aman: prepared statements + escaping. Cocok untuk perbandingan dengan versi vulnerable.</div>
        </div>
        <div class="ms-auto">
          <span class="safe-badge">SAFE</span>
            <a class="btn btn-outline-warning btn-sm" href="dashboard_XSS.php">Kembali</a>
        </div>
      </div>

      <form class="row g-2 align-items-center" method="get" action="">
        <div class="col-md-9">
          <input name="q" class="form-control" placeholder="Cari komentar atau username..." value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" autofocus>
        </div>
        <div class="col-md-3 d-grid">
          <button class="btn btn-success" type="submit">Search</button>
        </div>
      </form>

      <?php if ($q !== ''): ?>
        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="mb-0">Hasil untuk: <small class="text-muted"><?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?></small></h5>
            <div class="note">Menampilkan komentar yang mengandung kata pencarian atau username (case-insensitive).</div>
          </div>
          <div class="text-end">
            <span class="count-badge"><?php echo count($results); ?> hasil</span>
          </div>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (empty($results)): ?>
          <div class="alert alert-info">Tidak ada hasil untuk pencarian ini.</div>
        <?php else: ?>
          <div>
            <?php foreach ($results as $r): ?>
              <div class="comment">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong><?php echo htmlspecialchars($r['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?></strong>
                    <div class="meta"><?php echo htmlspecialchars($r['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <div>
                    <a href="#" class="btn btn-sm btn-outline-secondary">View</a>
                  </div>
                </div>

                <div class="mt-2 text-break">
                  <?php echo safe_highlight((string)$r['comment'], $q); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>

    <div class="card-footer text-muted small">
      Catatan: file ini <strong>AMAN</strong> — menggunakan prepared statements & escaping output.
      Bandingkan dengan <code>search_vul.php</code> untuk pembelajaran SQLi & XSS.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
