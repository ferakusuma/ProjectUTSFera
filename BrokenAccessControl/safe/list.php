<?php
// safe/list.php
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['user'])) header('Location: ../login.php');

$stmt = $pdo->prepare("SELECT id, uuid, title, created_at FROM items_safe WHERE user_id = :u ORDER BY created_at DESC");
$stmt->execute([':u' => $_SESSION['user']['id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safe Items - Security Demo</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .header h2 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8em;
        }

        .badge {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.95em;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 172, 132, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .info-banner {
            background: linear-gradient(135deg, #d4f4e7, #a8e6cf);
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #10ac84;
            color: #0a7d5a;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            margin-bottom: 25px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .uuid-cell {
            font-family: 'Courier New', monospace;
            color: #667eea;
            font-size: 0.85em;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .title-cell {
            font-weight: 600;
            color: #333;
        }

        .date-cell {
            color: #6c757d;
            font-size: 0.9em;
        }

        .action-cell {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 0.85em;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            background: #5568d3;
        }

        .btn-edit {
            background: #ffa502;
            color: white;
        }

        .btn-edit:hover {
            background: #e59400;
        }

        .btn-delete {
            background: #ff4757;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #ff3838;
        }

        .delete-form {
            display: inline;
        }

        .stats-bar {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #495057;
        }

        .stats-number {
            font-size: 1.5em;
            font-weight: 700;
            color: #10ac84;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0;
            }

            .header, .card {
                border-radius: 10px;
            }

            .header {
                padding: 20px;
            }

            .header h2 {
                font-size: 1.4em;
            }

            .header-actions {
                width: 100%;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }

            table {
                font-size: 0.9em;
            }

            th, td {
                padding: 12px 10px;
            }

            .uuid-cell {
                max-width: 120px;
            }

            .action-cell {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-small {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-top">
                <h2>
                    🔐 Safe Items
                    <span class="badge">SECURE</span>
                </h2>
                <div class="header-actions">
                    <a href="create.php" class="btn btn-primary">
                        ➕ Buat Item Baru
                    </a>
                    <a href="../dashboard_bac.php" class="btn btn-secondary">
                        ← Dashboard
                    </a>
                </div>
            </div>
            <div class="info-banner">
                <span style="font-size: 1.5em;">✅</span>
                <div>
                    <strong>Area Aman:</strong> Semua item dilindungi dengan UUID dan Access Token. 
                    Hanya pemilik yang dapat mengakses data mereka.
                </div>
            </div>
        </div>

        <div class="card">
            <?php if (count($rows) > 0): ?>
                <div class="stats-bar">
                    <div class="stats-item">
                        <span style="font-size: 1.5em;">📊</span>
                        <div>
                            <div class="stats-number"><?= count($rows) ?></div>
                            <div style="font-size: 0.9em;">Total Items</div>
                        </div>
                    </div>
                    <div style="color: #6c757d; font-size: 0.9em;">
                        Menampilkan item milik Anda
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>🆔 UUID</th>
                                <th>📝 Judul</th>
                                <th>📅 Dibuat</th>
                                <th>⚙️ Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rows as $r): ?>
                            <tr>
                                <td>
                                    <div class="uuid-cell" title="<?= htmlspecialchars($r['uuid']) ?>">
                                        <?= htmlspecialchars($r['uuid']) ?>
                                    </div>
                                </td>
                                <td class="title-cell">
                                    <?= htmlspecialchars($r['title']) ?>
                                </td>
                                <td class="date-cell">
                                    <?= htmlspecialchars(date('d M Y, H:i', strtotime($r['created_at']))) ?>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <a href="view.php?u=<?= urlencode($r['uuid']) ?>" class="btn-small btn-view">
                                            👁️ View
                                        </a>
                                        <a href="edit.php?u=<?= urlencode($r['uuid']) ?>" class="btn-small btn-edit">
                                            ✏️ Edit
                                        </a>
                                        <form action="delete.php" method="post" class="delete-form" onsubmit="return confirm('⚠️ Yakin ingin menghapus item ini?')">
                                            <input type="hidden" name="uuid" value="<?= htmlspecialchars($r['uuid']) ?>">
                                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn-small btn-delete">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <h3>Belum Ada Item</h3>
                    <p>Anda belum membuat item aman. Mulai dengan membuat item pertama Anda!</p>
                    <a href="create.php" class="btn btn-primary">
                        ➕ Buat Item Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>