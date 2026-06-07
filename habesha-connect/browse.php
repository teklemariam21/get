<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();

$filters = [
    'gender'     => $_GET['gender'] ?? ($currentUser['seeking'] !== 'both' ? $currentUser['seeking'] : ''),
    'city'       => $_GET['city'] ?? '',
    'ethnicity'  => $_GET['ethnicity'] ?? '',
    'religion'   => $_GET['religion'] ?? '',
    'min_age'    => $_GET['min_age'] ?? '',
    'max_age'    => $_GET['max_age'] ?? '',
    'has_photo'  => !empty($_GET['has_photo']),
    'online_only'=> !empty($_GET['online_only']),
    'sort'       => $_GET['sort'] ?? 'newest',
];

$page   = max(1, (int)($_GET['page'] ?? 1));
$result = browseUsers($filters, $page, 12);
$users  = $result['users'];
$total  = $result['total'];
$pages  = $result['pages'];

// Build current liked IDs for UI
$db = getDB();
$likedIds = $db->prepare("SELECT to_user_id FROM likes WHERE from_user_id = ?");
$likedIds->execute([$currentUser['id']]);
$likedSet = array_column($likedIds->fetchAll(), 'to_user_id');

$pageTitle = 'Browse Members';
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid py-4" style="max-width:1400px">
    <div class="row g-4">

        <!-- ======= SIDEBAR FILTERS ======= -->
        <div class="col-lg-3 col-xl-2">
            <div class="filter-sidebar">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-700 mb-0">Filter Members</h6>
                    <a href="<?= SITE_URL ?>/browse.php" class="text-muted small">Reset</a>
                </div>
                <form method="GET" id="filterForm">
                    <div class="mb-3">
                        <div class="section-label">Gender</div>
                        <select class="form-select form-select-sm" name="gender">
                            <option value="">All</option>
                            <option value="female" <?= $filters['gender']==='female'?'selected':'' ?>>Women</option>
                            <option value="male"   <?= $filters['gender']==='male'?'selected':'' ?>>Men</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="section-label">Age Range</div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" class="form-control form-control-sm" name="min_age" placeholder="Min"
                                   value="<?= (int)($filters['min_age']?:18) ?>" min="18" max="80">
                            <span class="text-muted small">–</span>
                            <input type="number" class="form-control form-control-sm" name="max_age" placeholder="Max"
                                   value="<?= (int)($filters['max_age']?:60) ?>" min="18" max="80">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="section-label">City</div>
                        <select class="form-select form-select-sm" name="city">
                            <option value="">All Cities</option>
                            <?php foreach (ETHIOPIAN_CITIES as $city => $region): ?>
                            <option value="<?= $city ?>" <?= $filters['city']===$city?'selected':'' ?>><?= $city ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="section-label">Ethnicity</div>
                        <select class="form-select form-select-sm" name="ethnicity">
                            <option value="">All</option>
                            <?php foreach (ETHIOPIAN_ETHNICITIES as $eth): ?>
                            <option value="<?= $eth ?>" <?= $filters['ethnicity']===$eth?'selected':'' ?>><?= $eth ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="section-label">Religion</div>
                        <select class="form-select form-select-sm" name="religion">
                            <option value="">All</option>
                            <?php foreach (ETHIOPIAN_RELIGIONS as $rel): ?>
                            <option value="<?= $rel ?>" <?= $filters['religion']===$rel?'selected':'' ?>><?= $rel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="section-label">Sort By</div>
                        <select class="form-select form-select-sm" name="sort">
                            <option value="newest"  <?= $filters['sort']==='newest'?'selected':'' ?>>Newest</option>
                            <option value="online"  <?= $filters['sort']==='online'?'selected':'' ?>>Online Now</option>
                            <option value="popular" <?= $filters['sort']==='popular'?'selected':'' ?>>Most Popular</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="has_photo" id="hasPhoto" value="1" <?= $filters['has_photo']?'checked':'' ?>>
                            <label class="form-check-label small fw-600" for="hasPhoto">Has Photo</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="online_only" id="onlineOnly" value="1" <?= $filters['online_only']?'checked':'' ?>>
                            <label class="form-check-label small fw-600" for="onlineOnly">Online Now</label>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm fw-600">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ======= MAIN CONTENT ======= -->
        <div class="col-lg-9 col-xl-10">
            <!-- Header bar -->
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-700 mb-0">Browse Members</h5>
                    <small class="text-muted"><?= number_format($total) ?> members found</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                        <i class="bi bi-funnel me-1"></i>Filters
                    </button>
                </div>
            </div>

            <?php if (empty($users)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people" style="font-size:3rem;opacity:.3"></i>
                <h5 class="mt-3">No members found</h5>
                <p>Try adjusting your filters</p>
                <a href="<?= SITE_URL ?>/browse.php" class="btn btn-outline-primary btn-sm">Clear Filters</a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($users as $member): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="member-card">
                        <div class="member-photo-wrap">
                            <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>">
                                <img src="<?= getProfilePhoto($member['profile_photo'], $member['gender']) ?>"
                                     alt="<?= htmlspecialchars($member['username']) ?>"
                                     loading="lazy">
                            </a>
                            <div class="member-badge">
                                <?php if (isOnline($member['last_active'])): ?>
                                <span class="badge-online">Online</span>
                                <?php endif; ?>
                                <?php if ($member['is_premium']): ?>
                                <span class="badge-premium"><i class="bi bi-star-fill"></i> VIP</span>
                                <?php endif; ?>
                                <?php if ($member['is_verified']): ?>
                                <span class="badge-verified"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                <?php endif; ?>
                            </div>
                            <div class="photo-overlay">
                                <div class="member-actions">
                                    <a href="<?= SITE_URL ?>/messages.php?user=<?= $member['id'] ?>" class="btn btn-light btn-sm" title="Send Message">
                                        <i class="bi bi-chat"></i>
                                    </a>
                                    <button class="btn btn-like btn-sm <?= in_array($member['id'], $likedSet) ? 'liked' : '' ?>"
                                            data-user-id="<?= $member['id'] ?>" title="Like">
                                        <i class="bi bi-heart<?= in_array($member['id'], $likedSet) ? '-fill' : '' ?>"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="member-info">
                            <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>" class="member-name">
                                <?= htmlspecialchars($member['username']) ?>
                                <?php if ($member['is_verified']): ?>
                                <i class="bi bi-patch-check-fill text-primary" style="font-size:.75rem"></i>
                                <?php endif; ?>
                            </a>
                            <div class="member-meta">
                                <span><?= $member['age'] ?> yrs</span>
                                <?php if ($member['city']): ?>
                                <span><i class="bi bi-geo-alt"></i><?= htmlspecialchars($member['city']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($member['ethnicity'] || $member['religion']): ?>
                            <div class="member-tags">
                                <?php if ($member['ethnicity']): ?><span class="member-tag"><?= htmlspecialchars($member['ethnicity']) ?></span><?php endif; ?>
                                <?php if ($member['religion']): ?><span class="member-tag"><?= htmlspecialchars($member['religion']) ?></span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
