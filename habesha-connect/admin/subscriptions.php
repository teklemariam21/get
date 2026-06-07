<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $subId  = (int)($_POST['sub_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($subId && in_array($action, ['activate','cancel'])) {
        if ($action === 'activate') {
            $sub = $db->prepare("SELECT * FROM subscriptions WHERE id=?");
            $sub->execute([$subId]);
            $sub = $sub->fetch();
            if ($sub) {
                $days = match($sub['plan']) { 'weekly'=>7,'monthly'=>30,'quarterly'=>90,'annual'=>365,default=>30 };
                $db->prepare("UPDATE subscriptions SET status='active',starts_at=NOW(),expires_at=DATE_ADD(NOW(),INTERVAL $days DAY) WHERE id=?")->execute([$subId]);
                $db->prepare("UPDATE users SET is_premium=1,premium_expires=DATE_ADD(NOW(),INTERVAL $days DAY) WHERE id=?")->execute([$sub['user_id']]);
            }
        } elseif ($action === 'cancel') {
            $db->prepare("UPDATE subscriptions SET status='cancelled' WHERE id=?")->execute([$subId]);
        }
        flash('success', 'Subscription updated.');
    }
    redirect(SITE_URL . '/admin/subscriptions.php');
}

$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage= 20;
$offset = ($page - 1) * $perPage;
$filter = $_GET['status'] ?? 'all';
$statusWhere = $filter !== 'all' ? "AND s.status = ?" : "";
$params      = $filter !== 'all' ? [$filter] : [];

$countStmt = $db->prepare("SELECT COUNT(*) FROM subscriptions s WHERE 1=1 $statusWhere");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $db->prepare("
    SELECT s.*, u.username, u.email, u.profile_photo, u.gender
    FROM subscriptions s JOIN users u ON u.id = s.user_id
    WHERE 1=1 $statusWhere
    ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$subs = $stmt->fetchAll();

$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active'")->fetchColumn();

$pageTitle = 'Subscriptions';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content flex-grow-1">
    <div class="admin-topbar">
        <h6 class="mb-0 fw-700"><i class="bi bi-star me-2"></i>Subscriptions</h6>
        <span class="text-muted small">Total Revenue: <strong class="text-success"><?= number_format($totalRevenue) ?> ETB</strong></span>
    </div>
    <div class="container-fluid p-4">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <ul class="nav nav-pills mb-3 gap-2">
            <?php foreach (['all'=>'All','pending'=>'Pending','active'=>'Active','expired'=>'Expired','cancelled'=>'Cancelled'] as $s=>$l): ?>
            <li class="nav-item"><a class="nav-link <?= $filter===$s?'active':'' ?> fw-600 px-3" href="?status=<?= $s ?>"><?= $l ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.83rem">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Member</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Txn Ref</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subs as $sub): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= getProfilePhoto($sub['profile_photo'], $sub['gender']) ?>" class="rounded-circle" width="30" height="30" style="object-fit:cover" alt="">
                                        <div>
                                            <div class="fw-600"><?= htmlspecialchars($sub['username']) ?></div>
                                            <div class="text-muted" style="font-size:.7rem"><?= htmlspecialchars($sub['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-primary"><?= ucfirst($sub['plan']) ?></span></td>
                                <td class="fw-600"><?= number_format($sub['amount']) ?> ETB</td>
                                <td class="text-muted"><?= ucwords(str_replace('_',' ',$sub['payment_method']??'—')) ?></td>
                                <td class="text-muted" style="font-size:.75rem;font-family:monospace"><?= htmlspecialchars(mb_substr($sub['transaction_id']??'—',0,20)) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($sub['status']) { 'active'=>'success','pending'=>'warning text-dark','expired'=>'secondary','cancelled'=>'danger',default=>'secondary' } ?>">
                                        <?= ucfirst($sub['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= $sub['expires_at'] ? date('M d, Y', strtotime($sub['expires_at'])) : '—' ?></td>
                                <td class="pe-4">
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="sub_id" value="<?= $sub['id'] ?>">
                                        <?php if ($sub['status'] === 'pending'): ?>
                                        <button type="submit" name="action" value="activate" class="btn btn-sm btn-success fw-600">Activate</button>
                                        <button type="submit" name="action" value="cancel" class="btn btn-sm btn-outline-danger fw-600">Cancel</button>
                                        <?php elseif ($sub['status'] === 'active'): ?>
                                        <button type="submit" name="action" value="cancel" class="btn btn-sm btn-outline-danger fw-600">Cancel</button>
                                        <?php else: ?>
                                        <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php if ($pages > 1): ?>
        <nav class="mt-3"><ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i===$page?'active':'' ?>">
                <a class="page-link" href="?status=<?= $filter ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
