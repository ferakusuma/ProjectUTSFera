<?php
// vuln/delete.php
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['user'])) header('Location: ../login.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); exit('Bad Request'); }

// VULNERABLE: no ownership check
$pdo->exec("DELETE FROM items_vuln WHERE id = $id");
header('Location: list.php'); exit;
