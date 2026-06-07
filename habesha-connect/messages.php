<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser  = getCurrentUser();
$db           = getDB();

$partnerParam = (int)($_GET['user'] ?? 0);
$partner      = $partnerParam ? getUserById($partnerParam) : null;

if ($partner && isBlocked($currentUser['id'], $partner['id'])) {
    flash('error', 'You cannot message this user.');
    redirect(SITE_URL . '/browse.php');
}

// Handle new message send (non-AJAX fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $toId = (int)($_POST['to_user_id'] ?? 0);
    $msg  = trim($_POST['message'] ?? '');
    if ($toId && $msg && !isBlocked($currentUser['id'], $toId)) {
        $db->prepare("INSERT INTO messages (from_user_id, to_user_id, message) VALUES (?,?,?)")
           ->execute([$currentUser['id'], $toId, substr($msg, 0, 2000)]);
        addNotification($toId, 'message', $currentUser['id'], htmlspecialchars($currentUser['username']) . ' sent you a message', '/messages.php?user=' . $currentUser['id']);
    }
    redirect(SITE_URL . '/messages.php?user=' . $toId);
}

// Mark messages as read
if ($partner) {
    $db->prepare("UPDATE messages SET is_read = 1 WHERE from_user_id = ? AND to_user_id = ?")
       ->execute([$partner['id'], $currentUser['id']]);
}

// Conversations list
$conversations = getConversations($currentUser['id']);

