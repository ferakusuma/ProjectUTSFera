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
        $file_size = $_FILES['file']['size'];

        // ✅ Validasi ekstensi
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            die("Ekstensi file tidak diizinkan!");
        }

        // ✅ Validasi ukuran (max 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            die("File terlalu besar! Maksimal 2MB.");
        }

        // ✅ Validasi MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp_file);
        finfo_close($finfo);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($mime, $allowed_mimes)) {
            die("Tipe file tidak valid!");
        }

        // ✅ Nama file acak
        $new_name = uniqid('upload_') . '.' . $ext;
        $target = $upload_dir . $new_name;

        if (move_uploaded_file($tmp_file, $target)) {
            $file_path = $target;
        } else {
            die("Gagal menyimpan file.");
        }
    }

    $stmt = $pdo->prepare("INSERT INTO articles (user_id, title, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $content, $file_path]);

    $message = "Artikel berhasil disimpan dengan aman!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - Versi AMAN</title>
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

        .secure-badge {
            font-size: 14px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #68d391 0%, #38a169 100%);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(56, 161, 105, 0.3);
            display: flex;
            align-items: center;
            gap: 6px;
            animation: glow 2s infinite;
        }

        @keyframes glow {
            0%, 100% {
                box-shadow: 0 4px 10px rgba(56, 161, 105, 0.3);
            }
            50% {
                box-shadow: 0 6px 20px rgba(56, 161, 105, 0.5);
            }
        }

        /* Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Alert Success */
        .alert-success {
            background: white;
            border-left: 6px solid #38a169;
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

        .alert-success .icon {
            font-size: 40px;
            flex-shrink: 0;
        }

        .alert-success .content h3 {
            color: #22543d;
            font-size: 20px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .alert-success .content p {
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .alert-success ul {
            margin-left: 20px;
            color: #4a5568;
        }

        .alert-success li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .alert-success strong {
            color: #38a169;
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
            background: #f0fdf4;
            border: 2px dashed #68d391;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #dcfce7;
            border-color: #38a169;
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
            color: #38a169;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .file-restrictions {
            margin-top: 8px;
            padding: 10px 12px;
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            border-radius: 6px;
            font-size: 13px;
            color: #1e40af;
        }

        .file-restrictions strong {
            font-weight: 600;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #68d391 0%, #38a169 100%);
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
            box-shadow: 0 4px 15px rgba(56, 161, 105, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 161, 105, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit span:first-child {
            font-size: 20px;
        }

        /* Security Features */
        .security-features {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.7s ease-out;
        }

        .security-features h3 {
            color: #2d3748;
            font-size: 22px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .feature-item {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: #eff6ff;
        }

        .feature-item .icon {
            font-size: 28px;
            flex-shrink: 0;
        }

        .feature-item .text .title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .feature-item .text .description {
            font-size: 13px;
            color: #718096;
            line-height: 1.5;
        }

        /* Comparison Link */
        .comparison-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 24px;
            background: white;
            color: #e53e3e;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: 2px solid #fed7d7;
        }

        .comparison-link:hover {
            transform: translateX(5px);
            box-shadow: 0 6px 20px rgba(229, 62, 62, 0.2);
            background: #fff5f5;
            border-color: #fc8181;
        }

        .comparison-link span:first-child,
        .comparison-link span:last-child {
            font-size: 18px;
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

            .secure-badge {
                font-size: 12px;
                padding: 6px 12px;
            }

            .alert-success {
                flex-direction: column;
                padding: 20px;
            }

            .alert-success .icon {
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

            .features-grid {
                grid-template-columns: 1fr;
            }

            .comparison-link {
                width: 100%;
                justify-content: center;
                margin-bottom: 15px;
            }

            .back-link {
                width: 100%;
                justify-content: center;
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
                🛡️ Tulis Artikel
                <span class="secure-badge">
                    <span>✅</span>
                    <span>VERSI AMAN</span>
                </span>
            </h1>
        </div>
    </div>

    <div class="container">
        <!-- Alert Success Info -->
        <div class="alert-success">
            <div class="icon">🔒</div>
            <div class="content">
                <h3>Keamanan Terjamin!</h3>
                <p>Halaman ini menerapkan <strong>best practices</strong> keamanan upload file:</p>
                <ul>
                    <li><strong>Validasi Ekstensi</strong> - Hanya file gambar (JPG, PNG, GIF) dan PDF yang diizinkan</li>
                    <li><strong>Validasi MIME Type</strong> - Memverifikasi tipe file sebenarnya, bukan hanya ekstensi</li>
                    <li><strong>Batasan Ukuran</strong> - Maksimal 2MB untuk mencegah DoS</li>
                    <li><strong>Nama File Acak</strong> - Mencegah file overwrite dan path traversal</li>
                </ul>
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
                        <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="updateFileName(this)">
                        <label for="file" class="file-upload-label">
                            <span class="icon">📁</span>
                            <span class="text">
                                <span class="placeholder">Pilih file untuk diupload...</span>
                                <span class="filename" style="display:none;"></span>
                            </span>
                        </label>
                    </div>
                    <p class="file-note">
                        <span>✅</span>
                        <span>File akan divalidasi untuk keamanan</span>
                    </p>
                    <div class="file-restrictions">
                        <strong>Batasan File:</strong>
                        Format: JPG, JPEG, PNG, GIF, PDF | Ukuran Maksimal: 2MB
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>🔒</span>
                    <span>Simpan Artikel dengan Aman</span>
                </button>
            </form>
        </div>

        <!-- Security Features -->
        <div class="security-features">
            <h3>
                <span>🛡️</span>
                <span>Fitur Keamanan yang Diterapkan</span>
            </h3>
            <div class="features-grid">
                <div class="feature-item">
                    <span class="icon">🔍</span>
                    <div class="text">
                        <div class="title">Validasi Ekstensi</div>
                        <div class="description">Memeriksa ekstensi file sebelum upload</div>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="icon">🎯</span>
                    <div class="text">
                        <div class="title">Validasi MIME Type</div>
                        <div class="description">Verifikasi tipe file yang sebenarnya</div>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="icon">📏</span>
                    <div class="text">
                        <div class="title">Batasan Ukuran</div>
                        <div class="description">Maksimal 2MB untuk mencegah abuse</div>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="icon">🔀</span>
                    <div class="text">
                        <div class="title">Nama File Acak</div>
                        <div class="description">Menggunakan uniqid() untuk keamanan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Link -->
        <a href="artikel_vul.php" class="comparison-link">
            <span>⚠️</span>
            <span>Bandingkan dengan Versi Rentan</span>
            <span>→</span>
        </a>

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
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                placeholder.style.display = 'none';
                filename.style.display = 'inline';
                filename.textContent = `${file.name} (${fileSize} MB)`;
                
                // Validasi ukuran di client-side
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ File terlalu besar! Maksimal 2MB.');
                    input.value = '';
                    placeholder.style.display = 'inline';
                    filename.style.display = 'none';
                }
            } else {
                placeholder.style.display = 'inline';
                filename.style.display = 'none';
            }
        }
    </script>
</body>
</html>