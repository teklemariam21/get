<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }

$adminTitle = 'Messages';

// Mark as read if viewing specific
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $pdo->prepare("UPDATE contacts SET is_read=1 WHERE id=?")->execute([(int)$_GET['read']]);
}

// Mark all read
if (isset($_GET['markall'])) {
    $pdo->query("UPDATE contacts SET is_read=1");
    header('Location: messages.php');
    exit;
}

// Delete message
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM contacts WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: messages.php');
    exit;
}

$messages = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();

// Viewing single message
$viewMsg = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id=?");
    $stmt->execute([(int)$_GET['view']]);
    $viewMsg = $stmt->fetch();
    if ($viewMsg) $pdo->prepare("UPDATE contacts SET is_read=1 WHERE id=?")->execute([$viewMsg['id']]);
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title">Messages</h1>
        <?php $unread = array_sum(array_column($messages, 'is_read') ? array_map(fn($m) => !$m['is_read'], $messages) : []); ?>
        <a href="?markall=1" class="btn-outline-glow" style="font-size:0.8rem;padding:0.35rem 0.9rem" onclick="return confirm('Mark all as read?')">
            <i class="fas fa-check-double me-1"></i> Mark All Read
        </a>
    </div>

    <div class="admin-content">
        <div class="row gy-4">
            <!-- Message List -->
            <div class="col-lg-<?= $viewMsg ? '4' : '12' ?>">
                <div class="glass-card p-0" style="overflow:hidden">
                    <?php if (empty($messages)): ?>
                    <div class="p-5 text-center" style="color:var(--text-muted)">
                        <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity:0.2"></i>
                        No messages yet.
                    </div>
                    <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                    <a href="?view=<?= $msg['id'] ?>" class="d-block text-decoration-none" style="border-bottom:1px solid var(--glass-border);padding:1rem 1.2rem;background:<?= !$msg['is_read'] ? 'rgba(0,212,255,0.03)' : 'transparent' ?>;transition:background 0.2s"
                        onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='<?= !$msg['is_read'] ? 'rgba(0,212,255,0.03)' : 'transparent' ?>'">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div style="flex:1;min-width:0">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!$msg['is_read']): ?>
                                    <span style="width:7px;height:7px;background:var(--cyan);border-radius:50%;flex-shrink:0"></span>
                                    <?php endif; ?>
                                    <span style="font-weight:<?= $msg['is_read'] ? '400' : '600' ?>;color:<?= $msg['is_read'] ? 'var(--text-muted)' : '#fff' ?>;font-size:0.9rem"><?= htmlspecialchars($msg['name']) ?></span>
                                </div>
                                <div style="font-size:0.78rem;color:var(--cyan);margin:2px 0"><?= htmlspecialchars($msg['subject'] ?? 'General Inquiry') ?></div>
                                <div style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars(substr($msg['message'], 0, 80)) ?>...</div>
                            </div>
                            <div style="text-align:right;flex-shrink:0">
                                <div style="font-size:0.72rem;color:var(--text-muted)"><?= timeAgo($msg['created_at']) ?></div>
                                <a href="?delete=<?= $msg['id'] ?>" class="action-btn delete mt-1 d-inline-flex" title="Delete" onclick="return confirm('Delete this message?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Message Detail -->
            <?php if ($viewMsg): ?>
            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 style="margin:0;font-size:1.1rem"><?= htmlspecialchars($viewMsg['name']) ?></h5>
                            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px">
                                <?php if ($viewMsg['phone']): ?><i class="fas fa-phone me-1" style="color:var(--cyan)"></i><a href="tel:<?= htmlspecialchars($viewMsg['phone']) ?>" style="color:var(--text-muted);text-decoration:none"><?= htmlspecialchars($viewMsg['phone']) ?></a> &nbsp;<?php endif; ?>
                                <?php if ($viewMsg['email']): ?><i class="fas fa-envelope me-1" style="color:var(--cyan)"></i><a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>" style="color:var(--text-muted);text-decoration:none"><?= htmlspecialchars($viewMsg['email']) ?></a><?php endif; ?>
                            </div>
                        </div>
                        <div style="font-size:0.78rem;color:var(--text-muted)"><?= date('M d, Y · H:i', strtotime($viewMsg['created_at'])) ?></div>
                    </div>
                    <div style="font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">Subject: <?= htmlspecialchars($viewMsg['subject'] ?? 'General Inquiry') ?></div>
                    <div class="cyber-line" style="margin:1rem 0"></div>
                    <div style="color:var(--text);line-height:1.8;white-space:pre-wrap"><?= htmlspecialchars($viewMsg['message']) ?></div>
                    <div class="cyber-line" style="margin:1.5rem 0"></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($viewMsg['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($viewMsg['phone']) ?>" class="btn-hero-primary" style="font-size:0.82rem;padding:0.5rem 1.2rem">
                            <i class="fas fa-phone-alt"></i> Call <?= htmlspecialchars($viewMsg['phone']) ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($viewMsg['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>" class="btn-hero-secondary" style="font-size:0.82rem;padding:0.5rem 1.2rem">
                            <i class="fas fa-reply"></i> Reply by Email
                        </a>
                        <?php endif; ?>
                        <?php if ($viewMsg['phone']): ?>
                        <a href="https://wa.me/251<?= ltrim(htmlspecialchars($viewMsg['phone']), '0') ?>" target="_blank" class="btn-outline-glow" style="border-color:#25D366;color:#25D366 !important;font-size:0.82rem;padding:0.5rem 1rem">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <?php endif; ?>
                        <a href="?delete=<?= $viewMsg['id'] ?>" class="btn-outline-glow" style="border-color:var(--pink);color:var(--pink) !important;font-size:0.82rem;padding:0.5rem 1rem" onclick="return confirm('Delete?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
