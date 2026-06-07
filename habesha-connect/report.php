<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();

$reportedId = (int)($_GET['id'] ?? 0);
$reported   = $reportedId ? getUserById($reportedId) : null;
if (!$reported || $reportedId === $currentUser['id']) {
    flash('error', 'Invalid report.');
    redirect(SITE_URL . '/browse.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $reason  = sanitize($_POST['reason'] ?? '');
    $details = sanitize(substr($_POST['details'] ?? '', 0, 1000));
    if (empty($reason)) {
        $errors[] = 'Please select a reason.';
    } else {
        $db = getDB();
        $db->prepare("INSERT INTO reports (reporter_id, reported_id, reason, details) VALUES (?,?,?,?)")
           ->execute([$currentUser['id'], $reportedId, $reason, $details]);
        flash('success', 'Report submitted. Our moderation team will review it.');
        redirect(SITE_URL . '/profile.php?id=' . $reportedId);
    }
}

$pageTitle = 'Report User';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="profile-card p-4">
                <h5 class="fw-700 mb-1 text-danger"><i class="bi bi-flag-fill me-2"></i>Report User</h5>
                <p class="text-muted small mb-4">Reporting: <strong><?= htmlspecialchars($reported['username']) ?></strong></p>
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger small"><?= htmlspecialchars($errors[0]) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label fw-600">Reason *</label>
                        <select class="form-select" name="reason" required>
                            <option value="">-- Select reason --</option>
                            <option value="fake_profile">Fake or scam profile</option>
                            <option value="harassment">Harassment or abuse</option>
                            <option value="inappropriate_photo">Inappropriate photos</option>
                            <option value="spam">Spam or advertising</option>
                            <option value="underage">Appears to be underage</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-600">Details (optional)</label>
                        <textarea class="form-control" name="details" rows="4" maxlength="1000"
                                  placeholder="Please provide more details about your report..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger fw-700"><i class="bi bi-flag me-2"></i>Submit Report</button>
                        <a href="<?= SITE_URL ?>/profile.php?id=<?= $reportedId ?>" class="btn btn-outline-secondary fw-600">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
