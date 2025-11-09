<?php
// safe/view.php
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['user'])) header('Location: ../login.php');

$uuid = $_GET['u'] ?? '';
// If token not provided in GET, show form to ask token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uuid = $_POST['u'] ?? '';
    $token = $_POST['token'] ?? '';
} else {
    $token = $_GET['t'] ?? '';
}

if (!$uuid) { http_response_code(400); exit('Missing uuid'); }

$stmt = $pdo->prepare("SELECT * FROM items_safe WHERE uuid = :u LIMIT 1");
$stmt->execute([':u'=>$uuid]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) { http_response_code(404); exit('Not found'); }

// Ownership check first (defense-in-depth)
if ($item['user_id'] != $_SESSION['user']['id']) {
    http_response_code(403); exit('Forbidden: not owner');
}

// If token not yet provided, ask user to input token (or provide via ?t=)
if (!$token) {
    // show token input form
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Access Token - Security Demo</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #1a202c;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .lock-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        
        .uuid-badge {
            display: inline-block;
            background: #edf2f7;
            color: #4a5568;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            font-weight: 500;
            word-break: break-all;
            margin-top: 10px;
        }
        
        .info-text {
            color: #4a5568;
            text-align: center;
            margin-bottom: 25px;
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
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            transition: all 0.3s ease;
            background: #f7fafc;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            margin-bottom: 12px;
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
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 28px;
            }
            
            .header h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="lock-icon">🔐</div>
            <h2>Enter Access Token</h2>
            <div class="uuid-badge"><?= htmlspecialchars($uuid) ?></div>
        </div>
        
        <p class="info-text">
            This item is protected with an access token. Please enter the token to view the content.
        </p>
        
        <form method="post">
            <input type="hidden" name="u" value="<?= htmlspecialchars($uuid) ?>">
            
            <div class="form-group">
                <label for="token">🔑 Access Token</label>
                <input 
                    type="text" 
                    id="token"
                    name="token" 
                    placeholder="Paste your access token here..."
                    required
                    autocomplete="off"
                >
            </div>
            
            <button type="submit" class="btn btn-primary">
                🔓 View Item
            </button>
            <a href="list.php" class="btn btn-secondary">
                ← Back to List
            </a>
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

// Verify token (compare hash)
$provided_hash = token_hash($token);
if (!hash_equals($item['token_hash'], $provided_hash)) {
    http_response_code(403); exit('Invalid token');
}

// Passed checks — show safe content escaped
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['title']) ?> - Security Demo</title>
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
            flex: 1;
        }
        
        .badge {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .content-section {
            margin-bottom: 30px;
        }
        
        .content-section h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .content-text {
            color: #4a5568;
            line-height: 1.8;
            font-size: 15px;
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
        }
        
        .meta-info {
            background: #edf2f7;
            padding: 15px 20px;
            border-radius: 10px;
            color: #4a5568;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .meta-info code {
            background: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            color: #3b82f6;
            font-size: 13px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
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
                🔓 <?= htmlspecialchars($item['title']) ?>
                <span class="badge">VERIFIED</span>
            </h2>
        </div>
        
        <div class="card">
            <div class="content-section">
                <h3>📄 Content</h3>
                <div class="content-text">
                    <?= nl2br(htmlspecialchars($item['content'])) ?>
                </div>
            </div>
            
            <div class="meta-info">
                🆔 UUID: <code><?= htmlspecialchars($item['uuid']) ?></code>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="list.php" class="back-btn">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>
</body>
</html>