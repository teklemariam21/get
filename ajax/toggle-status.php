<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success' => false]); exit; }

$post = $pdo->prepare("SELECT status FROM posts WHERE id = ?");
$post->execute([$id]);
$row = $post->fetch();
if (!$row) { echo json_encode(['success' => false]); exit; }

$newStatus = $row['status'] === 'published' ? 'draft' : 'published';
$pdo->prepare("UPDATE posts SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

echo json_encode(['success' => true, 'new_status' => $newStatus]);
