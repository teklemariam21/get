<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $reportId = (int)($_POST['report_id'] ?? 0);
    $action   = $_POST['action'] ?? '';
    if ($reportId && in_array($action, ['resolved','dismissed'])) {
        $db->prepare("UPDATE reports SET status=? WHERE id=?")->execute([$action, $reportId]);
        if ($action === 'resolved') {
            $reportedId = (int)($_POST['reported_id'] ?? 0);
            if ($reportedId) {
                $db->prepare("UPDATE users SET is_banned=1, ban_reason='Reported & Confirmed Violation' WHERE id=?")->execute([$reportedId]);
            }
        }
        flash('success', 'Report ' . $action . '.');
    }
    redirect(SITE_URL . '/admin/reports.php');
}

$status = $_GET['status'] ?? 'pending';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage= 15;
$offset = ($page - 1) * $perPage;

$statusWhere = $status !== 'all' ? "AND r.status = ?" : "";
$params      = $status !== 'all' ? [$status] : [];

$countStmt = $db->prepare("SELECT COUNT(*) FROM reports r WHERE 1=1 $statusWhere");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $db->prepare("
    SELECT r.*, u.username AS reporter, u.profile_photo AS reporter_photo, u.gender AS reporter_gender,
           v.username AS reported_user, v.profile_photo AS reported_photo, v.gender AS reported_gender
    FROM reports r
    JOIN users u ON u.id = r.reporter_id
    JOIN users v ON v.id = r.reported_id
    WHERE 1=1 $statusWhere
    ORDER BY r.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$reports = $stmt->fetchAll();

$counts = $db->query("SELECT status, COUNT(*) as c FROM reports GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Reports';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content flex-grow-1">
    <div class="admin-topbar">
        <h6 class="mb-0 fw-700"><i class="bi bi-flag me-2"></i>User Reports</h6>
    </div>
    <div class="container-fluid p-4">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Status tabs -->
        <ul class="nav nav-pills mb-3 gap-2">
            <?php foreach (['pending'=>'Pending','reviewed'=>'Reviewed','resolved'=>'Resolved','dismissed'=>'Dismissed','all'=>'All'] as $s => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= $status===$s?'active':'' ?> fw-600 px-3" href="?status=<?= $s ?>">
                    <?= $label ?>
                    <?php if (isset($counts[$s])): ?>
                    <span class="badge bg-<?= $s==='pending'?'danger':'secondary' ?> ms-1"><?= $counts[$s] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <?php if (empty($reports)): ?>
                <div class="text-center py-5 text-muted"><i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>No <?= $status ?> reports</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.83rem">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Reporter</th>
                                <th>Reported User</th>
                                <th>Reason</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $rep): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= getProfilePhoto($rep['reporter_photo'], $rep['reporter_gender']) ?>" class="rounded-circle" width="30" height="30" style="object-fit:cover" alt="">
                                        <a href="?action=view&id=<?= $rep['reporter_id'] ?>" class="text-decoration-none fw-600"><?= htmlspecialchars($rep['reporter']) ?></a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= getProfilePhoto($rep['reported_photo'], $rep['reported_gender']) ?>" class="rounded-circle" width="30" height="30" style="object-fit:cover" alt="">
                                        <a href="<?= SITE_URL ?>/admin/users.php?action=view&id=<?= $rep['reported_id'] ?>" class="text-decoration-none fw-600"><?= htmlspecialchars($rep['reported_user']) ?></a>
                                    </div>
                                </td>
                                <td><span class="badge bg-danger-subtle text-danger fw-600"><?= htmlspecialchars(ucwords(str_replace('_',' ',$rep['reason']))) ?></span></td>
                                <td class="text-muted" style="max-width:200px">
                                    <span title="<?= htmlspecialchars($rep['details'] ?? '') ?>" style="cursor:help">
                                        <?= htmlspecialchars(mb_substr($rep['details'] ?? '—', 0, 60)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= match($rep['status']) { 'pending'=>'warning text-dark', 'resolved'=>'success', 'dismissed'=>'secondary', default=>'info' } ?>">
                                        <?= ucfirst($rep['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= date('M d', strtotime($rep['created_at'])) ?></td>
                                <td class="pe-4">
                                    <?php if ($rep['status'] === 'pending'): ?>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                                        <input type="hidden" name="reported_id" value="<?= $rep['reported_id'] ?>">
                                        <button type="submit" name="action" value="resolved" class="btn btn-sm btn-danger fw-600" onclick="return confirm('Ban reported user and resolve?')">
                                            Ban & Resolve
                                        </button>
                                        <button type="submit" name="action" value="dismissed" class="btn btn-sm btn-outline-secondary fw-600">
                                            Dismiss
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted small"><?= ucfirst($rep['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pages > 1): ?>
        <nav class="mt-3"><ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i===$page?'active':'' ?>">
                <a class="page-link" href="?status=<?= $status ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
