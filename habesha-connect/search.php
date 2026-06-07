<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();

$filters = [
    'keyword'    => sanitize($_GET['keyword'] ?? ''),
    'gender'     => $_GET['gender'] ?? '',
    'city'       => $_GET['city'] ?? '',
    'ethnicity'  => $_GET['ethnicity'] ?? '',
    'religion'   => $_GET['religion'] ?? '',
    'min_age'    => (int)($_GET['min_age'] ?? 18),
    'max_age'    => (int)($_GET['max_age'] ?? 60),
    'has_photo'  => !empty($_GET['has_photo']),
    'online_only'=> !empty($_GET['online_only']),
    'sort'       => $_GET['sort'] ?? 'newest',
    'education'  => $_GET['education'] ?? '',
    'marital_status' => $_GET['marital_status'] ?? '',
];

$searched = !empty($_GET['keyword']) || !empty($_GET['gender']) || !empty($_GET['city']) || !empty($_GET['ethnicity']);
$result   = $searched ? browseUsers($filters, 1, 24) : ['users' => [], 'total' => 0, 'pages' => 0];
$users    = $result['users'];
$db       = getDB();
$likedIds = [];
if (!empty($users)) {
    $likedQ = $db->prepare("SELECT to_user_id FROM likes WHERE from_user_id=?");
    $likedQ->execute([$currentUser['id']]);
    $likedIds = array_column($likedQ->fetchAll(), 'to_user_id');
}

