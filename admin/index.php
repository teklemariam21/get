<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }

$adminTitle = 'Dashboard';

$totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$publishedPosts = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$totalMessages = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$unreadMessages = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
$totalViews = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status='active'")->fetchColumn();

$recentPosts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 8")->fetchAll();
$recentMessages = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5")->fetchAll();
$topPosts = $pdo->query("SELECT * FROM posts WHERE status='published' ORDER BY views DESC LIMIT 5")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="admin-page-title">Dashboard</h1>
        <div class="d-flex align-items-center gap-3">
            <span style="font-size:0.85rem;color:var(--text-muted)">Welcome, <strong style="color:#fff"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong></span>
            <a href="<?= SITE_URL ?>/admin/add-post.php" class="btn-glow" style="font-size:0.8rem;padding:0.4rem 1rem">
                <i class="fas fa-plus me-1"></i> New Post
            </a>
        </div>
    </div>

    <div class="admin-content">
        <!-- Stats Cards -->
        <div class="row gy-3 mb-4">
            <?php
            $cards = [
                ['label' => 'Total Posts', 'value' => $totalPosts, 'icon' => 'fa-file-alt', 'color' => '#00d4ff', 'link' => 'posts.php'],
                ['label' => 'Published', 'value' => $publishedPosts, 'icon' => 'fa-check-circle', 'color' => '#00ff88', 'link' => 'posts.php'],
                ['label' => 'Total Views', 'value' => number_format($totalViews), 'icon' => 'fa-eye', 'color' => '#7b2fff', 'link' => 'posts.php'],
                ['label' => 'Messages', 'value' => $totalMessages, 'icon' => 'fa-envelope', 'color' => '#ff6b35', 'link' => 'messages.php', 'badge' => $unreadMessages],
            ];
            foreach ($cards as $card): ?>
            <div class="col-6 col-lg-3">
                <a href="<?= SITE_URL ?>/admin/<?= $card['link'] ?>" class="text-decoration-none">
                    <div class="glass-card stat-card" style="border-color:<?= $card['color'] ?>33">
                        <div class="stat-card-icon" style="color:<?= $card['color'] ?>"><i class="fas <?= $card['icon'] ?>"></i></div>
                        <span class="stat-card-value" style="color:<?= $card['color'] ?>"><?= $card['value'] ?></span>
                        <div class="stat-card-label">
                            <?= $card['label'] ?>
                            <?php if (!empty($card['badge']) && $card['badge'] > 0): ?>
                            <span style="background:var(--pink);color:#fff;font-size:0.65rem;padding:1px 6px;border-radius:50px;margin-left:4px"><?= $card['badge'] ?> new</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row gy-4">
            <!-- Recent Posts -->
            <div class="col-lg-8">
                <div class="glass-card p-0" style="overflow:hidden">
                    <div class="d-flex align-items-center justify-content-between p-3" style="border-bottom:1px solid var(--glass-border)">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.85rem;margin:0;color:var(--cyan)">Recent Posts</h6>
                        <a href="<?= SITE_URL ?>/admin/posts.php" style="font-size:0.78rem;color:var(--text-muted);text-decoration:none">View All</a>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Views</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentPosts as $post): ?>
                        <tr id="post-row-<?= $post['id'] ?>">
                            <td style="max-width:220px">
                                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500">
                                    <?= htmlspecialchars($post['title']) ?>
                                </div>
                            </td>
                            <td><span class="post-category-badge badge-<?= $post['category'] ?>"><?= ucfirst($post['category']) ?></span></td>
                            <td style="color:var(--text-muted)"><?= number_format($post['views']) ?></td>
                            <td>
                                <button class="toggle-status-btn status-badge status-<?= $post['status'] ?>" data-id="<?= $post['id'] ?>" title="Toggle status" style="border:none;cursor:pointer;background:none">
                                    <?= $post['status'] ?>
                                </button>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= SITE_URL ?>/admin/edit-post.php?id=<?= $post['id'] ?>" class="action-btn" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a href="<?= SITE_URL ?>/tutorial.php?slug=<?= urlencode($post['slug']) ?>" class="action-btn" target="_blank" title="View"><i class="fas fa-eye"></i></a>
                                    <button class="action-btn delete delete-post-btn" data-id="<?= $post['id'] ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar: Recent Messages + Top Posts -->
            <div class="col-lg-4">
                <!-- Messages -->
                <div class="glass-card p-0 mb-4" style="overflow:hidden">
                    <div class="d-flex align-items-center justify-content-between p-3" style="border-bottom:1px solid var(--glass-border)">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;margin:0;color:var(--orange)">
                            <i class="fas fa-envelope me-1"></i>Recent Messages
                        </h6>
                        <a href="<?= SITE_URL ?>/admin/messages.php" style="font-size:0.75rem;color:var(--text-muted);text-decoration:none">View All</a>
                    </div>
                    <?php foreach ($recentMessages as $msg): ?>
                    <div class="p-3" style="border-bottom:1px solid var(--glass-border)<?= !$msg['is_read'] ? ';background:rgba(255,107,53,0.03)' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="font-weight:<?= $msg['is_read'] ? '400' : '600' ?>;font-size:0.85rem;color:<?= $msg['is_read'] ? 'var(--text-muted)' : '#fff' ?>"><?= htmlspecialchars($msg['name']) ?></div>
                            <span style="font-size:0.7rem;color:var(--text-muted)"><?= timeAgo($msg['created_at']) ?></span>
                        </div>
                        <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars(substr($msg['message'], 0, 60)) ?>...</div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Top Posts -->
                <div class="glass-card p-3">
                    <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--purple);margin-bottom:1rem">
                        <i class="fas fa-fire me-1"></i>Most Viewed
                    </h6>
                    <?php foreach ($topPosts as $i => $tp): ?>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--text-muted);width:20px"><?= $i+1 ?></span>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:0.82rem;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($tp['title']) ?></div>
                            <div style="font-size:0.72rem;color:var(--text-muted)"><?= number_format($tp['views']) ?> views</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
