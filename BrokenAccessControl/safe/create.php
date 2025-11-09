<?php
// safe/create.php
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['user'])) header('Location: ../login.php');

$success = false;
$created_uuid = '';
$created_token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF fail'); }
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title === '') { $err = "Title required"; }
    if (empty($err)) {
        $uuid = uuid4();
        $token = token_generate();
        $hash = token_hash($token);
        $stmt = $pdo->prepare("INSERT INTO items_safe (uuid, token_hash, token_expires_at, user_id, title, content)
                               VALUES (:uuid, :th, NULL, :uid, :t, :c)");
        $stmt->execute([
            ':uuid'=>$uuid, ':th'=>$hash, ':uid'=>$_SESSION['user']['id'],
            ':t'=>$title, ':c'=>$content
        ]);
        $success = true;
        $created_uuid = $uuid;
        $created_token = $token;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Safe Item - Security Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h2 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }

        .error-message {
            background: #ffe0e0;
            color: #ff4757;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #ff4757;
        }

        .success-card {
            background: linear-gradient(135deg, #d4f4e7, #a8e6cf);
            border-left: 5px solid #10ac84;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-card h3 {
            color: #0a7d5a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5em;
        }

        .info-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .info-box strong {
            color: #333;
            display: block;
            margin-bottom: 10px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .uuid-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            color: #495057;
            font-size: 0.95em;
            word-break: break-all;
        }

        .token-display {
            background: #fff3cd;
            border: 2px dashed #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .token-display pre {
            background: white;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            color: #d63384;
            font-size: 0.9em;
            overflow-x: auto;
            margin: 10px 0 0 0;
            word-break: break-all;
            white-space: pre-wrap;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            color: #856404;
            margin-top: 15px;
            display: flex;
            align-items: start;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #10ac84;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 172, 132, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 172, 132, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .copy-btn {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.9em;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: #2563eb;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0;
            }

            .header, .card {
                border-radius: 10px;
                padding: 20px;
            }

            .header h2 {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>
                ✅ Create Safe Item
                <span class="badge">SECURE</span>
            </h2>
            <a href="list.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($success): ?>
            <div class="card success-card">
                <h3>✨ Item Berhasil Dibuat!</h3>
                
                <div class="info-box">
                    <strong>🆔 UUID</strong>
                    <div class="uuid-display"><?= htmlspecialchars($created_uuid) ?></div>
                    <button class="copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($created_uuid) ?>')">
                        📋 Copy UUID
                    </button>
                </div>

                <div class="info-box">
                    <strong>🔑 Access Token</strong>
                    <div class="token-display">
                        <div style="color: #856404; margin-bottom: 10px;">
                            ⚠️ <strong>SIMPAN TOKEN INI SEKARANG!</strong>
                        </div>
                        Token ini hanya ditampilkan sekali dan tidak dapat dilihat lagi.
                        <pre><?= htmlspecialchars($created_token) ?></pre>
                        <button class="copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($created_token) ?>')">
                            📋 Copy Token
                        </button>
                    </div>
                </div>

                <div class="warning-box">
                    <span style="font-size: 1.5em;">⚠️</span>
                    <div>
                        <strong>Penting:</strong> Token ini diperlukan untuk mengakses item. 
                        Simpan di tempat yang aman karena tidak akan ditampilkan lagi!
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="list.php" class="btn btn-primary">📋 Lihat Semua Item</a>
                    <a href="create.php" class="btn btn-primary">➕ Buat Item Baru</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <?php if (!empty($err)): ?>
                    <div class="error-message">
                        ❌ <?= htmlspecialchars($err) ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="title">📝 Judul Item</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            placeholder="Masukkan judul item..."
                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="content">📄 Konten</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            placeholder="Masukkan konten item..."
                        ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>

                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    
                    <button type="submit" class="submit-btn">
                        ✨ Buat Item Aman
                    </button>
                </form>

                <div class="warning-box" style="margin-top: 25px;">
                    <span style="font-size: 1.5em;">🔐</span>
                    <div>
                        <strong>Keamanan:</strong> Item ini akan dilindungi dengan UUID dan Access Token. 
                        Token hanya ditampilkan sekali setelah pembuatan.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('✅ Berhasil disalin ke clipboard!');
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        }
    </script>
</body>
</html>