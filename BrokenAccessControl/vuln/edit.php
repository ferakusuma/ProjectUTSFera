<?php
// vuln/edit.php
require_once __DIR__ . '/../config.php';

// Authentication check
if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Get and validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Bad Request');
}

// Load item (no ownership check - vulnerability demonstration)
$row = $pdo->query("SELECT * FROM items_vuln WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(404);
    exit('Not found');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    
    // VULNERABLE: direct string concatenation into SQL (SQLi demonstration)
    $sql = "UPDATE items_vuln SET title = '{$title}', content = '{$content}' WHERE id = $id";
    $pdo->exec($sql);
    
    header('Location: list.php');
    exit;
}

$pageTitle = 'Edit Vulnerable Item';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            animation: fadeInUp 0.5s ease-out;
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
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #1a202c;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .id-badge {
            display: inline-block;
            background: #edf2f7;
            color: #4a5568;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            margin-right: 8px;
        }
        
        .warning-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff5f5;
            color: #c53030;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid #feb2b2;
        }
        
        .warning-icon {
            width: 14px;
            height: 14px;
        }
        
        .alert-box {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: start;
            gap: 12px;
        }
        
        .alert-icon {
            width: 20px;
            height: 20px;
            color: #c53030;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .alert-box-content {
            flex: 1;
        }
        
        .alert-box h4 {
            color: #c53030;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        
        .alert-box p {
            color: #742a2a;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }
        
        .alert-box ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        
        .alert-box li {
            color: #742a2a;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
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
            resize: vertical;
            min-height: 150px;
            line-height: 1.6;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
        }
        
        .icon {
            width: 18px;
            height: 18px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 24px;
            }
            
            .header h2 {
                font-size: 24px;
            }
            
            .button-group {
                flex-direction: column-reverse;
            }
            
            .btn {
                width: 100%;
            }
            
            .alert-box {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><?= htmlspecialchars($pageTitle) ?></h2>
            <div>
                <span class="id-badge">ID: <?= $row['id'] ?></span>
                <span class="warning-badge">
                    <svg class="warning-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Multiple Vulnerabilities
                </span>
            </div>
        </div>
        
        <div class="alert-box">
            <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="alert-box-content">
                <h4>⚠️ Peringatan Keamanan Kritis</h4>
                <p>Halaman ini memiliki beberapa vulnerability untuk tujuan demonstrasi:</p>
                <ul>
                    <li><strong>SQL Injection:</strong> Direct string concatenation tanpa prepared statements</li>
                    <li><strong>Missing Authorization:</strong> Tidak ada pengecekan ownership item</li>
                    <li><strong>No CSRF Protection:</strong> Tidak ada token CSRF untuk validasi</li>
                </ul>
            </div>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="title">Judul</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?= htmlspecialchars($row['title']) ?>" 
                    required
                    placeholder="Masukkan judul item"
                >
            </div>
            
            <div class="form-group">
                <label for="content">Konten</label>
                <textarea 
                    id="content" 
                    name="content" 
                    required
                    placeholder="Masukkan konten item Anda..."
                ><?= htmlspecialchars($row['content']) ?></textarea>
            </div>
            
            <div class="button-group">
                <a href="list.php" class="btn btn-secondary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</body>
</html>