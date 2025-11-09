<?php
// vuln/list.php
require_once __DIR__ . '/../config.php';

// Authentication check
if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch all vulnerable items with user information
$res = $pdo->query("
    SELECT items_vuln.*, users.username 
    FROM items_vuln 
    JOIN users ON items_vuln.user_id = users.id 
    ORDER BY items_vuln.id DESC
");

$pageTitle = 'Vulnerable Items';
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
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 30px 40px;
            margin-bottom: 24px;
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
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .header h1 {
            color: #1a202c;
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
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
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
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
            width: 16px;
            height: 16px;
        }
        
        .alert-box {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 16px 20px;
            border-radius: 8px;
            margin-top: 20px;
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
        
        .alert-box p {
            color: #742a2a;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        
        .alert-box strong {
            color: #c53030;
        }
        
        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
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
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }
        
        thead th {
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 16px 20px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s ease;
        }
        
        tbody tr:hover {
            background-color: #f7fafc;
        }
        
        tbody tr:last-child {
            border-bottom: none;
        }
        
        tbody td {
            padding: 16px 20px;
            color: #2d3748;
            font-size: 14px;
        }
        
        .id-badge {
            display: inline-block;
            background: #edf2f7;
            color: #4a5568;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        
        .title-cell {
            font-weight: 600;
            color: #1a202c;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .content-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #4a5568;
        }
        
        .author-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .author-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .action-links {
            display: flex;
            gap: 12px;
        }
        
        .action-link {
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .action-link:hover {
            color: #3b82f6;
        }
        
        .action-link.delete:hover {
            color: #e53e3e;
        }
        
        .action-icon {
            width: 14px;
            height: 14px;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
        
        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            color: #cbd5e0;
        }
        
        .empty-state h3 {
            color: #2d3748;
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #718096;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 20px 12px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .header-top {
                flex-direction: column;
                align-items: stretch;
            }
            
            .header-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            thead th,
            tbody td {
                padding: 12px;
                font-size: 13px;
            }
            
            .title-cell,
            .content-cell {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-top">
                <h1>
                    <?= htmlspecialchars($pageTitle) ?>
                    <span class="warning-badge">
                        <svg class="warning-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        XSS Vulnerable
                    </span>
                </h1>
                <div class="header-actions">
                    <a href="create.php" class="btn btn-primary">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Item Baru
                    </a>
                    <a href="../dashboard_bac.php" class="btn btn-secondary">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </div>
            </div>
            
            <div class="alert-box">
                <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p><strong>⚠️ Peringatan Keamanan:</strong> Halaman ini dengan sengaja tidak menggunakan htmlspecialchars() pada konten untuk mendemonstrasikan stored XSS vulnerability. Jangan gunakan pola ini di production!</p>
            </div>
        </div>
        
        <div class="content-card">
            <div class="table-container">
                <?php if ($res->rowCount() > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Konten</th>
                            <th>Pembuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($res as $r): ?>
                        <tr>
                            <td>
                                <span class="id-badge">#<?= $r['id'] ?></span>
                            </td>
                            <td class="title-cell" title="<?= $r['title'] ?>">
                                <?= $r['title'] ?>
                            </td>
                            <!-- intentionally not escaped (stored XSS demonstration) -->
                            <td class="content-cell" title="<?= strip_tags($r['content']) ?>">
                                <?= $r['content'] ?>
                            </td>
                            <td>
                                <div class="author-cell">
                                    <div class="author-avatar">
                                        <?= strtoupper(substr($r['username'], 0, 1)) ?>
                                    </div>
                                    <?= $r['username'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-links">
                                    <a href="edit.php?id=<?= $r['id'] ?>" class="action-link">
                                        <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="delete.php?id=<?= $r['id'] ?>" class="action-link delete" onclick="return confirm('Yakin ingin menghapus item ini?')">
                                        <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3>Belum ada item</h3>
                    <p>Mulai dengan membuat item vulnerable pertama Anda</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>