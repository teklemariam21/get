<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $photoId = (int)($_POST['photo_id'] ?? 0);
    $action  = $_POST['action'] ?? '';
    if ($photoId) {
        if ($action === 'approve') {
            $db->prepare("UPDATE photos SET is_approved=1 WHERE id=?")->execute([$photoId]);
            flash('success', 'Photo approved.');
        } elseif ($action === 'reject') {
            $photo = $db->prepare("SELECT * FROM photos WHERE id=?");
            $photo->execute([$photoId]);
            $photo = $photo->fetch();
            if ($photo) {
                @unlink(UPLOAD_PATH . $photo['filename']);
                $db->prepare("DELETE FROM photos WHERE id=?")->execute([$photoId]);
                flash('success', 'Photo removed.');
            }
        }
    }
    redirect(SITE_URL . '/admin/photos.php');
}

$page    = max(1,(int)($_GET['page']??1));
$perPage = 24;
$offset  = ($page-1)*$perPage;
$filter  = $_GET['status'] ?? 'all';
$where   = $filter === 'pending' ? "WHERE p.is_approved=0" : ($filter === 'approved' ? "WHERE p.is_approved=1" : "");

$total  = (int)$db->query("SELECT COUNT(*) FROM photos p $where")->fetchColumn();
$pages  = ceil($total / $perPage);
$photos = $db->query("
    SELECT p.*, u.username, u.gender
    FROM photos p JOIN users u ON u.id=p.user_id $where
    ORDER BY p.uploaded_at DESC LIMIT $perPage OFFSET $offset
")->fetchAll();

$pageTitle = 'Photo Moderation';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content flex-grow-1">
    <div class="admin-topbar">
        <h6 class="mb-0 fw-700"><i class="bi bi-images me-2"></i>Photo Moderation</h6>
        <span class="text-muted small"><?= number_format($total) ?> photos</span>
    </div>
    <div class="container-fluid p-4">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <ul class="nav nav-pills mb-3 gap-2">
            <?php foreach (['all'=>'All','pending'=>'Pending Review','approved'=>'Approved'] as $s=>$l): ?>
            <li class="nav-item"><a class="nav-link <?= $filter===$s?'active':'' ?> fw-600 px-3" href="?status=<?= $s ?>"><?= $l ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="row g-3">
            <?php foreach ($photos as $photo): ?>
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div style="aspect-ratio:1;overflow:hidden">
                        <img src="<?= UPLOAD_URL . htmlspecialchars($photo['filename']) ?>"
                             class="w-100 h-100" style="object-fit:cover" alt="" loading="lazy">
                    </div>
                    <div class="card-body p-2">
                        <div class="fw-600 small mb-1">
                            <a href="<?= SITE_URL ?>/admin/users.php?action=view&id=<?= $photo['user_id'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($photo['username']) ?>
                            </a>
                            <?php if ($photo['is_profile']): ?>
                            <span class="badge bg-primary ms-1" style="font-size:.6rem">Profile</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.68rem"><?= timeAgo($photo['uploaded_at']) ?></div>
                        <div class="d-flex gap-1 mt-2">
                            <form method="POST" class="flex-fill">
                                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                                <?php if (!$photo['is_approved']): ?>
                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success w-100 fw-600 py-1" style="font-size:.72rem">
                                    <i class="bi bi-check"></i> Approve
                                </button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger w-100 fw-600 py-1 mt-1" style="font-size:.72rem"
                                        onclick="return confirm('Delete this photo?')">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($photos)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-images fs-1 opacity-25"></i>
                <p class="mt-2">No photos to review</p>
            </div>
            <?php endif; ?>
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
