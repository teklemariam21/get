<?php $adminPage = basename($_SERVER['PHP_SELF'], '.php'); ?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <a href="<?= SITE_URL ?>/" class="text-decoration-none d-flex align-items-center gap-2">
            <span style="font-size:1.2rem;color:var(--cyan)"><i class="fas fa-microchip"></i></span>
            <span style="font-family:'Orbitron',sans-serif;font-size:0.95rem;color:#fff">Hitech<span style="color:var(--cyan)">Computer</span></span>
        </a>
        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;padding-left:30px">Admin Panel</div>
    </div>

    <nav class="admin-nav">
        <div class="admin-nav-section">Main</div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/admin/index.php" class="<?= $adminPage === 'index' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>

        <div class="admin-nav-section">Content</div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/admin/posts.php" class="<?= in_array($adminPage, ['posts', 'add-post', 'edit-post']) ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Posts
            </a>
        </div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/admin/add-post.php" class="<?= $adminPage === 'add-post' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Add New Post
            </a>
        </div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/admin/courses.php" class="<?= $adminPage === 'courses' ? 'active' : '' ?>">
                <i class="fas fa-graduation-cap"></i> Courses
            </a>
        </div>

        <div class="admin-nav-section">Communication</div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/admin/messages.php" class="<?= $adminPage === 'messages' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Messages
                <?php
                $unread = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
                if ($unread > 0): ?>
                <span style="background:var(--pink);color:#fff;font-size:0.65rem;padding:2px 6px;border-radius:50px;margin-left:auto"><?= $unread ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="admin-nav-section">Site</div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
        </div>
        <div class="admin-nav-item">
            <a href="<?= SITE_URL ?>/admin/logout.php" style="color:#ff6b6b !important">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</aside>
