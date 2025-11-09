<?php
// vuln/create.php
require_once __DIR__ . '/../config.php';

// Authentication check
if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $uid = (int)$_SESSION['user']['id'];
    
    // VULNERABLE: string concatenation into SQL (demonstrate SQLi)
    $sql = "INSERT INTO items_vuln (user_id, title, content) VALUES ($uid, '{$title}', '{$content}')";
    $pdo->exec($sql);
    
    header('Location: list.php');
    exit;
}

$pageTitle = 'Create Vulnerable Item';
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
        
        .warning-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff5f5;
            color: #c53030;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: 2px solid #feb2b2;
        }
        
        .warning-icon {
            width: 16px;
            height: 16px;
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
        
        .char-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
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
        
        .info-box {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .info-box p {
            color: #742a2a;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }
        
        .info-box strong {
            color: #c53030;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><?= htmlspecialchars($pageTitle) ?></h2>
            <span class="warning-badge">
                <svg class="warning-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                SQL Injection Vulnerable
            </span>
        </div>
        
        <div class="info-box">
            <p><strong>⚠️ Peringatan:</strong> Form ini rentan terhadap SQL Injection untuk tujuan demonstrasi keamanan. Jangan gunakan di production!</p>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="title">Judul</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    placeholder="Masukkan judul item"
                    required
                >
                <div class="char-hint">Contoh: Meeting Notes, Project Ideas</div>
            </div>
            
            <div class="form-group">
                <label for="content">Konten</label>
                <textarea 
                    id="content" 
                    name="content" 
                    placeholder="Tulis konten item Anda di sini..."
                    required
                ></textarea>
                <div class="char-hint">Jelaskan detail tentang item ini</div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Item
                </button>
            </div>
        </form>
    </div>
</body>
</html>