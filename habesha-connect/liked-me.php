<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$db = getDB();

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

if (!$currentUser['is_premium']) {
    // Free users see blurred count only
    $likeCount = (int)$db->prepare("SELECT COUNT(*) FROM likes WHERE to_user_id=?")->execute([$currentUser['id']]) ? $db->query("SELECT COUNT(*) FROM likes WHERE to_user_id={$currentUser['id']}")->fetchColumn() : 0;
    $users = [];
} else {
    $stmt = $db->prepare("
        SELECT u.*, TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) AS age, l.created_at AS liked_at
        FROM likes l JOIN users u ON u.id = l.from_user_id
        WHERE l.to_user_id = ? AND u.is_active = 1
        ORDER BY l.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute([$currentUser['id']]);
    $users     = $stmt->fetchAll();
    $likeCount = (int)$db->query("SELECT COUNT(*) FROM likes WHERE to_user_id={$currentUser['id']}")->fetchColumn();
}

$pages = ceil($likeCount / $perPage);
$pageTitle = 'Who Liked Me';
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-800 mb-0"><i class="bi bi-heart-fill text-danger me-2"></i>Who Liked Me</h4>
            <p class="text-muted small mb-0"><?= $likeCount ?> people liked your profile</p>
        </div>
        <?php if (!$currentUser['is_premium']): ?>
        <a href="<?= SITE_URL ?>/subscription.php" class="btn btn-warning fw-700">
            <i class="bi bi-star-fill me-1"></i>Go Premium to See All
        </a>
        <?php endif; ?>
    </div>

    <?php if (!$currentUser['is_premium']): ?>
    <div class="row g-3">
        <?php for ($i = 0; $i < min($likeCount, 6); $i++): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="member-card">
                <div class="member-photo-wrap position-relative">
                    <div style="aspect-ratio:3/4;background:linear-gradient(135deg,#1a6e3c,#13512c);display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-lock-fill text-white" style="font-size:2rem;opacity:.5"></i>
                    </div>
                    <div class="position-absolute inset-0 d-flex align-items-center justify-content-center" style="background:rgba(0,0,0,.3)">
                        <div class="text-center text-white">
                            <i class="bi bi-eye-slash fs-1"></i>
                            <div class="small mt-1">Upgrade to see</div>
                        </div>
                    </div>
                </div>
                <div class="member-info text-center">
                    <div class="fw-600 text-muted">Hidden Profile</div>
                    <a href="<?= SITE_URL ?>/subscription.php" class="btn btn-warning btn-sm fw-600 mt-2 w-100">
                        <i class="bi bi-unlock me-1"></i>Unlock
                    </a>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="mt-5 text-center">
        <div class="pricing-card featured mx-auto p-4" style="max-width:400px">
            <h5 class="fw-800"><i class="bi bi-star-fill text-warning me-2"></i>Go Premium</h5>
            <p class="text-muted">See who liked you and get unlimited messaging</p>
            <a href="<?= SITE_URL ?>/subscription.php" class="btn btn-warning fw-700 px-4">Upgrade Now – From 299 ETB/month</a>
        </div>
    </div>

    <?php else: ?>

    <?php if (empty($users)): ?>
    <div class="text-center py-5">
        <div style="font-size:4rem">❤️</div>
        <h5 class="mt-3">No likes yet</h5>
        <p class="text-muted">Complete your profile and add photos to attract more attention!</p>
        <a href="<?= SITE_URL ?>/edit-profile.php" class="btn btn-primary btn-sm fw-600">Complete Profile</a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($users as $member): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="member-card">
                <div class="member-photo-wrap">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>">
                        <img src="<?= getProfilePhoto($member['profile_photo'], $member['gender']) ?>" alt="<?= htmlspecialchars($member['username']) ?>">
                    </a>
                    <div class="member-badge">
                        <?php if (isOnline($member['last_active'])): ?><span class="badge-online">Online</span><?php endif; ?>
                    </div>
                    <div class="photo-overlay">
                        <div class="member-actions">
                            <button class="btn btn-like btn-sm <?= hasLiked($currentUser['id'], $member['id']) ? 'liked' : '' ?>" data-user-id="<?= $member['id'] ?>">
                                <i class="bi bi-heart<?= hasLiked($currentUser['id'], $member['id']) ? '-fill' : '' ?>"></i>
                            </button>
                            <a href="<?= SITE_URL ?>/messages.php?user=<?= $member['id'] ?>" class="btn btn-light btn-sm">
                                <i class="bi bi-chat"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>" class="member-name"><?= htmlspecialchars($member['username']) ?></a>
                    <div class="member-meta">
                        <span><?= $member['age'] ?> yrs</span>
                        <?php if ($member['city']): ?><span><i class="bi bi-geo-alt"></i><?= htmlspecialchars($member['city']) ?></span><?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size:.7rem">Liked you <?= timeAgo($member['liked_at']) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
    <nav class="mt-4"><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
