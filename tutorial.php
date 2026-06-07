<?php
require_once __DIR__ . '/config/db.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!$slug) { header('Location: ' . SITE_URL . '/tutorials.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) { header('Location: ' . SITE_URL . '/tutorials.php'); exit; }

// Increment views
$pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

// Related posts
$related = $pdo->prepare("SELECT * FROM posts WHERE category = ? AND slug != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
$related->execute([$post['category'], $slug]);
$relatedPosts = $related->fetchAll();

$pageTitle = $post['title'];
$pageDesc = $post['excerpt'] ?: substr(strip_tags($post['content']), 0, 160);
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="page-hero" style="padding:120px 0 50px;text-align:left">
    <div class="container">
        <nav aria-label="breadcrumb" style="margin-bottom:1rem">
            <ol class="breadcrumb" style="background:none;padding:0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/" style="color:var(--text-muted)">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/tutorials.php" style="color:var(--text-muted)">Tutorials</a></li>
                <li class="breadcrumb-item active" style="color:var(--cyan)"><?= htmlspecialchars($post['title']) ?></li>
            </ol>
        </nav>
        <span class="post-category-badge badge-<?= htmlspecialchars($post['category']) ?>"><?= ucfirst(htmlspecialchars($post['category'])) ?></span>
        <h1 style="font-size:clamp(1.5rem,4vw,2.5rem);margin-top:0.8rem;max-width:700px"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="d-flex align-items-center gap-3 mt-2 flex-wrap" style="color:var(--text-muted);font-size:0.85rem">
            <span><i class="far fa-calendar me-1"></i><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
            <span><i class="far fa-eye me-1"></i><?= number_format($post['views'] + 1) ?> views</span>
            <?php if ($post['tags']): ?>
            <span><i class="fas fa-tags me-1"></i><?= htmlspecialchars($post['tags']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section style="padding:60px 0">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-8">
                <?php if ($post['image']): ?>
                <img src="<?= UPLOAD_URL . htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
                    style="width:100%;border-radius:var(--radius);margin-bottom:2rem;border:1px solid var(--glass-border);max-height:450px;object-fit:cover">
                <?php endif; ?>

                <div class="glass-card p-4 p-lg-5">
                    <div class="tutorial-content">
                        <?= $post['content'] ?>
                    </div>
                </div>

                <!-- Share -->
                <div class="glass-card p-3 mt-4 d-flex align-items-center gap-3 flex-wrap">
                    <span style="font-size:0.85rem;color:var(--text-muted)">Share:</span>
                    <a href="https://t.me/share/url?url=<?= urlencode(SITE_URL . '/tutorial.php?slug=' . $slug) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="social-btn"><i class="fab fa-telegram-plane"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/tutorial.php?slug=' . $slug) ?>" target="_blank" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' ' . SITE_URL . '/tutorial.php?slug=' . $slug) ?>" target="_blank" class="social-btn"><i class="fab fa-whatsapp"></i></a>
                    <a href="<?= SITE_URL ?>/tutorials.php" class="btn-outline-glow ms-auto" style="font-size:0.82rem;padding:6px 14px">
                        <i class="fas fa-arrow-left me-1"></i> Back to Tutorials
                    </a>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- About box -->
                <div class="glass-card p-3 mb-4">
                    <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">ABOUT HITECHCOMPUTER</h6>
                    <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1rem">Professional tech training &amp; repair shop at Zefmesh Grand Mall, Megenagna, Addis Ababa.</p>
                    <a href="tel:0968752100" class="btn-glow d-block text-center" style="font-size:0.85rem"><i class="fas fa-phone-alt me-1"></i>0968752100</a>
                </div>

                <!-- Courses CTA -->
                <div class="glass-card p-3 mb-4" style="background:linear-gradient(135deg,rgba(0,212,255,0.08),rgba(123,47,255,0.08));border-color:rgba(0,212,255,0.2)">
                    <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:0.8rem">LEARN FROM EXPERTS</h6>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem">Join our professional repair courses and build a tech career.</p>
                    <a href="<?= SITE_URL ?>/courses.php" class="btn-glow d-block text-center" style="font-size:0.82rem">View Courses</a>
                </div>

                <?php if (!empty($relatedPosts)): ?>
                <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem">RELATED POSTS</h6>
                <?php foreach ($relatedPosts as $rp): ?>
                <a href="<?= SITE_URL ?>/tutorial.php?slug=<?= urlencode($rp['slug']) ?>" class="d-block text-decoration-none mb-3">
                    <div class="glass-card p-3" style="transition:all 0.2s">
                        <span class="post-category-badge badge-<?= htmlspecialchars($rp['category']) ?>" style="margin-bottom:0.4rem;display:inline-block"><?= ucfirst($rp['category']) ?></span>
                        <div style="font-size:0.85rem;color:#fff;line-height:1.4"><?= htmlspecialchars($rp['title']) ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px"><?= timeAgo($rp['created_at']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
