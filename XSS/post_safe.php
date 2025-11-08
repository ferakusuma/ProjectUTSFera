<?php
// post_safe.php (SAFE - prepared statements, escaped output)
// Perbaikan: pastikan session, include auth_simple.php jika ada,
// fallback pdo_connect() & current_user() jika tidak tersedia.

// mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// coba include helper/auth jika ada (biasanya mendefinisikan pdo_connect() dan current_user())
if (file_exists(__DIR__ . '/auth_simple.php')) {
    require_once __DIR__ . '/auth_simple.php';
}

// fallback pdo_connect() jika belum ada
if (!function_exists('pdo_connect')) {
    function pdo_connect() {
        $host = 'localhost';
        $db   = 'projectuts'; // ganti jika diperlukan
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            die('Gagal koneksi DB: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }
}

// fallback current_user() sederhana jika tidak tersedia di auth_simple.php
if (!function_exists('current_user')) {
    function current_user() {
        // untuk testing/dev: jika ingin otomatis login, uncomment baris berikut
        // if (!isset($_SESSION['user'])) $_SESSION['user'] = ['id' => 1, 'username' => 'FERA'];
        return $_SESSION['user'] ?? null;
    }
}

// koneksi DB
$pdo = pdo_connect();

// inisialisasi variabel
$post_id = (int)($_GET['id'] ?? 1);
$user = current_user();
$msg = '';
$err = '';

// pastikan CSRF token ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

// Handle new comment (POST) - SAFE: prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'post_comment') {
    if (!$user) {
        $err = 'Anda harus login untuk mengirim komentar.';
    } elseif (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $err = 'Request tidak valid (CSRF).';
    } else {
        $comment = trim((string)($_POST['comment'] ?? ''));
        if ($comment === '') {
            $err = 'Komentar tidak boleh kosong.';
        } elseif (mb_strlen($comment, 'UTF-8') > 2000) {
            $err = 'Komentar terlalu panjang (maks 2000 karakter).';
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment, created_at) VALUES (:uid, :pid, :c, NOW())");
            $stmt->execute([
                ':uid' => $user['id'],
                ':pid' => $post_id,
                ':c'   => $comment
            ]);
            // pesan sukses opsional
            $msg = 'Komentar berhasil dikirim.';
            // redirect supaya form tidak doble submit
            header("Location: post_safe.php?id=" . urlencode($post_id));
            exit;
        }
    }
}

// Handle delete comment (POST) - owner-only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
    if (!$user) {
        $err = 'Anda harus login untuk menghapus komentar.';
    } elseif (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $err = 'Request tidak valid (CSRF).';
    } else {
        $del_id = (int)($_POST['delete_comment_id'] ?? 0);
        if ($del_id <= 0) {
            $err = 'ID komentar tidak valid.';
        } else {
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
                header("Location: post_safe.php?id=" . urlencode($post_id));
                exit;
            }
        }
    }
}

// Ambil post (SAFE)
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Ambil komentar (SAFE)
$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = :pid 
    ORDER BY c.created_at DESC
");
$stmt->execute([':pid' => $post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// helper untuk escape + nl2br
function esc_nl(string $s): string {
    return nl2br(htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'));
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($post['title'] ?? 'Post', ENT_QUOTES, 'UTF-8'); ?> — SAFE</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(120deg,#f8fafc 0%, #eef6ff 100%); min-height:100vh; font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    .container-main { max-width:900px; margin:36px auto; }
    .post-card { border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.06); overflow:hidden; background:#fff; }
    .post-body { padding:28px; }
    .post-meta { color:#6c757d; font-size:.95rem; }
    .comment-card { margin-top:18px; padding:18px; background:#fff; border-radius:10px; box-shadow:0 6px 20px rgba(15,23,42,0.04); }
    .safe-badge { font-size:.75rem; background:#e6f7ff; color:#055160; padding:6px 10px; border-radius:999px; }
    .note { font-size:.85rem; color:#6c757d; }
    .escaped-comment { white-space: pre-wrap; }
  </style>
</head>
<body>
  <div class="container container-main">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff;box-shadow:0 4px 12px rgba(16,24,40,0.06);font-weight:700;color:#0d6efd">SAFE</div>
        <div>
          <h4 class="mb-0">Contoh Artikel</h4>
          <div class="note">Halaman ini <strong>SAFE</strong> — komentar akan di-escape sehingga tidak mengeksekusi HTML/JS.</div>
        </div>
      </div>

      <div class="text-end">
        <span class="safe-badge">SAFE</span>
      </div>
    </div>

    <div class="card post-card">
      <div class="post-body">
        <div class="d-flex justify-content-between mb-2">
          <div>
            <?php if ($user): ?>
              <small class="text-muted">Signed in as: <strong><?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></small>
            <?php else: ?>
              <a class="btn btn-primary btn-sm" href="login.php">Login</a>
            <?php endif; ?>
          </div>
          <?php if ($user): ?>
            <div>
              <a class="btn btn-outline-warning btn-sm" href="dashboard_XSS.php">Kembali</a>
            </div>
          <?php endif; ?>
        </div>

        <h2 class="mb-1"><?php echo htmlspecialchars($post['title'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="post-meta mb-3">
          <?php echo htmlspecialchars($post['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?> 
          <?php if (!empty($post['author'])): ?> &nbsp;oleh <?php echo htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8'); ?> <?php endif; ?>
        </div>

        <div class="mb-4"><?php echo nl2br(htmlspecialchars($post['body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>

        <hr>

        <h4>Tulis Komentar</h4>

        <?php if ($err): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($msg): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
          <form method="post" class="mb-3" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="post_comment">
            <div class="mb-3">
              <textarea name="comment" rows="5" class="form-control" placeholder="Tulis komentar Anda (maks 2000 karakter)"><?php echo isset($_POST['comment']) ? htmlspecialchars((string)$_POST['comment'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="note">HTML dalam komentar akan di-*escape* (aman).</div>
              <div>
                <button type="submit" class="btn btn-primary">Kirim Komentar</button>
              </div>
            </div>
          </form>
        <?php else: ?>
          <p>Anda harus <a href="login.php">login</a> untuk mengirim komentar.</p>
        <?php endif; ?>

        <hr>

        <h4>Komentar</h4>

        <?php if (empty($comments)): ?>
          <p class="text-muted">Belum ada komentar.</p>
        <?php else: ?>
          <div class="mt-3">
            <?php foreach ($comments as $c): ?>
              <div class="comment-card mb-3">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong><?php echo htmlspecialchars($c['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?></strong>
                    <div class="text-muted"><?php echo htmlspecialchars($c['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>

                  <?php if ($user && (int)($c['user_id'] ?? 0) === (int)($user['id'] ?? 0)): ?>
                    <div>
                      <form method="post" style="display:inline-block;" onsubmit="return confirm('Hapus komentar ini?');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="delete_comment_id" value="<?php echo (int)($c['id'] ?? 0); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                      </form>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="mt-2 escaped-comment">
                  <!-- SAFE: escaped + nl2br -->
                  <?php echo esc_nl((string)($c['comment'] ?? '')); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

      <div class="card-footer text-muted small">
        Halaman ini aman: komentar di-escape untuk mencegah XSS. Gunakan versi vulnerable (post_vul.php) untuk demo kontrastar.
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
