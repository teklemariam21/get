<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) { redirect(SITE_URL . '/login.php'); }

$currentUser = getCurrentUser();
$targetId    = (int)($_GET['id'] ?? 0);

if (!$targetId || $targetId === $currentUser['id'] || !verifyCsrf($_GET['csrf'] ?? '')) {
    flash('error', 'Invalid request.');
    redirect(SITE_URL . '/browse.php');
}

$db   = getDB();
$stmt = $db->prepare("SELECT id FROM blocked_users WHERE blocker_id=? AND blocked_id=?");
$stmt->execute([$currentUser['id'], $targetId]);

if ($stmt->fetch()) {
    // Unblock
    $db->prepare("DELETE FROM blocked_users WHERE blocker_id=? AND blocked_id=?")->execute([$currentUser['id'], $targetId]);
    flash('success', 'User unblocked.');
} else {
    // Block
    $db->prepare("INSERT IGNORE INTO blocked_users (blocker_id, blocked_id) VALUES (?,?)")->execute([$currentUser['id'], $targetId]);
    // Remove any match
    $db->prepare("DELETE FROM matches WHERE (user1_id=? AND user2_id=?) OR (user1_id=? AND user2_id=?)")
       ->execute([$currentUser['id'], $targetId, $targetId, $currentUser['id']]);
    flash('success', 'User blocked. You will no longer see their profile.');
}

redirect(SITE_URL . '/browse.php');
