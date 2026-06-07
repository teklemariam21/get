<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$currentUser = getCurrentUser();
$toId        = (int)($_POST['to_user_id'] ?? 0);
$message     = trim($_POST['message'] ?? '');

if (!$toId || empty($message) || $toId === $currentUser['id']) {
    echo json_encode(['success'=>false,'error'=>'Invalid input']); exit;
}

if (isBlocked($currentUser['id'], $toId)) {
    echo json_encode(['success'=>false,'error'=>'Cannot message this user']); exit;
}

$message = substr($message, 0, 2000);
$db      = getDB();
$stmt    = $db->prepare("INSERT INTO messages (from_user_id, to_user_id, message) VALUES (?,?,?)");
$stmt->execute([$currentUser['id'], $toId, $message]);
$msgId = $db->lastInsertId();

addNotification($toId, 'message', $currentUser['id'], htmlspecialchars($currentUser['username']) . ' sent you a message', '/messages.php?user=' . $currentUser['id']);

echo json_encode(['success'=>true,'id'=>$msgId,'time'=>date('h:i A')]);
