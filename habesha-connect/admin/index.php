<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

// Stats
$totalUsers    = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$totalMatches  = (int)$db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$totalMessages = (int)$db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$premiumUsers  = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_premium=1 AND is_active=1")->fetchColumn();
$totalRevenue  = (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active'")->fetchColumn();
$newToday      = (int)$db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$onlineNow     = (int)$db->query("SELECT COUNT(*) FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 10 MINUTE)")->fetchColumn();
$pendingReports= (int)$db->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();

// Charts data
$registrations = $db->query("
    SELECT DATE_FORMAT(created_at,'%b %d') as d, COUNT(*) as c
    FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY created_at
")->fetchAll();

// Recent users
$recentUsers = $db->query("
    SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
    FROM users ORDER BY created_at DESC LIMIT 8
")->fetchAll();

// Recent reports
$recentReports = $db->query("
    SELECT r.*, u.username as reporter, v.username as reported_user
    FROM reports r
    JOIN users u ON u.id = r.reporter_id
    JOIN users v ON v.id = r.reported_id
    ORDER BY r.created_at DESC LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard';
$flash     = getFlash();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content flex-grow-1">
    <!-- Topbar -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
            <h6 class="mb-0 fw-700">Dashboard</h6>
        </div>
        <div class="d-flex align-items-center gap-3 text-muted small">
            <span><i class="bi bi-calendar me-1"></i><?= date('D, M d Y') ?></span>
            <span class="d-none d-md-inline"><i class="bi bi-clock me-1"></i><?= date('H:i A') ?></span>
        </div>
    </div>

    <div class="container-fluid p-4">
        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <?php $stats = [
                ['label'=>'Total Members',   'value'=>number_format($totalUsers),   'icon'=>'people-fill',    'color'=>'#1a6e3c', 'bg'=>'#f0fdf4'],
                ['label'=>'Matches Made',    'value'=>number_format($totalMatches), 'icon'=>'hearts',         'color'=>'#dc2626', 'bg'=>'#fef2f2'],
                ['label'=>'Messages Sent',   'value'=>number_format($totalMessages),'icon'=>'chat-dots-fill', 'color'=>'#2563eb', 'bg'=>'#eff6ff'],
                ['label'=>'Premium Members', 'value'=>number_format($premiumUsers), 'icon'=>'star-fill',      'color'=>'#d97706', 'bg'=>'#fffbeb'],
                ['label'=>'Revenue (ETB)',   'value'=>number_format($totalRevenue), 'icon'=>'cash-stack',     'color'=>'#059669', 'bg'=>'#ecfdf5'],
                ['label'=>'New Today',       'value'=>$newToday,                    'icon'=>'person-plus',    'color'=>'#7c3aed', 'bg'=>'#f5f3ff'],
                ['label'=>'Online Now',      'value'=>$onlineNow,                   'icon'=>'wifi',           'color'=>'#0891b2', 'bg'=>'#ecfeff'],
                ['label'=>'Pending Reports', 'value'=>$pendingReports,              'icon'=>'flag-fill',      'color'=>'#dc2626', 'bg'=>'#fef2f2'],
            ];
            foreach ($stats as $s): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="stat-card" style="border-left-color:<?= $s['color'] ?>">
                    <div class="stat-icon" style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>">
                        <i class="bi bi-<?= $s['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $s['value'] ?></div>
                        <div class="stat-label-sm"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 mb-4">
            <!-- Registrations Chart -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                        <h6 class="fw-700 mb-0">New Registrations (Last 7 Days)</h6>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <canvas id="registrationsChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                        <h6 class="fw-700 mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="d-flex flex-column gap-2">
                            <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline-primary fw-600 text-start"><i class="bi bi-people me-2"></i>Manage Members</a>
                            <a href="<?= SITE_URL ?>/admin/reports.php" class="btn btn-outline-danger fw-600 text-start position-relative">
                                <i class="bi bi-flag me-2"></i>View Reports
                                <?php if ($pendingReports): ?><span class="badge bg-danger ms-2"><?= $pendingReports ?></span><?php endif; ?>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/subscriptions.php" class="btn btn-outline-warning fw-600 text-start"><i class="bi bi-star me-2"></i>Subscriptions</a>
                            <a href="<?= SITE_URL ?>/admin/photos.php" class="btn btn-outline-secondary fw-600 text-start"><i class="bi bi-images me-2"></i>Review Photos</a>
                            <a href="<?= SITE_URL ?>/admin/settings.php" class="btn btn-outline-secondary fw-600 text-start"><i class="bi bi-gear me-2"></i>Site Settings</a>
                            <a href="<?= SITE_URL ?>/" target="_blank" class="btn btn-primary fw-600 text-start"><i class="bi bi-box-arrow-up-right me-2"></i>View Live Site</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Members -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3 px-4 pb-0">
                        <h6 class="fw-700 mb-0">Recent Members</h6>
                        <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-sm btn-outline-primary fw-600">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:.83rem">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Member</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= getProfilePhoto($u['profile_photo'], $u['gender']) ?>"
                                                 class="rounded-circle" width="32" height="32" style="object-fit:cover" alt="">
                                            <div>
                                                <div class="fw-600"><?= htmlspecialchars($u['username']) ?></div>
                                                <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($u['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($u['city'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($u['is_banned']): ?>
                                        <span class="badge bg-danger">Banned</span>
                                        <?php elseif ($u['is_premium']): ?>
                                        <span class="badge bg-warning text-dark">Premium</span>
                                        <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?= date('M d', strtotime($u['created_at'])) ?></td>
                                    <td class="pe-4">
                                        <a href="<?= SITE_URL ?>/admin/users.php?action=view&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-3 px-4 pb-0">
                        <h6 class="fw-700 mb-0">Recent Reports</h6>
                        <a href="<?= SITE_URL ?>/admin/reports.php" class="btn btn-sm btn-outline-danger fw-600">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentReports)): ?>
                        <div class="text-center text-muted py-4 small"><i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>No pending reports</div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentReports as $rep): ?>
                            <li class="list-group-item px-4 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-600 small"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $rep['reason']))) ?></div>
                                        <div class="text-muted" style="font-size:.73rem">
                                            <?= htmlspecialchars($rep['reporter']) ?> reported <?= htmlspecialchars($rep['reported_user']) ?>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <span class="badge bg-<?= $rep['status']==='pending'?'warning text-dark':'success' ?>" style="font-size:.63rem">
                                            <?= ucfirst($rep['status']) ?>
                                        </span>
                                        <span class="text-muted" style="font-size:.68rem"><?= timeAgo($rep['created_at']) ?></span>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
// Registrations chart
const ctx = document.getElementById('registrationsChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($registrations, 'd')) ?>,
            datasets: [{
                label: 'New Members',
                data: <?= json_encode(array_column($registrations, 'c')) ?>,
                backgroundColor: 'rgba(26,110,60,.7)',
                borderColor: '#1a6e3c',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}
// Sidebar toggle mobile
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.querySelector('.admin-sidebar').classList.toggle('open');
});
</script>
