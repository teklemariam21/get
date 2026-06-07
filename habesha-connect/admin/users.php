<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db     = getDB();
$action = $_GET['action'] ?? '';
$userId = (int)($_GET['id'] ?? 0);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $act = $_POST['action'] ?? '';
    $uid = (int)($_POST['user_id'] ?? 0);

    if ($act === 'ban' && $uid) {
        $reason = sanitize($_POST['ban_reason'] ?? 'Violation of terms');
        $db->prepare("UPDATE users SET is_banned=1, ban_reason=? WHERE id=?")->execute([$reason, $uid]);
        flash('success', 'User banned.');
    } elseif ($act === 'unban' && $uid) {
        $db->prepare("UPDATE users SET is_banned=0, ban_reason=NULL WHERE id=?")->execute([$uid]);
        flash('success', 'User unbanned.');
    } elseif ($act === 'verify' && $uid) {
        $db->prepare("UPDATE users SET is_verified=1 WHERE id=?")->execute([$uid]);
        flash('success', 'User verified.');
    } elseif ($act === 'premium' && $uid) {
        $db->prepare("UPDATE users SET is_premium=1, premium_expires=DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id=?")->execute([$uid]);
        flash('success', 'Premium granted for 30 days.');
    } elseif ($act === 'delete' && $uid) {
        $db->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$uid]);
        flash('success', 'User deactivated.');
    }
    redirect(SITE_URL . '/admin/users.php');
}

// Search/filter
$search  = sanitize($_GET['search'] ?? '');
$filter  = $_GET['filter'] ?? 'all';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]    = "(username LIKE ? OR email LIKE ? OR city LIKE ?)";
    $kw         = '%' . $search . '%';
    $params[]   = $kw; $params[] = $kw; $params[] = $kw;
}

if ($filter === 'premium') $where[] = "is_premium = 1";
elseif ($filter === 'banned') $where[] = "is_banned = 1";
elseif ($filter === 'unverified') $where[] = "email_verified = 0";
elseif ($filter === 'online') $where[] = "last_active > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";

$whereStr = implode(' AND ', $where);
$total    = (int)$db->prepare("SELECT COUNT(*) FROM users WHERE $whereStr")->execute($params) ?
            $db->query("SELECT COUNT(*) FROM users WHERE $whereStr" . (!empty($params) ? " LIMIT 9999" : ''))->fetchColumn() : 0;

// Better count using prepared statement
$countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereStr");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = ceil($total / $perPage);

