<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/admin/posts.php'); exit; }

$post = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$post->execute([$id]);
$post = $post->fetch();
if (!$post) { header('Location: ' . SITE_URL . '/admin/posts.php'); exit; }

$adminTitle = 'Edit Post';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $category = sanitize($_POST['category'] ?? 'tutorial');
    $tags = sanitize($_POST['tags'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!$title || !$content) {
        $error = 'Title and content are required.';
    } else {
        $imageName = $post['image'];

        if (!empty($_FILES['image']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mime, $allowed) && $_FILES['image']['size'] < 5 * 1024 * 1024) {
                if ($imageName && file_exists(UPLOAD_DIR . $imageName)) unlink(UPLOAD_DIR . $imageName);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('post_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $imageName);
            } else {
                $error = 'Invalid image.';
            }
        }

        if (!empty($_POST['remove_image']) && $imageName) {
            if (file_exists(UPLOAD_DIR . $imageName)) unlink(UPLOAD_DIR . $imageName);
            $imageName = null;
        }

        if (!$error) {
            $stmt = $pdo->prepare("UPDATE posts SET title=?,excerpt=?,content=?,category=?,tags=?,image=?,status=?,featured=?,updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $excerpt, $content, $category, $tags, $imageName, $status, $featured, $id]);
            $post = array_merge($post, compact('title','excerpt','content','category','tags','status','featured') + ['image' => $imageName]);
            $success = "Post updated successfully! <a href='" . SITE_URL . "/tutorial.php?slug={$post['slug']}' target='_blank' style='color:var(--cyan)'>View Post →</a>";
        }
    }
}

$extraHead = '<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= SITE_URL ?>/admin/posts.php" style="color:var(--text-muted);font-size:0.85rem;text-decoration:none"><i class="fas fa-arrow-left me-1"></i>Posts</a>
            <span style="color:var(--glass-border)">/</span>
            <h1 class="admin-page-title">Edit Post</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= SITE_URL ?>/tutorial.php?slug=<?= urlencode($post['slug']) ?>" target="_blank" class="btn-outline-glow" style="font-size:0.8rem;padding:0.35rem 0.9rem">
                <i class="fas fa-eye me-1"></i> Preview
            </a>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($error): ?><div class="alert-glass error mb-4"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-glass success mb-4"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row gy-4">
                <div class="col-lg-8">
                    <div class="glass-card p-4 mb-4">
                        <div class="mb-3">
                            <label class="form-label">Post Title *</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Excerpt</label>
                            <textarea class="form-control" name="excerpt" rows="2"><?= htmlspecialchars($post['excerpt']) ?></textarea>
                        </div>
                        <div>
                            <label class="form-label">Content *</label>
                            <div class="quill-editor-wrap">
                                <div id="quill-editor" style="min-height:350px"></div>
                            </div>
                            <textarea name="content" id="content-hidden" style="display:none"><?= htmlspecialchars($post['content']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-card p-4 mb-4">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">PUBLISH SETTINGS</h6>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= $post['featured'] ? 'checked' : '' ?> style="margin-right:8px">
                            <label class="form-check-label" for="featured" style="color:var(--text-muted);font-size:0.88rem">Feature this post</label>
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem">
                            Slug: <code style="color:var(--cyan)"><?= htmlspecialchars($post['slug']) ?></code>
                        </div>
                        <button type="submit" class="btn-glow w-100"><i class="fas fa-save me-1"></i> Update Post</button>
                    </div>

                    <div class="glass-card p-4 mb-4">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">CATEGORY & TAGS</h6>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <?php foreach (['tutorial','tip','trick','news'] as $cat): ?>
                                <option value="<?= $cat ?>" <?= $post['category'] === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tags</label>
                            <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($post['tags']) ?>">
                        </div>
                    </div>

                    <div class="glass-card p-4">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">FEATURED IMAGE</h6>
                        <?php if ($post['image']): ?>
                        <div style="position:relative;margin-bottom:1rem">
                            <img src="<?= UPLOAD_URL . htmlspecialchars($post['image']) ?>" style="width:100%;border-radius:8px;border:1px solid var(--glass-border)">
                            <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:0.8rem;cursor:pointer;color:var(--pink)">
                                <input type="checkbox" name="remove_image" value="1"> Remove image
                            </label>
                        </div>
                        <?php endif; ?>
                        <div style="border:2px dashed var(--glass-border);border-radius:10px;padding:1rem;text-align:center;cursor:pointer" onclick="document.getElementById('image-input').click()">
                            <img id="image-preview" style="display:none;max-width:100%;border-radius:8px;margin-bottom:0.5rem">
                            <div id="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:var(--text-muted);display:block;margin-bottom:4px"></i>
                                <div style="font-size:0.78rem;color:var(--text-muted)"><?= $post['image'] ? 'Replace image' : 'Click to upload' ?></div>
                            </div>
                            <input type="file" id="image-input" name="image" accept="image/*" style="display:none">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script>
var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            ['link', 'image', 'video', 'code-block', 'blockquote'],
            ['clean']
        ]
    }
});

// Load existing content
quill.root.innerHTML = <?= json_encode($post['content']) ?>;

document.querySelector('form').addEventListener('submit', function () {
    document.getElementById('content-hidden').value = quill.root.innerHTML;
});

document.getElementById('image-input').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('image-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