// Chat messages
$chatMessages = [];
$lastMsgId    = 0;
if ($partner) {
    $stmt = $db->prepare("
        SELECT m.*, u.username, u.profile_photo, u.gender
        FROM messages m
        JOIN users u ON u.id = m.from_user_id
        WHERE (m.from_user_id = ? AND m.to_user_id = ?)
           OR (m.from_user_id = ? AND m.to_user_id = ?)
        ORDER BY m.created_at ASC
        LIMIT 100
    ");
    $stmt->execute([$currentUser['id'], $partner['id'], $partner['id'], $currentUser['id']]);
    $chatMessages = $stmt->fetchAll();
    $lastMsgId    = !empty($chatMessages) ? (int)end($chatMessages)['id'] : 0;
}

// Check if premium is required
$canMessage = true;
if ($partner && !$currentUser['is_premium']) {
    // Free users: allow messaging with matches only
    if ($currentUser['allow_messages'] === 'matches' && !isMatch($currentUser['id'], $partner['id'])) {
        $canMessage = false;
    }
}

$pageTitle = $partner ? 'Chat with ' . htmlspecialchars($partner['username']) : 'Messages';
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container py-3">
    <div class="messages-layout">

        <!-- Conversations List -->
        <div class="conv-list">
            <div class="conv-search">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="convSearch" placeholder="Search conversations...">
                </div>
            </div>

            <?php if (empty($conversations)): ?>
            <div class="text-center text-muted py-5 px-3">
                <i class="bi bi-chat-dots" style="font-size:2.5rem;opacity:.3"></i>
                <p class="mt-2 small">No conversations yet.<br><a href="<?= SITE_URL ?>/browse.php">Browse members</a> to start chatting!</p>
            </div>
            <?php else: ?>
            <?php foreach ($conversations as $conv): ?>
            <a href="<?= SITE_URL ?>/messages.php?user=<?= $conv['partner_id'] ?>"
               class="conv-item <?= ($partner && $conv['partner_id'] == $partner['id']) ? 'active' : '' ?>">
                <div class="conv-avatar">
                    <img src="<?= getProfilePhoto($conv['profile_photo'], $conv['gender']) ?>" alt="">
                    <?php if (isOnline($conv['last_active'])): ?>
                    <span class="online-dot position-absolute" style="bottom:0;right:0;width:12px;height:12px;border:2px solid #fff;border-radius:50%;background:#22c55e"></span>
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between">
                        <span class="conv-name"><?= htmlspecialchars($conv['username']) ?></span>
                        <span class="conv-time"><?= timeAgo($conv['created_at']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="conv-preview"><?= htmlspecialchars(mb_substr($conv['message'], 0, 40)) ?></span>
                        <?php if ($conv['unread_count'] > 0): ?>
                        <span class="conv-unread"><?= $conv['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <?php if (!$partner): ?>
            <div class="chat-placeholder">
                <i class="bi bi-chat-heart" style="font-size:3.5rem;opacity:.25"></i>
                <h5 class="text-muted">Select a conversation</h5>
                <p class="small text-muted">Choose a chat from the left or browse members to start a new conversation.</p>
                <a href="<?= SITE_URL ?>/browse.php" class="btn btn-primary btn-sm fw-600">Browse Members</a>
            </div>
            <?php else: ?>

            <!-- Chat Header -->
            <div class="chat-header">
                <div class="position-relative">
                    <img src="<?= getProfilePhoto($partner['profile_photo'], $partner['gender']) ?>"
                         class="rounded-circle" width="42" height="42" style="object-fit:cover" alt="">
                    <?php if (isOnline($partner['last_active'])): ?>
                    <span class="online-dot" style="position:absolute;bottom:1px;right:1px;width:11px;height:11px;border:2px solid #fff;border-radius:50%;background:#22c55e"></span>
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-700">
                        <a href="<?= SITE_URL ?>/profile.php?id=<?= $partner['id'] ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($partner['username']) ?>
                            <?php if ($partner['is_verified']): ?>
                            <i class="bi bi-patch-check-fill text-primary" style="font-size:.8rem"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="small text-muted">
                        <?= isOnline($partner['last_active']) ? '<span class="text-success">Online now</span>' : 'Last active ' . timeAgo($partner['last_active'] ?? $partner['created_at']) ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $partner['id'] ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-person"></i>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile.php?id=<?= $partner['id'] ?>"><i class="bi bi-person me-2"></i>View Profile</a></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/api/block.php?id=<?= $partner['id'] ?>&csrf=<?= csrfToken() ?>" onclick="return confirm('Block this user?')"><i class="bi bi-slash-circle me-2"></i>Block User</a></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/report.php?id=<?= $partner['id'] ?>"><i class="bi bi-flag me-2"></i>Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="chat-messages" id="chatMessages" data-partner-id="<?= $partner['id'] ?>">
                <?php if (empty($chatMessages)): ?>
                <div class="text-center text-muted py-5">
                    <div style="font-size:2.5rem">👋</div>
                    <p class="mt-2 small">Say hello to <?= htmlspecialchars($partner['username']) ?>!</p>
                </div>
                <?php else: ?>
                <?php foreach ($chatMessages as $msg): ?>
                <?php $isMine = $msg['from_user_id'] == $currentUser['id']; ?>
                <div class="message-bubble-wrap <?= $isMine ? 'mine' : '' ?>" data-msg-id="<?= $msg['id'] ?>">
                    <?php if (!$isMine): ?>
                    <img src="<?= getProfilePhoto($msg['profile_photo'], $msg['gender']) ?>"
                         class="rounded-circle flex-shrink-0" width="28" height="28" style="object-fit:cover" alt="">
                    <?php endif; ?>
                    <div class="message-bubble <?= $isMine ? 'mine' : 'theirs' ?>">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </div>
                    <div class="message-time"><?= date('h:i A', strtotime($msg['created_at'])) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Input -->
            <?php if (!$canMessage): ?>
            <div class="chat-input-area text-center py-3">
                <div class="text-muted small mb-2">
                    <i class="bi bi-lock me-1"></i>Match with this user or
                    <a href="<?= SITE_URL ?>/subscription.php" class="text-warning fw-600">go Premium</a> to send messages.
                </div>
            </div>
            <?php else: ?>
            <div class="chat-input-area">
                <form id="chatForm" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="to_user" value="<?= $partner['id'] ?>">
                    <div class="flex-grow-1">
                        <input type="text" class="form-control" name="message"
                               placeholder="Type your message..." maxlength="2000" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary fw-600 px-3">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
                <div class="text-muted text-center mt-1" style="font-size:.7rem">Press Enter to send • Be respectful</div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
// Enter to send
document.querySelector('#chatForm input[name="message"]')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm')?.requestSubmit();
    }
});

// Search conversations
document.getElementById('convSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.conv-item').forEach(item => {
        const name = item.querySelector('.conv-name')?.textContent.toLowerCase() ?? '';
        item.style.display = name.includes(q) ? '' : 'none';
    });
});
</script>
