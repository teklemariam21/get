<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$db = getDB();

$stmt = $db->prepare("
    SELECT u.*, TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) AS age, MAX(pv.viewed_at) AS viewed_at, COUNT(*) AS view_count
    FROM profile_views pv
    JOIN users u ON u.id = pv.viewer_id
    WHERE pv.profile_id = ? AND u.is_active = 1 AND u.id != ?
    GROUP BY u.id
    ORDER BY viewed_at DESC
    LIMIT 50
");
$stmt->execute([$currentUser['id'], $currentUser['id']]);
$viewers = $stmt->fetchAll();

$pageTitle = 'Who Viewed My Profile';
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="mb-4">
        <h4 class="fw-800 mb-0"><i class="bi bi-eye text-primary me-2"></i>Who Viewed My Profile</h4>
        <p class="text-muted small"><?= count($viewers) ?> recent visitor<?= count($viewers) !== 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($viewers)): ?>
    <div class="text-center py-5">
        <i class="bi bi-eye-slash" style="font-size:3.5rem;opacity:.25"></i>
        <h5 class="mt-3">No views yet</h5>
        <p class="text-muted">Add a great photo and bio to get more profile visits!</p>
        <a href="<?= SITE_URL ?>/edit-profile.php" class="btn btn-primary btn-sm fw-600">Edit Profile</a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($viewers as $viewer): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="member-card">
                <div class="member-photo-wrap">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $viewer['id'] ?>">
                        <img src="<?= getProfilePhoto($viewer['profile_photo'], $viewer['gender']) ?>" alt="<?= htmlspecialchars($viewer['username']) ?>">
                    </a>
                    <div class="member-badge">
                        <?php if (isOnline($viewer['last_active'])): ?><span class="badge-online">Online</span><?php endif; ?>
                    </div>
                    <div class="photo-overlay">
                        <div class="member-actions">
                            <button class="btn btn-like btn-sm <?= hasLiked($currentUser['id'], $viewer['id']) ? 'liked' : '' ?>" data-user-id="<?= $viewer['id'] ?>">
                                <i class="bi bi-heart<?= hasLiked($currentUser['id'], $viewer['id']) ? '-fill' : '' ?>"></i>
                            </button>
                            <a href="<?= SITE_URL ?>/messages.php?user=<?= $viewer['id'] ?>" class="btn btn-light btn-sm">
                                <i class="bi bi-chat"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $viewer['id'] ?>" class="member-name"><?= htmlspecialchars($viewer['username']) ?></a>
                    <div class="member-meta">
                        <span><?= $viewer['age'] ?> yrs</span>
                        <?php if ($viewer['city']): ?><span><i class="bi bi-geo-alt"></i><?= htmlspecialchars($viewer['city']) ?></span><?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size:.7rem">
                        Viewed <?= $viewer['view_count'] > 1 ? $viewer['view_count'] . ' times · ' : '' ?><?= timeAgo($viewer['viewed_at']) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
