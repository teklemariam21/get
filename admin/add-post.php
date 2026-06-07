<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }

$adminTitle = 'Add New Post';
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
        // Generate slug
        $slug = slugify($title);
        $baseSlug = $slug;
        $counter = 1;
        while ($pdo->prepare("SELECT id FROM posts WHERE slug = ?")->execute([$slug]) &&
               $pdo->prepare("SELECT id FROM posts WHERE slug = ?")->execute([$slug]) &&
               $pdo->query("SELECT COUNT(*) FROM posts WHERE slug = '$slug'")->fetchColumn() > 0) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Handle image upload
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mime, $allowed) && $_FILES['image']['size'] < 5 * 1024 * 1024) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('post_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $imageName);
            } else {
                $error = 'Invalid image. Use JPG, PNG, WebP under 5MB.';
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, excerpt, content, category, tags, image, status, featured, user_id) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $tags, $imageName, $status, $featured, $_SESSION['admin_id']]);
                $newId = $pdo->lastInsertId();
                $success = "Post published successfully! <a href='" . SITE_URL . "/tutorial.php?slug=$slug' target='_blank' style='color:var(--cyan)'>View Post →</a>";
                header("Refresh: 2; url=" . SITE_URL . "/admin/edit-post.php?id=$newId");
            } catch (Exception $e) {
                $error = 'Error saving post. Please try again.';
            }
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
            <h1 class="admin-page-title">Add New Post</h1>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($error): ?><div class="alert-glass error mb-4"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-glass success mb-4"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row gy-4">
                <!-- Main content -->
                <div class="col-lg-8">
                    <div class="glass-card p-4 mb-4">
                        <div class="mb-3">
                            <label class="form-label">Post Title *</label>
                            <input type="text" class="form-control" name="title" placeholder="Enter a descriptive title..." required
                                value="<?= isset($title) ? htmlspecialchars($title) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Excerpt <small style="color:var(--text-muted)">(short summary shown in listings)</small></label>
                            <textarea class="form-control" name="excerpt" rows="2" placeholder="Brief description of this post..."><?= isset($excerpt) ? htmlspecialchars($excerpt) : '' ?></textarea>
                        </div>
                        <div>
                            <label class="form-label">Content *</label>
                            <div class="quill-editor-wrap">
                                <div id="quill-editor" style="min-height:350px"><?= isset($content) ? $content : '' ?></div>
                            </div>
                            <textarea name="content" id="content-hidden" style="display:none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar settings -->
                <div class="col-lg-4">
                    <!-- Publish -->
                    <div class="glass-card p-4 mb-4">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">PUBLISH SETTINGS</h6>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="published" <?= (isset($status) && $status === 'published') ? 'selected' : '' ?>>Published</option>
                                <option value="draft" <?= (isset($status) && $status === 'draft') ? 'selected' : '' ?>>Draft</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check" style="padding-left:0">
                                <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= (isset($featured) && $featured) ? 'checked' : '' ?> style="margin-right:8px">
                                <label class="form-check-label" for="featured" style="color:var(--text-muted);font-size:0.88rem">Feature this post</label>
                            </div>
                        </div>
                        <button type="submit" class="btn-glow w-100"><i class="fas fa-save me-1"></i> Save Post</button>
                    </div>

                    <!-- Category & Tags -->
                    <div class="glass-card p-4 mb-4">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">CATEGORY & TAGS</h6>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="tutorial">Tutorial</option>
                                <option value="tip">Tip</option>
                                <option value="trick">Trick</option>
                                <option value="news">News</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tags <small style="color:var(--text-muted)">(comma separated)</small></label>
                            <input type="text" class="form-control" name="tags" placeholder="e.g. iPhone, screen, repair"
                                value="<?= isset($tags) ? htmlspecialchars($tags) : '' ?>">
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="glass-card p-4">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:1rem">FEATURED IMAGE</h6>
                        <div style="border:2px dashed var(--glass-border);border-radius:10px;padding:1.5rem;text-align:center;cursor:pointer" onclick="document.getElementById('image-input').click()">
                            <img id="image-preview" style="display:none;max-width:100%;border-radius:8px;margin-bottom:1rem">
                            <div id="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--text-muted);margin-bottom:0.5rem;display:block"></i>
                                <div style="font-size:0.82rem;color:var(--text-muted)">Click to upload image</div>
                                <div style="font-size:0.73rem;color:var(--text-muted)">JPG, PNG, WebP · Max 5MB</div>
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
    },
    placeholder: 'Start writing your post content here...'
});

// Sync quill content to hidden textarea on form submit
document.querySelector('form').addEventListener('submit', function () {
    document.getElementById('content-hidden').value = quill.root.innerHTML;
});

// Image preview
document.getElementById('image-input').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('image-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('upload-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
