<?php
// safe/edit.php
require_once __DIR__ . '/../config.php';

// Authentication check
if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Get UUID from request
$uuid = $_GET['u'] ?? ($_POST['uuid'] ?? '');
if (!$uuid) {
    http_response_code(400);
    exit('Missing uuid');
}

// Fetch item from database
$stmt = $pdo->prepare("SELECT * FROM items_safe WHERE uuid = :u LIMIT 1");
$stmt->execute([':u' => $uuid]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    http_response_code(404);
    exit('Not found');
}

// Ownership verification
if ($item['user_id'] != $_SESSION['user']['id']) {
    http_response_code(403);
    exit('Forbidden: not owner');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('CSRF fail');
    }
    
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    $stmt = $pdo->prepare("UPDATE items_safe SET title = :t, content = :c WHERE uuid = :u");
    $stmt->execute([':t' => $title, ':c' => $content, ':u' => $uuid]);
    
    header('Location: list.php');
    exit;
}

$pageTitle = 'Edit Safe Item';
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
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #1a202c;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .uuid-badge {
            display: inline-block;
            background: #edf2f7;
            color: #4a5568;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            font-weight: 500;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><?= htmlspecialchars($pageTitle) ?></h2>
            <span class="uuid-badge"><?= htmlspecialchars($item['uuid']) ?></span>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="title">Judul</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?= htmlspecialchars($item['title']) ?>" 
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
                ><?= htmlspecialchars($item['content']) ?></textarea>
            </div>
            
            <input type="hidden" name="uuid" value="<?= htmlspecialchars($item['uuid']) ?>">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            
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