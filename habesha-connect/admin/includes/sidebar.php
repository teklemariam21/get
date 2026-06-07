<?php
require_once __DIR__ . '/auth.php';
$admin = getAdmin();
$cur   = basename($_SERVER['PHP_SELF']);
$db    = getDB();
$pendingReports = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$pendingSubs    = (int)$db->query("SELECT COUNT(*) FROM subscriptions WHERE status='pending'")->fetchColumn();
?>
<div class="admin-sidebar d-flex flex-column">
    <!-- Brand -->
    <div class="brand d-flex align-items-center gap-2">
        <div class="logo-icon" style="width:30px;height:30px;font-size:.85rem"><i class="bi bi-heart-fill"></i></div>
        <div>
            <div class="fw-700 text-white" style="font-size:.95rem">Habesha<span style="color:var(--secondary)">Connect</span></div>
            <div style="font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.5px">Admin Panel</div>
        </div>
    </div>

    <!-- Admin Info -->
    <div class="px-3 py-3 border-bottom" style="border-color:rgba(255,255,255,.08)!important">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 text-dark" style="width:34px;height:34px;background:var(--secondary);font-size:.85rem;flex-shrink:0">
                <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>
            </div>
            <div>
                <div class="text-white fw-600" style="font-size:.82rem"><?= htmlspecialchars($admin['username'] ?? 'Admin') ?></div>
                <div style="font-size:.65rem;color:rgba(255,255,255,.4)"><?= ucfirst($admin['role'] ?? 'admin') ?></div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-grow-1 py-2 overflow-auto">
        <div class="admin-nav-section">Main</div>
        <a href="<?= SITE_URL ?>/admin/" class="admin-nav-link <?= $cur === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/admin/users.php" class="admin-nav-link <?= $cur === 'users.php' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Members
        </a>
        <a href="<?= SITE_URL ?>/admin/reports.php" class="admin-nav-link <?= $cur === 'reports.php' ? 'active' : '' ?>">
            <i class="bi bi-flag-fill"></i> Reports
            <?php if ($pendingReports): ?><span class="badge bg-danger badge-sidebar ms-auto"><?= $pendingReports ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/subscriptions.php" class="admin-nav-link <?= $cur === 'subscriptions.php' ? 'active' : '' ?>">
            <i class="bi bi-star-fill"></i> Subscriptions
            <?php if ($pendingSubs): ?><span class="badge bg-warning text-dark badge-sidebar ms-auto"><?= $pendingSubs ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/photos.php" class="admin-nav-link <?= $cur === 'photos.php' ? 'active' : '' ?>">
            <i class="bi bi-images"></i> Photos
        </a>
        <div class="admin-nav-section mt-2">Settings</div>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="admin-nav-link <?= $cur === 'settings.php' ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i> Site Settings
        </a>
        <div class="admin-nav-section mt-2">Quick Links</div>
        <a href="<?= SITE_URL ?>/" class="admin-nav-link" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> View Site
        </a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="admin-nav-link" style="color:rgba(239,68,68,.8)!important">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>

    <div class="px-3 py-2" style="border-top:1px solid rgba(255,255,255,.07)">
        <div class="text-center" style="font-size:.65rem;color:rgba(255,255,255,.25)">&copy; <?= date('Y') ?> HabeshaConnect</div>
    </div>
</div>
