<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }

$currentUser = getCurrentUser();
$targetId    = (int)($_POST['user_id'] ?? 0);

if (!$targetId || $targetId === $currentUser['id']) {
    echo json_encode(['success'=>false,'error'=>'Invalid target']);
    exit;
}

$target = getUserById($targetId);
if (!$target || isBlocked($currentUser['id'], $targetId)) {
    echo json_encode(['success'=>false,'error'=>'User not found']);
    exit;
}

$result = toggleLike($currentUser['id'], $targetId);
echo json_encode([
    'success'    => true,
    'action'     => $result['action'],
    'is_match'   => $result['is_match'],
    'match_user' => $result['is_match'] ? ['id' => $target['id'], 'username' => $target['username']] : null,
]);
