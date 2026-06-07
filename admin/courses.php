<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }

$adminTitle = 'Manage Courses';
$error = $success = '';

// Handle add/edit course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = (int)($_POST['course_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $duration = sanitize($_POST['duration'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $level = sanitize($_POST['level'] ?? 'beginner');
    $icon = sanitize($_POST['icon'] ?? 'fas fa-graduation-cap');
    $color = sanitize($_POST['color'] ?? '#00d4ff');
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

    if (!$title || !$description) {
        $error = 'Title and description are required.';
    } else {
        if ($courseId) {
            $stmt = $pdo->prepare("UPDATE courses SET title=?,description=?,content=?,duration=?,price=?,level=?,icon=?,color=?,status=? WHERE id=?");
            $stmt->execute([$title,$description,$content,$duration,$price,$level,$icon,$color,$status,$courseId]);
            $success = 'Course updated successfully.';
        } else {
            $slug = slugify($title);
            $stmt = $pdo->prepare("INSERT INTO courses (title,slug,description,content,duration,price,level,icon,color,status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title,$slug,$description,$content,$duration,$price,$level,$icon,$color,$status]);
            $success = 'Course added successfully.';
        }
    }
}

// Delete course
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM courses WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: courses.php');
    exit;
}

$courses = $pdo->query("SELECT * FROM courses ORDER BY sort_order, id")->fetchAll();

// Edit mode
$editCourse = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCourse = $stmt->fetch();
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="admin-page-title">Manage Courses</h1>
    </div>

    <div class="admin-content">
        <?php if ($error): ?><div class="alert-glass error mb-3"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-glass success mb-3"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

        <div class="row gy-4">
            <!-- Form -->
            <div class="col-lg-5">
                <div class="glass-card p-4">
                    <h6 style="font-family:'Orbitron',sans-serif;font-size:0.85rem;color:var(--cyan);margin-bottom:1.5rem">
                        <?= $editCourse ? 'Edit Course' : 'Add New Course' ?>
                    </h6>
                    <form method="POST">
                        <?php if ($editCourse): ?>
                        <input type="hidden" name="course_id" value="<?= $editCourse['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Course Title *</label>
                            <input type="text" class="form-control" name="title" value="<?= $editCourse ? htmlspecialchars($editCourse['title']) : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Short Description *</label>
                            <textarea class="form-control" name="description" rows="3"><?= $editCourse ? htmlspecialchars($editCourse['description']) : '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">What Students Learn (one per line)</label>
                            <textarea class="form-control" name="content" rows="4"><?= $editCourse ? htmlspecialchars($editCourse['content']) : '' ?></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Duration</label>
                                <input type="text" class="form-control" name="duration" placeholder="e.g. 3 Months" value="<?= $editCourse ? htmlspecialchars($editCourse['duration']) : '' ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Price (ETB)</label>
                                <input type="number" class="form-control" name="price" value="<?= $editCourse ? $editCourse['price'] : '' ?>">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Level</label>
                                <select class="form-select" name="level">
                                    <?php foreach (['beginner','intermediate','advanced'] as $lv): ?>
                                    <option value="<?= $lv ?>" <?= ($editCourse && $editCourse['level'] === $lv) ? 'selected' : '' ?>><?= ucfirst($lv) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?= ($editCourse && $editCourse['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($editCourse && $editCourse['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label">Icon Class <small style="color:var(--text-muted)">(Font Awesome)</small></label>
                                <input type="text" class="form-control" name="icon" placeholder="fas fa-mobile-alt" value="<?= $editCourse ? htmlspecialchars($editCourse['icon']) : '' ?>">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color w-100" name="color" value="<?= $editCourse ? htmlspecialchars($editCourse['color']) : '#00d4ff' ?>">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-glow flex-fill">
                                <i class="fas fa-save me-1"></i><?= $editCourse ? 'Update' : 'Add Course' ?>
                            </button>
                            <?php if ($editCourse): ?>
                            <a href="courses.php" class="btn-outline-glow">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Courses List -->
            <div class="col-lg-7">
                <div class="glass-card p-0" style="overflow:hidden">
                    <div class="p-3" style="border-bottom:1px solid var(--glass-border)">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin:0">All Courses</h6>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Course</th><th>Duration</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:8px;background:<?= htmlspecialchars($c['color']) ?>22;color:<?= htmlspecialchars($c['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0">
                                        <i class="<?= htmlspecialchars($c['icon']) ?>"></i>
                                    </div>
                                    <div style="font-weight:500;font-size:0.88rem"><?= htmlspecialchars($c['title']) ?></div>
                                </div>
                            </td>
                            <td style="color:var(--text-muted);font-size:0.83rem"><?= htmlspecialchars($c['duration']) ?></td>
                            <td style="color:var(--cyan);font-family:'Orbitron',sans-serif;font-size:0.82rem"><?= number_format($c['price']) ?></td>
                            <td><span class="status-badge status-<?= $c['status'] === 'active' ? 'published' : 'draft' ?>"><?= $c['status'] ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="?edit=<?= $c['id'] ?>" class="action-btn" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a href="?delete=<?= $c['id'] ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this course?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