$pageTitle = 'Search Members';
?>
<?php include 'includes/header.php'; ?>
<meta name="csrf" content="<?= csrfToken() ?>">
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="mb-4">
        <h4 class="fw-800 mb-1"><i class="bi bi-search-heart me-2 text-primary"></i>Search Members</h4>
        <p class="text-muted small">Use the filters below to find your ideal Ethiopian match</p>
    </div>

    <!-- Search Form -->
    <div class="profile-card p-4 mb-4">
        <form method="GET" id="searchForm">
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <label class="form-label fw-600">Keyword</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="keyword"
                               value="<?= htmlspecialchars($filters['keyword']) ?>"
                               placeholder="Name, occupation, about...">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-600">Gender</label>
                    <select class="form-select" name="gender">
                        <option value="">Any</option>
                        <option value="female" <?= $filters['gender']==='female'?'selected':'' ?>>Women</option>
                        <option value="male"   <?= $filters['gender']==='male'?'selected':'' ?>>Men</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-600">City</label>
                    <select class="form-select" name="city">
                        <option value="">Any</option>
                        <?php foreach (ETHIOPIAN_CITIES as $c => $r): ?>
                        <option value="<?= $c ?>" <?= $filters['city']===$c?'selected':'' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-600">Min Age</label>
                    <input type="number" class="form-control" name="min_age" value="<?= $filters['min_age'] ?>" min="18" max="80">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-600">Max Age</label>
                    <input type="number" class="form-control" name="max_age" value="<?= $filters['max_age'] ?>" min="18" max="80">
                </div>

                <!-- Advanced -->
                <div class="col-12">
                    <button class="btn btn-link text-muted p-0 small fw-600" type="button" data-bs-toggle="collapse" data-bs-target="#advFilters">
                        <i class="bi bi-sliders me-1"></i>Advanced Filters
                    </button>
                </div>
                <div class="col-12 collapse" id="advFilters">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-600">Ethnicity</label>
                            <select class="form-select" name="ethnicity">
                                <option value="">Any</option>
                                <?php foreach (ETHIOPIAN_ETHNICITIES as $e): ?>
                                <option value="<?= $e ?>" <?= $filters['ethnicity']===$e?'selected':'' ?>><?= $e ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Religion</label>
                            <select class="form-select" name="religion">
                                <option value="">Any</option>
                                <?php foreach (ETHIOPIAN_RELIGIONS as $r): ?>
                                <option value="<?= $r ?>" <?= $filters['religion']===$r?'selected':'' ?>><?= $r ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Education</label>
                            <select class="form-select" name="education">
                                <option value="">Any</option>
                                <?php foreach (EDUCATION_LEVELS as $e): ?>
                                <option value="<?= $e ?>" <?= $filters['education']===$e?'selected':'' ?>><?= $e ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Marital Status</label>
                            <select class="form-select" name="marital_status">
                                <option value="">Any</option>
                                <?php foreach (['single','divorced','widowed','separated'] as $ms): ?>
                                <option value="<?= $ms ?>" <?= $filters['marital_status']===$ms?'selected':'' ?>><?= ucfirst($ms) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Sort By</label>
                            <select class="form-select" name="sort">
                                <option value="newest"  <?= $filters['sort']==='newest'?'selected':'' ?>>Newest Members</option>
                                <option value="online"  <?= $filters['sort']==='online'?'selected':'' ?>>Online Now</option>
                                <option value="popular" <?= $filters['sort']==='popular'?'selected':'' ?>>Most Popular</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_photo" value="1" <?= $filters['has_photo']?'checked':'' ?>>
                                <label class="form-check-label small fw-600">Has Photo</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="online_only" value="1" <?= $filters['online_only']?'checked':'' ?>>
                                <label class="form-check-label small fw-600">Online Now</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-700 px-4">
                        <i class="bi bi-search me-2"></i>Search
                    </button>
                    <a href="<?= SITE_URL ?>/search.php" class="btn btn-outline-secondary fw-600">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results -->
    <?php if ($searched): ?>
    <div class="mb-3 fw-600 text-muted">
        Found <strong class="text-dark"><?= $result['total'] ?></strong> member<?= $result['total'] !== 1 ? 's' : '' ?>
        <?= $filters['keyword'] ? ' for "' . htmlspecialchars($filters['keyword']) . '"' : '' ?>
    </div>
    <?php if (empty($users)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-people" style="font-size:3rem;opacity:.3"></i>
        <h5 class="mt-3">No results found</h5>
        <p>Try different search terms or filters</p>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($users as $member): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="member-card">
                <div class="member-photo-wrap">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>">
                        <img src="<?= getProfilePhoto($member['profile_photo'], $member['gender']) ?>" alt="<?= htmlspecialchars($member['username']) ?>" loading="lazy">
                    </a>
                    <div class="member-badge">
                        <?php if (isOnline($member['last_active'])): ?><span class="badge-online">Online</span><?php endif; ?>
                        <?php if ($member['is_premium']): ?><span class="badge-premium"><i class="bi bi-star-fill"></i> VIP</span><?php endif; ?>
                    </div>
                    <div class="photo-overlay">
                        <div class="member-actions">
                            <a href="<?= SITE_URL ?>/messages.php?user=<?= $member['id'] ?>" class="btn btn-light btn-sm"><i class="bi bi-chat"></i></a>
                            <button class="btn btn-like btn-sm <?= in_array($member['id'], $likedIds)?'liked':'' ?>" data-user-id="<?= $member['id'] ?>">
                                <i class="bi bi-heart<?= in_array($member['id'], $likedIds)?'-fill':'' ?>"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>" class="member-name"><?= htmlspecialchars($member['username']) ?></a>
                    <div class="member-meta">
                        <span><?= $member['age'] ?> yrs</span>
                        <?php if ($member['city']): ?><span><i class="bi bi-geo-alt"></i><?= htmlspecialchars($member['city']) ?></span><?php endif; ?>
                    </div>
                    <?php if ($member['about_me']): ?>
                    <p class="text-muted small text-truncate-2 mb-0"><?= htmlspecialchars(mb_substr($member['about_me'], 0, 80)) ?>…</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-search-heart" style="font-size:3.5rem;opacity:.2"></i>
        <h5 class="mt-3">Start your search</h5>
        <p>Use the filters above to find your perfect match</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
