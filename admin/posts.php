<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }

$adminTitle = 'Manage Posts';

$filterCat = $_GET['cat'] ?? 'all';
$filterStatus = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = ['1=1'];
$params = [];
if ($filterCat !== 'all') { $where[] = 'category = ?'; $params[] = $filterCat; }
if ($filterStatus !== 'all') { $where[] = 'status = ?'; $params[] = $filterStatus; }
if ($search) { $where[] = 'title LIKE ?'; $params[] = "%$search%"; }

$sql = "SELECT * FROM posts WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title">Manage Posts</h1>
        <a href="<?= SITE_URL ?>/admin/add-post.php" class="btn-glow" style="font-size:0.8rem;padding:0.4rem 1rem">
            <i class="fas fa-plus me-1"></i> New Post
        </a>
    </div>

    <div class="admin-content">
        <!-- Filters -->
        <div class="glass-card p-3 mb-4">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="q" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" name="cat">
                        <option value="all" <?= $filterCat === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <option value="tutorial" <?= $filterCat === 'tutorial' ? 'selected' : '' ?>>Tutorial</option>
                        <option value="tip" <?= $filterCat === 'tip' ? 'selected' : '' ?>>Tip</option>
                        <option value="trick" <?= $filterCat === 'trick' ? 'selected' : '' ?>>Trick</option>
                        <option value="news" <?= $filterCat === 'news' ? 'selected' : '' ?>>News</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" name="status">
                        <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn-glow" style="padding:0.35rem 1rem;font-size:0.8rem">Filter</button>
                </div>
                <?php if ($search || $filterCat !== 'all' || $filterStatus !== 'all'): ?>
                <div class="col-auto">
                    <a href="posts.php" style="color:var(--text-muted);font-size:0.82rem">Clear</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="glass-card p-0" style="overflow:hidden">
            <div class="p-3" style="border-bottom:1px solid var(--glass-border)">
                <span style="font-size:0.85rem;color:var(--text-muted)"><?= count($posts) ?> post(s) found</span>
            </div>
            <?php if (empty($posts)): ?>
            <div class="p-5 text-center" style="color:var(--text-muted)">
                <i class="fas fa-file-alt fa-3x mb-3 d-block" style="opacity:0.2"></i>
                No posts found. <a href="add-post.php" style="color:var(--cyan)">Create your first post</a>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($posts as $i => $post): ?>
                    <tr id="post-row-<?= $post['id'] ?>">
                        <td style="color:var(--text-muted)"><?= $i+1 ?></td>
                        <td>
                            <div style="font-weight:500;color:#fff"><?= htmlspecialchars($post['title']) ?></div>
                            <?php if ($post['featured']): ?><span style="font-size:0.7rem;color:var(--orange)"><i class="fas fa-star me-1"></i>Featured</span><?php endif; ?>
                        </td>
                        <td><span class="post-category-badge badge-<?= $post['category'] ?>"><?= ucfirst($post['category']) ?></span></td>
                        <td style="color:var(--text-muted)"><?= number_format($post['views']) ?></td>
                        <td>
                            <button class="toggle-status-btn status-badge status-<?= $post['status'] ?>" data-id="<?= $post['id'] ?>" title="Click to toggle" style="border:none;cursor:pointer;background:none">
                                <?= $post['status'] ?>
                            </button>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.8rem"><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
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
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