$userStmt = $db->prepare("
    SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
    FROM users WHERE $whereStr
    ORDER BY created_at DESC LIMIT $perPage OFFSET $offset
");
$userStmt->execute($params);
$users = $userStmt->fetchAll();

// View single user
$viewUser = null;
if ($action === 'view' && $userId) {
    $s = $db->prepare("SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age FROM users WHERE id=?");
    $s->execute([$userId]);
    $viewUser = $s->fetch();
}

$pageTitle = 'Manage Members';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content flex-grow-1">
    <div class="admin-topbar">
        <h6 class="mb-0 fw-700"><i class="bi bi-people me-2"></i>Manage Members</h6>
        <span class="text-muted small"><?= number_format($total) ?> total members</span>
    </div>
    <div class="container-fluid p-4">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($viewUser): ?>
        <!-- Single User View -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                <h6 class="fw-700 mb-0">Member Details</h6>
                <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-sm btn-outline-secondary">← Back to list</a>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-3 text-center">
                        <img src="<?= getProfilePhoto($viewUser['profile_photo'], $viewUser['gender']) ?>"
                             class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover" alt="">
                        <h5 class="fw-700"><?= htmlspecialchars($viewUser['username']) ?></h5>
                        <div class="mb-2">
                            <?php if ($viewUser['is_banned']): ?>
                            <span class="badge bg-danger">Banned</span>
                            <?php elseif ($viewUser['is_premium']): ?>
                            <span class="badge bg-warning text-dark">Premium</span>
                            <?php else: ?>
                            <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                            <?php if ($viewUser['is_verified']): ?>
                            <span class="badge bg-primary">Verified</span>
                            <?php endif; ?>
                        </div>
                        <form method="POST" class="d-flex flex-column gap-2">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="user_id" value="<?= $viewUser['id'] ?>">
                            <?php if ($viewUser['is_banned']): ?>
                            <button type="submit" name="action" value="unban" class="btn btn-sm btn-success fw-600">Unban User</button>
                            <?php else: ?>
                            <div class="input-group input-group-sm mb-1">
                                <input type="text" class="form-control" name="ban_reason" placeholder="Ban reason" value="Violation of terms">
                                <button type="submit" name="action" value="ban" class="btn btn-danger fw-600" onclick="return confirm('Ban this user?')">Ban</button>
                            </div>
                            <?php endif; ?>
                            <?php if (!$viewUser['is_verified']): ?>
                            <button type="submit" name="action" value="verify" class="btn btn-sm btn-outline-primary fw-600">Verify Profile</button>
                            <?php endif; ?>
                            <?php if (!$viewUser['is_premium']): ?>
                            <button type="submit" name="action" value="premium" class="btn btn-sm btn-warning fw-600">Grant Premium</button>
                            <?php endif; ?>
                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger fw-600" onclick="return confirm('Deactivate this user?')">Deactivate</button>
                        </form>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <?php
                            $details = [
                                ['Email', $viewUser['email']], ['Age', $viewUser['age'] . ' years'],
                                ['Gender', ucfirst($viewUser['gender'])], ['City', $viewUser['city'] ?? '—'],
                                ['Ethnicity', $viewUser['ethnicity'] ?? '—'], ['Religion', $viewUser['religion'] ?? '—'],
                                ['Education', $viewUser['education'] ?? '—'], ['Occupation', $viewUser['occupation'] ?? '—'],
                                ['Joined', date('M d, Y', strtotime($viewUser['created_at']))],
                                ['Last Active', $viewUser['last_active'] ? timeAgo($viewUser['last_active']) : 'Never'],
                                ['Profile Views', number_format($viewUser['profile_views'])],
                                ['Premium Expires', $viewUser['premium_expires'] ? date('M d, Y', strtotime($viewUser['premium_expires'])) : 'N/A'],
                            ];
                            foreach ($details as [$label, $val]): ?>
                            <div class="col-md-4">
                                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.3px"><?= $label ?></div>
                                <div class="fw-600 small"><?= htmlspecialchars($val) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($viewUser['about_me']): ?>
                        <div class="mt-3">
                            <div class="text-muted small text-uppercase">About Me</div>
                            <p class="small mt-1"><?= htmlspecialchars(mb_substr($viewUser['about_me'], 0, 300)) ?>...</p>
                        </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="<?= SITE_URL ?>/profile.php?id=<?= $viewUser['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Public Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>

        <!-- Filter & Search Bar -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="search" placeholder="Search by name, email, city..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="filter">
                            <option value="all"        <?= $filter==='all'?'selected':'' ?>>All Members</option>
                            <option value="premium"    <?= $filter==='premium'?'selected':'' ?>>Premium Only</option>
                            <option value="banned"     <?= $filter==='banned'?'selected':'' ?>>Banned</option>
                            <option value="unverified" <?= $filter==='unverified'?'selected':'' ?>>Unverified</option>
                            <option value="online"     <?= $filter==='online'?'selected':'' ?>>Online Now</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary fw-600"><i class="bi bi-filter me-1"></i>Filter</button>
                        <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline-secondary fw-600 ms-1">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.83rem">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Member</th>
                                <th>Gender</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Last Active</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= getProfilePhoto($u['profile_photo'], $u['gender']) ?>"
                                             class="rounded-circle" width="34" height="34" style="object-fit:cover" alt="">
                                        <div>
                                            <div class="fw-600"><?= htmlspecialchars($u['username']) ?>
                                                <?php if ($u['is_verified']): ?><i class="bi bi-patch-check-fill text-primary" style="font-size:.7rem"></i><?php endif; ?>
                                            </div>
                                            <div class="text-muted" style="font-size:.7rem"><?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= ucfirst($u['gender']) ?>, <?= $u['age'] ?>y</td>
                                <td class="text-muted"><?= htmlspecialchars($u['city'] ?? '—') ?></td>
                                <td>
                                    <?php if ($u['is_banned']): ?>
                                    <span class="badge bg-danger">Banned</span>
                                    <?php elseif (!$u['is_active']): ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                    <?php elseif ($u['is_premium']): ?>
                                    <span class="badge bg-warning text-dark">Premium</span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td class="text-muted">
                                    <?php if (isOnline($u['last_active'])): ?>
                                    <span class="text-success fw-600">Online</span>
                                    <?php else: ?>
                                    <?= $u['last_active'] ? timeAgo($u['last_active']) : 'Never' ?>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex gap-1">
                                        <a href="?action=view&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <?php if ($u['is_banned']): ?>
                                            <button type="submit" name="action" value="unban" class="btn btn-sm btn-success" title="Unban"><i class="bi bi-unlock"></i></button>
                                            <?php else: ?>
                                            <input type="hidden" name="ban_reason" value="Violation of terms">
                                            <button type="submit" name="action" value="ban" class="btn btn-sm btn-danger" title="Ban" onclick="return confirm('Ban this user?')"><i class="bi bi-slash-circle"></i></button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <nav class="mt-3"><ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= min($pages, 20); $i++): ?>
            <li class="page-item <?= $i===$page?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(['search'=>$search,'filter'=>$filter,'page'=>$i]) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
