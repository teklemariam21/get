<?php
require_once __DIR__ . '/../config/db.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$params = [];
$where = ["status = 'published'"];

if ($category && $category !== 'all') {
    $where[] = "category = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(title LIKE ? OR excerpt LIKE ? OR tags LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql = "SELECT * FROM posts WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$catColors = ['tutorial' => '#00d4ff22', 'tip' => '#00ff8822', 'trick' => '#7b2fff22', 'news' => '#ff6b3522'];
$catIcons = ['tutorial' => '📖', 'tip' => '💡', 'trick' => '🔧', 'news' => '📰'];

if (empty($posts)) {
    echo '<div class="col-12 text-center py-5" style="color:var(--text-muted)">
        <i class="fas fa-search fa-3x mb-3 d-block" style="opacity:0.3"></i>
        No posts found. Try a different search or category.
    </div>';
    exit;
}

foreach ($posts as $i => $post):
    $delay = ($i % 3) * 100;
?>
<div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
    <div class="glass-card post-card h-100">
        <?php if ($post['image']): ?>
        <img src="<?= UPLOAD_URL . htmlspecialchars($post['image']) ?>" alt="" class="post-card-img">
        <?php else: ?>
        <div class="post-card-img-placeholder" style="background:<?= $catColors[$post['category']] ?? '#ffffff10' ?>">
            <span style="font-size:4rem"><?= $catIcons[$post['category']] ?? '📄' ?></span>
        </div>
        <?php endif; ?>
        <div class="post-card-body">
            <span class="post-category-badge badge-<?= htmlspecialchars($post['category']) ?>"><?= ucfirst(htmlspecialchars($post['category'])) ?></span>
            <h5 class="post-card-title"><?= htmlspecialchars($post['title']) ?></h5>
            <p class="post-card-excerpt"><?= htmlspecialchars($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 110)) ?></p>
            <div class="post-card-meta">
                <span class="view-count"><i class="far fa-eye"></i><?= number_format($post['views']) ?></span>
                <span style="font-size:0.77rem;color:var(--text-muted)"><?= timeAgo($post['created_at']) ?></span>
                <a href="<?= SITE_URL ?>/tutorial.php?slug=<?= urlencode($post['slug']) ?>" class="post-read-link">
                    Read <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
