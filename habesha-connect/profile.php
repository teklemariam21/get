<?php
require_once 'includes/functions.php';
requireLogin();

$currentUser = getCurrentUser();
$profileId   = (int)($_GET['id'] ?? $currentUser['id']);
$profile     = getUserById($profileId);

if (!$profile) {
    flash('error', 'Profile not found.');
    redirect(SITE_URL . '/browse.php');
}

if (isBlocked($currentUser['id'], $profileId)) {
    flash('error', 'This profile is unavailable.');
    redirect(SITE_URL . '/browse.php');
}

// Record view (not own profile)
if ($profileId !== $currentUser['id']) {
    recordProfileView($currentUser['id'], $profileId);
}

$age      = getAge($profile['birthdate']);
$isOwn    = ($profileId === $currentUser['id']);
$liked    = !$isOwn && hasLiked($currentUser['id'], $profileId);
$matched  = !$isOwn && isMatch($currentUser['id'], $profileId);
$db       = getDB();

// Photos
$photos = $db->prepare("SELECT * FROM photos WHERE user_id = ? AND is_approved = 1 ORDER BY is_profile DESC, id DESC");
$photos->execute([$profileId]);
$photos = $photos->fetchAll();

$pageTitle = htmlspecialchars($profile['username']) . "'s Profile";
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="row g-4">

        <!-- ======= MAIN PROFILE ======= -->
        <div class="col-lg-8">
            <div class="profile-card mb-4">
                <!-- Cover -->
                <div class="profile-cover">
                    <?php if ($profile['cover_photo']): ?>
                    <img src="<?= getProfilePhoto($profile['cover_photo'], $profile['gender']) ?>" alt="Cover">
                    <?php endif; ?>
                </div>

                <!-- Avatar & Header -->
                <div class="profile-avatar-wrap">
                    <img src="<?= getProfilePhoto($profile['profile_photo'], $profile['gender']) ?>"
                         alt="<?= htmlspecialchars($profile['username']) ?>">
                    <?php if (isOnline($profile['last_active'])): ?>
                    <span class="profile-online-dot"></span>
                    <?php endif; ?>
                </div>

                <div class="profile-header-info d-flex align-items-start justify-content-between flex-wrap gap-3 mt-2">
                    <div>
                        <h4 class="fw-800 mb-1">
                            <?= htmlspecialchars($profile['username']) ?>
                            <?php if ($profile['is_verified']): ?>
                            <i class="bi bi-patch-check-fill text-primary" title="Verified"></i>
                            <?php endif; ?>
                            <?php if ($profile['is_premium']): ?>
                            <span class="badge" style="background:var(--secondary);color:#333;font-size:.7rem"><i class="bi bi-star-fill me-1"></i>Premium</span>
                            <?php endif; ?>
                        </h4>
                        <div class="d-flex flex-wrap gap-3 text-muted small">
                            <span><i class="bi bi-calendar2 me-1"></i><?= $age ?> years old</span>
                            <?php if ($profile['city']): ?>
                            <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($profile['city']) ?></span>
                            <?php endif; ?>
                            <?php if ($profile['religion']): ?>
                            <span><i class="bi bi-stars me-1"></i><?= htmlspecialchars($profile['religion']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-1 small text-muted">
                            <i class="bi bi-clock me-1"></i>
                            <?= isOnline($profile['last_active']) ? '<span class="text-success fw-600">Online Now</span>' : 'Active ' . timeAgo($profile['last_active'] ?? $profile['created_at']) ?>
                        </div>
                    </div>

                    <?php if ($isOwn): ?>
                    <a href="<?= SITE_URL ?>/edit-profile.php" class="btn btn-outline-primary fw-600">
                        <i class="bi bi-pencil me-1"></i>Edit Profile
                    </a>
                    <?php else: ?>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($matched): ?>
                        <a href="<?= SITE_URL ?>/messages.php?user=<?= $profile['id'] ?>" class="btn btn-primary fw-600">
                            <i class="bi bi-chat-dots-fill me-1"></i>Message
                        </a>
                        <?php endif; ?>
                        <button class="btn btn-like fw-600 px-3 <?= $liked ? 'liked' : '' ?>"
                                data-user-id="<?= $profile['id'] ?>">
                            <i class="bi bi-heart<?= $liked ? '-fill' : '' ?> me-1"></i>
                            <?= $liked ? 'Liked' : 'Like' ?>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/messages.php?user=<?= $profile['id'] ?>"><i class="bi bi-chat me-2"></i>Send Message</a></li>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/api/block.php?id=<?= $profile['id'] ?>&csrf=<?= csrfToken() ?>"><i class="bi bi-slash-circle me-2"></i>Block User</a></li>
                                <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/report.php?id=<?= $profile['id'] ?>"><i class="bi bi-flag me-2"></i>Report</a></li>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Match indication -->
                <?php if ($matched && !$isOwn): ?>
                <div class="mx-3 mb-3">
                    <div class="alert alert-success py-2 d-flex align-items-center gap-2 small border-0">
                        <i class="bi bi-hearts fs-4 text-success"></i>
                        <div><strong>You matched!</strong> You both liked each other. Start a conversation!</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- About -->
            <?php if ($profile['about_me']): ?>
            <div class="profile-card p-3 mb-4">
                <h6 class="fw-700 mb-2"><i class="bi bi-person-lines-fill text-success me-2"></i>About Me</h6>
                <p class="text-muted mb-0" style="line-height:1.8"><?= nl2br(htmlspecialchars($profile['about_me'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Ideal Partner -->
            <?php if ($profile['ideal_partner']): ?>
            <div class="profile-card p-3 mb-4">
                <h6 class="fw-700 mb-2"><i class="bi bi-search-heart text-danger me-2"></i>My Ideal Partner</h6>
                <p class="text-muted mb-0" style="line-height:1.8"><?= nl2br(htmlspecialchars($profile['ideal_partner'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Photos -->
            <?php if (!empty($photos)): ?>
            <div class="profile-card p-3 mb-4">
                <h6 class="fw-700 mb-3"><i class="bi bi-images text-primary me-2"></i>Photos (<?= count($photos) ?>)</h6>
                <div class="photo-grid">
                    <?php foreach ($photos as $photo): ?>
                    <div class="photo-grid-item" onclick="openPhotoModal('<?= UPLOAD_URL . htmlspecialchars($photo['filename']) ?>')">
                        <img src="<?= UPLOAD_URL . htmlspecialchars($photo['filename']) ?>"
                             alt="Photo" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ======= SIDEBAR ======= -->
        <div class="col-lg-4">
            <!-- Details -->
            <div class="profile-card p-3 mb-4">
                <h6 class="fw-700 mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Profile Details</h6>
                <div>
                    <?php
                    $details = [
                        ['person', 'Gender', ucfirst($profile['gender'])],
                        ['calendar2', 'Age', $age . ' years old'],
                        ['geo-alt', 'Location', ($profile['city'] ?? '') . ($profile['region'] ? ', ' . $profile['region'] : '')],
                        ['people', 'Ethnicity', $profile['ethnicity']],
                        ['stars', 'Religion', $profile['religion']],
                        ['book', 'Education', $profile['education']],
                        ['briefcase', 'Occupation', $profile['occupation']],
                        ['heart', 'Status', ucwords(str_replace('_', ' ', $profile['marital_status'] ?? ''))],
                        ['rulers', 'Height', $profile['height_cm'] ? $profile['height_cm'] . ' cm' : null],
                        ['person-fill', 'Body Type', $profile['body_type']],
                        ['emoji-smile', 'Smoking', ucfirst($profile['smoking'] ?? '')],
                        ['cup-hot', 'Drinking', ucfirst($profile['drinking'] ?? '')],
                        ['baby', 'Children', ucwords(str_replace('_', ' ', $profile['children'] ?? ''))],
                    ];
                    foreach ($details as [$icon, $label, $value]):
                        if (empty($value)) continue;
                    ?>
                    <div class="info-item">
                        <i class="bi bi-<?= $icon ?>"></i>
                        <div>
                            <div class="info-label"><?= $label ?></div>
                            <div class="info-value"><?= htmlspecialchars($value) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($profile['languages']): ?>
                    <div class="info-item">
                        <i class="bi bi-translate"></i>
                        <div>
                            <div class="info-label">Languages</div>
                            <div class="info-value"><?= htmlspecialchars($profile['languages']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hobbies -->
            <?php if ($profile['hobbies']): ?>
            <div class="profile-card p-3 mb-4">
                <h6 class="fw-700 mb-2"><i class="bi bi-controller text-warning me-2"></i>Hobbies & Interests</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (explode(',', $profile['hobbies']) as $hobby): ?>
                    <span class="member-tag"><?= htmlspecialchars(trim($hobby)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="profile-card p-3">
                <h6 class="fw-700 mb-3"><i class="bi bi-bar-chart text-success me-2"></i>Profile Stats</h6>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="fs-4 fw-800 text-success"><?= number_format($profile['profile_views']) ?></div>
                        <div class="text-muted" style="font-size:.72rem">Views</div>
                    </div>
                    <div class="col-4">
                        <?php $likeCount = (int)$db->prepare("SELECT COUNT(*) FROM likes WHERE to_user_id=?")->execute([$profileId]) ? $db->query("SELECT COUNT(*) FROM likes WHERE to_user_id=$profileId")->fetchColumn() : 0; ?>
                        <div class="fs-4 fw-800 text-danger"><?= $likeCount ?></div>
                        <div class="text-muted" style="font-size:.72rem">Likes</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-800 text-warning"><?= (int)$db->query("SELECT COUNT(*) FROM matches WHERE user1_id=$profileId OR user2_id=$profileId")->fetchColumn() ?></div>
                        <div class="text-muted" style="font-size:.72rem">Matches</div>
                    </div>
                </div>
                <div class="mt-3 border-top pt-2 small text-muted text-center">
                    Member since <?= date('M Y', strtotime($profile['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center">
                <img id="modalPhoto" src="" class="img-fluid rounded-3" style="max-height:80vh" alt="">
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
function openPhotoModal(src) {
    document.getElementById('modalPhoto').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>
