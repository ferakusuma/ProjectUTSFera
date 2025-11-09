<?php
// safe/delete.php
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['user'])) header('Location: ../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
if (!check_csrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF fail'); }

$uuid = $_POST['uuid'] ?? '';
if (!$uuid) { http_response_code(400); exit('Missing uuid'); }

// verify ownership
$stmt = $pdo->prepare("SELECT user_id FROM items_safe WHERE uuid = :u LIMIT 1");
$stmt->execute([':u'=>$uuid]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) { http_response_code(404); exit('Not found'); }
if ($item['user_id'] != $_SESSION['user']['id']) { http_response_code(403); exit('Forbidden'); }

// delete
$stmt = $pdo->prepare("DELETE FROM items_safe WHERE uuid = :u");
$stmt->execute([':u'=>$uuid]);
header('Location: list.php'); exit;
