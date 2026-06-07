<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id = (int)$_POST['id'];
try {
    $post = $pdo->prepare("SELECT image FROM posts WHERE id = ?")->execute([$id]);
    $row = $pdo->query("SELECT image FROM posts WHERE id = $id")->fetch();
    if ($row && $row['image']) {
        $imgPath = UPLOAD_DIR . $row['image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }
    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
