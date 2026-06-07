<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['messages'=>[]]); exit; }

$currentUser = getCurrentUser();
$partnerId   = (int)($_GET['partner_id'] ?? 0);
$lastId      = (int)($_GET['last_id'] ?? 0);

if (!$partnerId) { echo json_encode(['messages'=>[]]); exit; }

$db   = getDB();
$stmt = $db->prepare("
    SELECT m.id, m.message, m.created_at
    FROM messages m
    WHERE m.from_user_id = ? AND m.to_user_id = ? AND m.id > ?
    ORDER BY m.created_at ASC
    LIMIT 20
");
$stmt->execute([$partnerId, $currentUser['id'], $lastId]);
$rows = $stmt->fetchAll();

// Mark as read
$db->prepare("UPDATE messages SET is_read=1 WHERE from_user_id=? AND to_user_id=?")->execute([$partnerId, $currentUser['id']]);

$messages = array_map(fn($r) => [
    'id'      => (int)$r['id'],
    'message' => $r['message'],
    'time'    => date('h:i A', strtotime($r['created_at'])),
], $rows);

echo json_encode(['messages' => $messages]);
