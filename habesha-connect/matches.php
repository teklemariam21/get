<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$db = getDB();

$stmt = $db->prepare("
    SELECT u.*, TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) AS age, m.created_at AS matched_at
    FROM matches m
    JOIN users u ON u.id = IF(m.user1_id = :uid, m.user2_id, m.user1_id)
    WHERE (m.user1_id = :uid2 OR m.user2_id = :uid3)
      AND u.is_active = 1
    ORDER BY m.created_at DESC
");
$stmt->execute([':uid' => $currentUser['id'], ':uid2' => $currentUser['id'], ':uid3' => $currentUser['id']]);
$matches = $stmt->fetchAll();

$pageTitle = 'My Matches';
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-800 mb-0"><i class="bi bi-hearts text-danger me-2"></i>My Matches</h4>
            <p class="text-muted small mb-0"><?= count($matches) ?> mutual match<?= count($matches) !== 1 ? 'es' : '' ?></p>
        </div>
        <a href="<?= SITE_URL ?>/browse.php" class="btn btn-outline-primary btn-sm fw-600">
            <i class="bi bi-people me-1"></i>Browse More
        </a>
    </div>

    <?php if (empty($matches)): ?>
    <div class="text-center py-5">
        <div style="font-size:4rem">💛</div>
        <h5 class="mt-3 fw-700">No matches yet</h5>
        <p class="text-muted">When you and someone both like each other, you'll appear here.</p>
        <a href="<?= SITE_URL ?>/browse.php" class="btn btn-primary fw-600 px-4 mt-2">
            <i class="bi bi-people me-2"></i>Start Browsing
        </a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($matches as $match): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="member-card position-relative">
                <!-- Match ribbon -->
                <div class="position-absolute" style="top:10px;right:10px;z-index:5">
                    <span class="badge rounded-pill" style="background:linear-gradient(135deg,#ef4444,#be123c);font-size:.65rem"><i class="bi bi-hearts me-1"></i>Match</span>
                </div>
                <div class="member-photo-wrap">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $match['id'] ?>">
                        <img src="<?= getProfilePhoto($match['profile_photo'], $match['gender']) ?>" alt="<?= htmlspecialchars($match['username']) ?>">
                    </a>
                    <div class="member-badge">
                        <?php if (isOnline($match['last_active'])): ?>
                        <span class="badge-online">Online</span>
                        <?php endif; ?>
                    </div>
                    <div class="photo-overlay">
                        <div class="member-actions">
                            <a href="<?= SITE_URL ?>/messages.php?user=<?= $match['id'] ?>" class="btn btn-warning btn-sm fw-600">
                                <i class="bi bi-chat-dots-fill me-1"></i>Chat
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $match['id'] ?>" class="member-name">
                        <?= htmlspecialchars($match['username']) ?>
                    </a>
                    <div class="member-meta">
                        <span><?= $match['age'] ?> yrs</span>
                        <?php if ($match['city']): ?>
                        <span><i class="bi bi-geo-alt"></i><?= htmlspecialchars($match['city']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size:.7rem">Matched <?= timeAgo($match['matched_at']) ?></div>
                    <div class="mt-2">
                        <a href="<?= SITE_URL ?>/messages.php?user=<?= $match['id'] ?>" class="btn btn-sm btn-primary w-100 fw-600">
                            <i class="bi bi-chat-dots me-1"></i>Send Message
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
