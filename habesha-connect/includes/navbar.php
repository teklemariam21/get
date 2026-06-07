<?php
$currentUser   = getCurrentUser();
$unreadMsgs    = $currentUser ? getUnreadCount($currentUser['id']) : 0;
$unreadNotifs  = $currentUser ? getUnreadNotifCount($currentUser['id']) : 0;
$notifications = $currentUser ? getNotifications($currentUser['id'], 8) : [];
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNav">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= SITE_URL ?>/">
            <div class="logo-icon">
                <i class="bi bi-heart-fill"></i>
            </div>
            <span class="brand-name">Habesha<span class="text-warning">Connect</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <?php if ($currentUser): ?>
            <!-- Logged-in navigation -->
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'browse.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/browse.php">
                        <i class="bi bi-people-fill me-1"></i>Browse
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'matches.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/matches.php">
                        <i class="bi bi-heart-fill me-1"></i>Matches
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'search.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/search.php">
                        <i class="bi bi-search me-1"></i>Search
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <!-- Messages -->
                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?= SITE_URL ?>/messages.php" title="Messages">
                        <i class="bi bi-chat-dots-fill fs-5"></i>
                        <?php if ($unreadMsgs > 0): ?>
                        <span class="badge-dot bg-danger"><?= $unreadMsgs > 9 ? '9+' : $unreadMsgs ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" id="notifDropdown" title="Notifications">
                        <i class="bi bi-bell-fill fs-5"></i>
                        <?php if ($unreadNotifs > 0): ?>
                        <span class="badge-dot bg-warning"><?= $unreadNotifs > 9 ? '9+' : $unreadNotifs ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow-lg border-0 p-0">
                        <div class="notif-header px-3 py-2 d-flex justify-content-between align-items-center">
                            <strong>Notifications</strong>
                            <a href="<?= SITE_URL ?>/api/mark-notifs-read.php" class="text-muted small">Mark all read</a>
                        </div>
                        <div class="notif-body">
                            <?php if (empty($notifications)): ?>
                            <div class="text-center text-muted py-4 small">No notifications yet</div>
                            <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                            <a href="<?= SITE_URL . htmlspecialchars($notif['link']) ?>" class="notif-item d-flex align-items-start gap-2 px-3 py-2 text-decoration-none <?= !$notif['is_read'] ? 'unread' : '' ?>">
                                <img src="<?= getProfilePhoto($notif['profile_photo'], $notif['gender'] ?? 'male') ?>"
                                     class="rounded-circle" width="36" height="36" style="object-fit:cover" alt="">
                                <div class="flex-grow-1">
                                    <div class="notif-text small"><?= htmlspecialchars($notif['message']) ?></div>
                                    <div class="text-muted" style="font-size:0.7rem"><?= timeAgo($notif['created_at']) ?></div>
                                </div>
                                <?php if ($notif['type'] === 'like'): ?>
                                <i class="bi bi-heart-fill text-danger small mt-1"></i>
                                <?php elseif ($notif['type'] === 'match'): ?>
                                <i class="bi bi-hearts text-warning small mt-1"></i>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center gap-2 user-menu-toggle" href="#" data-bs-toggle="dropdown">
                        <div class="nav-avatar position-relative">
                            <img src="<?= getProfilePhoto($currentUser['profile_photo'], $currentUser['gender']) ?>"
                                 class="rounded-circle" width="36" height="36" style="object-fit:cover" alt="">
                            <?php if (isOnline($currentUser['last_active'])): ?>
                            <span class="online-dot"></span>
                            <?php endif; ?>
                        </div>
                        <span class="d-none d-lg-inline text-white fw-500"><?= htmlspecialchars($currentUser['username']) ?></span>
                        <i class="bi bi-chevron-down small text-white-50"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                        <li>
                            <div class="dropdown-item-text px-3 py-2 border-bottom">
                                <div class="fw-600"><?= htmlspecialchars($currentUser['username']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($currentUser['email']) ?></small>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/edit-profile.php"><i class="bi bi-pencil me-2"></i>Edit Profile</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/liked-me.php"><i class="bi bi-heart me-2"></i>Who Liked Me</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/who-viewed-me.php"><i class="bi bi-eye me-2"></i>Who Viewed Me</a></li>
                        <?php if (!$currentUser['is_premium']): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-warning fw-600" href="<?= SITE_URL ?>/subscription.php"><i class="bi bi-star-fill me-2"></i>Go Premium</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>

            <?php else: ?>
            <!-- Guest navigation -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning btn-sm fw-600 px-3" href="<?= SITE_URL ?>/register.php">
                        <i class="bi bi-heart-fill me-1"></i>Join Free
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php
$flash = getFlash();
if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'x-circle' : 'info-circle') ?>-fill me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
