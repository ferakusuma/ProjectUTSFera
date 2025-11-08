<?php
session_start();

// ✅ Koneksi database (gunakan koneksi langsung, tanpa pdo_connect)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=projectuts;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Gagal koneksi DB: " . $e->getMessage());
}

// fallback user (otomatis login untuk demo)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['id' => 1, 'username' => 'FERA'];
}
$user = $_SESSION['user'];

// ambil id post dari URL
$post_id = (int)($_GET['id'] ?? 1);

// pastikan token CSRF ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$msg = '';
$err = '';

// === Handle tambah komentar ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'post_comment') {
    if (!$user) {
        $err = 'Anda harus login untuk mengirim komentar.';
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $err = 'Request tidak valid (CSRF).';
    } else {
        $comment = trim($_POST['comment'] ?? '');
        if ($comment === '') {
            $err = 'Komentar tidak boleh kosong.';
        } elseif (mb_strlen($comment) > 2000) {
            $err = 'Komentar terlalu panjang (maks 2000 karakter).';
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment, created_at) VALUES (:uid, :pid, :c, NOW())");
            $stmt->execute([
                ':uid' => $user['id'],
                ':pid' => $post_id,
                ':c'   => $comment
            ]);
            header("Location: post_vul.php?id=$post_id");
            exit;
        }
    }
}

// === Handle hapus komentar ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
    if (!$user) {
        $err = 'Anda harus login untuk menghapus komentar.';
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $err = 'Request tidak valid (CSRF).';
    } else {
        $del_id = (int)($_POST['delete_comment_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = :cid");
        $stmt->execute([':cid' => $del_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $err = 'Komentar tidak ditemukan.';
        } elseif ((int)$row['user_id'] !== (int)$user['id']) {
            $err = 'Anda tidak berhak menghapus komentar ini.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :cid");
            $stmt->execute([':cid' => $del_id]);
            header("Location: post_vul.php?id=$post_id");
            exit;
        }
    }
}

// === Ambil data post ===
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// === Ambil komentar ===
$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = :pid 
    ORDER BY c.created_at DESC
");
$stmt->execute([':pid' => $post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($post['title'] ?? 'Post'); ?> — LAB (VULNERABLE)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(120deg,#f8fafc 0%, #eef6ff 100%); min-height:100vh; font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    .container-main { max-width:900px; margin:36px auto; }
    .post-card { border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.06); overflow:hidden; background:#fff; }
    .post-body { padding:28px; }
    .post-meta { color:#6c757d; font-size:.95rem; }
    .comment-card { margin-top:18px; padding:18px; background:#fff; border-radius:10px; box-shadow:0 6px 20px rgba(15,23,42,0.04); }
    .vuln-badge { font-size:.75rem; background:#ffe9e9; color:#b02a37; padding:6px 10px; border-radius:999px; }
    .note { font-size:.85rem; color:#6c757d; }
    .raw-comment { white-space: pre-wrap; }
  </style>
</head>
<body>
  <div class="container container-main">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff;box-shadow:0 4px 12px rgba(16,24,40,0.06);font-weight:700;color:#d63384">LAB</div>
        <div>
          <h4 class="mb-0">Contoh Artikel</h4>
          <div class="note">Halaman ini <strong>intentionally vulnerable</strong> untuk demo Stored XSS (komentar ditampilkan raw).</div>
        </div>
      </div>
      <div class="text-end"><span class="vuln-badge">VULNERABLE</span></div>
    </div>

    <div class="card post-card">
      <div class="post-body">
        <div class="d-flex justify-content-between mb-2">
          <div>
            <small class="text-muted">Signed in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong></small>
          </div>
          <div>
            <a class="btn btn-outline-warning btn-sm" href="dashboard_XSS.php">Kembali</a>
          </div>
        </div>

        <h2 class="mb-1"><?php echo htmlspecialchars($post['title'] ?? '—'); ?></h2>
        <div class="post-meta mb-3">
          <?php echo htmlspecialchars($post['created_at'] ?? ''); ?> 
          <?php if (!empty($post['author'])): ?> &nbsp;oleh <?php echo htmlspecialchars($post['author']); ?> <?php endif; ?>
        </div>

        <div class="mb-4"><?php echo nl2br(htmlspecialchars($post['body'] ?? '')); ?></div>

        <hr>
        <h4>Tulis Komentar</h4>

        <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
        <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

        <form method="post" class="mb-3" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
          <input type="hidden" name="action" value="post_comment">
          <div class="mb-3">
            <textarea name="comment" rows="5" class="form-control" placeholder="Tulis komentar Anda (maks 2000 karakter)"></textarea>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="note">HTML dalam komentar akan dieksekusi — ini sengaja untuk demo XSS.</div>
            <div><button type="submit" class="btn btn-primary">Kirim Komentar</button></div>
          </div>
        </form>

        <hr>
        <h4>Komentar</h4>
        <?php if (empty($comments)): ?>
          <p class="text-muted">Belum ada komentar.</p>
        <?php else: ?>
          <?php foreach ($comments as $c): ?>
            <div class="comment-card mb-3">
              <div class="d-flex justify-content-between">
                <div>
                  <strong><?php echo htmlspecialchars($c['username'] ?? 'Guest'); ?></strong>
                  <div class="text-muted"><?php echo htmlspecialchars($c['created_at']); ?></div>
                </div>
                <?php if ((int)$c['user_id'] === (int)$user['id']): ?>
                  <form method="post" onsubmit="return confirm('Hapus komentar ini?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="delete_comment">
                    <input type="hidden" name="delete_comment_id" value="<?php echo (int)$c['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                  </form>
                <?php endif; ?>
              </div>
              <div class="mt-2 raw-comment">
                <?php echo $c['comment']; ?> <!-- sengaja tidak di-escape (demo XSS) -->
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="card-footer text-muted small">
        <strong>PERINGATAN:</strong> Halaman ini intentionally vulnerable (Stored XSS). Gunakan hanya untuk latihan di lingkungan lokal.
      </div>
    </div>
  </div>
</body>
</html>
