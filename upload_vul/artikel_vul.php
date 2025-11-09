<?php
require 'config.php';
require_login();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = 'uploads/';
        $file_name = $_FILES['file']['name'];
        $tmp_file = $_FILES['file']['tmp_name'];
        $target = $upload_dir . basename($file_name);

        // ❌ TIDAK ADA VALIDASI — RENTAN!
        if (move_uploaded_file($tmp_file, $target)) {
            $file_path = $target;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO articles (user_id, title, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $content, $file_path]);

    $message = "Artikel berhasil disimpan!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - Versi RENTAN</title>
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            padding: 25px 0;
            margin-bottom: 30px;
        }

        .header-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-title {
            font-size: 32px;
            color: #2d3748;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .vulnerability-badge {
            font-size: 14px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(229, 62, 62, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 10px rgba(229, 62, 62, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 6px 15px rgba(229, 62, 62, 0.5);
            }
        }

        /* Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Alert Warning */
        .alert-warning {
            background: white;
            border-left: 6px solid #e53e3e;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 20px;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-warning .icon {
            font-size: 40px;
            flex-shrink: 0;
            animation: shake 2s infinite;
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }

        .alert-warning .content h3 {
            color: #c53030;
            font-size: 20px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .alert-warning .content p {
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .alert-warning ul {
            margin-left: 20px;
            color: #4a5568;
        }

        .alert-warning li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .alert-warning strong {
            color: #e53e3e;
            font-weight: 600;
        }

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, #68d391 0%, #38a169 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(56, 161, 105, 0.3);
            animation: slideDown 0.5s ease-out;
        }

        .success-message .icon {
            font-size: 24px;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Group */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        /* File Upload */
        .file-upload-wrapper {
            position: relative;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            background: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #edf2f7;
            border-color: #3b82f6;
        }

        .file-upload-label .icon {
            font-size: 32px;
        }

        .file-upload-label .text {
            flex: 1;
            color: #4a5568;
        }

        .file-upload-label .filename {
            color: #2d3748;
            font-weight: 600;
        }

        .file-note {
            margin-top: 10px;
            font-size: 13px;
            color: #e53e3e;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(229, 62, 62, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit span:first-child {
            font-size: 20px;
        }

        /* Info Box */
        .info-box {
            background: white;
            border-left: 5px solid #3b82f6;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.7s ease-out;
        }

        .info-box h4 {
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            margin-left: 20px;
            color: #4a5568;
        }

        .info-box li {
            margin-bottom: 10px;
            line-height: 1.7;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: white;
            color: #2d3748;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            transform: translateX(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            background: #f7fafc;
        }

        .back-link span:first-child {
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 24px;
                flex-direction: column;
                align-items: flex-start;
            }

            .vulnerability-badge {
                font-size: 12px;
                padding: 6px 12px;
            }

            .alert-warning {
                flex-direction: column;
                padding: 20px;
            }

            .alert-warning .icon {
                font-size: 32px;
            }

            .form-card {
                padding: 25px 20px;
            }

            .form-group input[type="text"],
            .form-group textarea {
                font-size: 14px;
            }

            .btn-submit {
                font-size: 15px;
                padding: 14px 20px;
            }
        }

        /* Loading State */
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1 class="page-title">
                ⚠️ Tulis Artikel
                <span class="vulnerability-badge">VERSI RENTAN</span>
            </h1>
        </div>
    </div>

    <div class="container">
        <!-- Alert Warning -->
        <div class="alert-warning">
            <div class="icon">🚨</div>
            <div class="content">
                <h3>PERINGATAN KEAMANAN!</h3>
                <p>Halaman ini mengandung celah keamanan yang BERBAHAYA:</p>
                <ul>
                    <li><strong>File Upload Tanpa Validasi</strong> - Memungkinkan upload file PHP berbahaya</li>
                    <li><strong>XSS Vulnerability</strong> - Input tidak disanitasi dengan benar</li>
                    <li><strong>Path Traversal Risk</strong> - Nama file tidak divalidasi</li>
                </ul>
                <p style="margin-top: 10px;"><strong>JANGAN gunakan kode ini di produksi!</strong> Ini hanya untuk pembelajaran.</p>
            </div>
        </div>

        <!-- Success Message -->
        <?php if ($message): ?>
            <div class="success-message">
                <span class="icon">✅</span>
                <span><?= $message ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <form method="post" enctype="multipart/form-data" id="articleForm">
                <div class="form-group">
                    <label for="title">📝 Judul Artikel</label>
                    <input type="text" id="title" name="title" placeholder="Masukkan judul artikel..." required>
                </div>

                <div class="form-group">
                    <label for="content">📄 Isi Artikel</label>
                    <textarea id="content" name="content" placeholder="Tulis isi artikel di sini..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="file">📎 Upload File (Opsional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="file" name="file" onchange="updateFileName(this)">
                        <label for="file" class="file-upload-label">
                            <span class="icon">📁</span>
                            <span class="text">
                                <span class="placeholder">Pilih file untuk diupload...</span>
                                <span class="filename" style="display:none;"></span>
                            </span>
                        </label>
                    </div>
                    <p class="file-note">⚠️ WARNING: Tidak ada validasi file! Semua jenis file dapat diupload.</p>
                </div>

                <button type="submit" class="btn-submit">
                    <span>💾</span>
                    <span>Simpan Artikel</span>
                </button>
            </form>
        </div>

        <!-- Info Box -->
        <div class="info-box">
            <h4>🛡️ Apa yang salah dengan form ini?</h4>
            <ul>
                <li>Tidak ada pengecekan tipe file (extension, MIME type)</li>
                <li>Tidak ada batasan ukuran file</li>
                <li>File disimpan dengan nama asli tanpa sanitasi</li>
                <li>Tidak ada proteksi terhadap file executable (.php, .exe, dll)</li>
                <li>Path upload bisa di-exploit dengan path traversal</li>
            </ul>
        </div>

        <!-- Back Link -->
        <a href="dashboard_upvul.php" class="back-link">
            <span>←</span>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <script>
        function updateFileName(input) {
            const label = input.nextElementSibling;
            const placeholder = label.querySelector('.placeholder');
            const filename = label.querySelector('.filename');
            
            if (input.files.length > 0) {
                placeholder.style.display = 'none';
                filename.style.display = 'inline';
                filename.textContent = input.files[0].name;
            } else {
                placeholder.style.display = 'inline';
                filename.style.display = 'none';
            }
        }
    </script>
</body>
</html>