<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $db = getDB();

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!verifyPassword($current, $currentUser['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([hashPassword($new), $currentUser['id']]);
            flash('success', 'Password changed successfully.');
            redirect(SITE_URL . '/settings.php');
        }
    } elseif ($action === 'notifications') {
        $db->prepare("UPDATE users SET notifications_email=?, notifications_likes=?, notifications_messages=? WHERE id=?")
           ->execute([
               isset($_POST['notifications_email']) ? 1 : 0,
               isset($_POST['notifications_likes']) ? 1 : 0,
               isset($_POST['notifications_messages']) ? 1 : 0,
               $currentUser['id'],
           ]);
        flash('success', 'Notification preferences saved.');
        redirect(SITE_URL . '/settings.php');
    } elseif ($action === 'deactivate') {
        $confirm = $_POST['confirm_deactivate'] ?? '';
        if ($confirm !== 'DELETE') {
            $errors[] = 'Please type DELETE to confirm account deactivation.';
        } else {
            $db->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$currentUser['id']]);
            session_destroy();
            redirect(SITE_URL . '/login.php?msg=deactivated');
        }
    } elseif ($action === 'messaging') {
        $allow = in_array($_POST['allow_messages'] ?? '', ['everyone','matches','premium']) ? $_POST['allow_messages'] : 'everyone';
        $db->prepare("UPDATE users SET allow_messages=? WHERE id=?")->execute([$allow, $currentUser['id']]);
        flash('success', 'Messaging settings saved.');
        redirect(SITE_URL . '/settings.php');
    }
}

$pageTitle = 'Account Settings';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="profile-card p-3">
                <div class="text-center mb-3">
                    <img src="<?= getProfilePhoto($currentUser['profile_photo'], $currentUser['gender']) ?>"
                         class="rounded-circle" width="70" height="70" style="object-fit:cover" alt="">
                    <div class="fw-700 mt-2"><?= htmlspecialchars($currentUser['username']) ?></div>
                </div>
                <nav class="nav flex-column gap-1">
                    <a class="nav-link py-2 px-3 rounded active" href="#password" data-bs-toggle="tab"><i class="bi bi-lock me-2"></i>Password</a>
                    <a class="nav-link py-2 px-3 rounded" href="#notifications" data-bs-toggle="tab"><i class="bi bi-bell me-2"></i>Notifications</a>
                    <a class="nav-link py-2 px-3 rounded" href="#messaging" data-bs-toggle="tab"><i class="bi bi-chat me-2"></i>Messaging</a>
                    <a class="nav-link py-2 px-3 rounded" href="#subscription-tab" data-bs-toggle="tab"><i class="bi bi-star me-2"></i>Subscription</a>
                    <a class="nav-link py-2 px-3 rounded text-danger" href="#deactivate" data-bs-toggle="tab"><i class="bi bi-trash me-2"></i>Deactivate</a>
                </nav>
            </div>
        </div>

        <div class="col-lg-9">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-3">
                <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="tab-content">
                <!-- Password -->
                <div class="tab-pane fade show active" id="password">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-lock me-2 text-primary"></i>Change Password</h5>
                        <form method="POST" style="max-width:400px">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="password">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" id="password" required>
                                <div class="password-strength strength-0 mt-1" id="passwordStrength"></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Change Password</button>
                        </form>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="tab-pane fade" id="notifications">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-bell me-2 text-primary"></i>Notification Preferences</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="notifications">
                            <div class="d-flex flex-column gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notifications_email" id="ne1" value="1" <?= $currentUser['notifications_email']?'checked':'' ?>>
                                    <label class="form-check-label" for="ne1">
                                        <strong>Email Notifications</strong>
                                        <div class="text-muted small">Receive notifications via email</div>
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notifications_likes" id="ne2" value="1" <?= $currentUser['notifications_likes']?'checked':'' ?>>
                                    <label class="form-check-label" for="ne2">
                                        <strong>Like Notifications</strong>
                                        <div class="text-muted small">Get notified when someone likes you</div>
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notifications_messages" id="ne3" value="1" <?= $currentUser['notifications_messages']?'checked':'' ?>>
                                    <label class="form-check-label" for="ne3">
                                        <strong>Message Notifications</strong>
                                        <div class="text-muted small">Get notified when you receive a message</div>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary fw-700 px-4 mt-4"><i class="bi bi-save me-2"></i>Save Preferences</button>
                        </form>
                    </div>
                </div>

                <!-- Messaging -->
                <div class="tab-pane fade" id="messaging">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-chat me-2 text-primary"></i>Messaging Settings</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="messaging">
                            <div class="mb-4">
                                <label class="form-label fw-600">Who can send me messages?</label>
                                <div class="d-flex flex-column gap-2 mt-2">
                                    <?php foreach (['everyone'=>'Everyone (all members)', 'matches'=>'Only my matches', 'premium'=>'Only Premium members'] as $val => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="allow_messages" id="am_<?= $val ?>" value="<?= $val ?>" <?= $currentUser['allow_messages']===$val?'checked':'' ?>>
                                        <label class="form-check-label" for="am_<?= $val ?>"><?= $label ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Save</button>
                        </form>
                    </div>
                </div>

                <!-- Subscription status -->
                <div class="tab-pane fade" id="subscription-tab">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-star me-2 text-warning"></i>Subscription Status</h5>
                        <?php if ($currentUser['is_premium']): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Active Premium Membership</strong><br>
                            <?php if ($currentUser['premium_expires']): ?>
                            Expires: <?= date('F d, Y', strtotime($currentUser['premium_expires'])) ?>
                            <?php endif; ?>
                        </div>
                        <a href="<?= SITE_URL ?>/subscription.php" class="btn btn-outline-warning fw-600">Renew / Change Plan</a>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-star me-2"></i>You're on the <strong>Free Plan</strong>
                        </div>
                        <p class="text-muted mb-3">Upgrade to Premium for unlimited messaging, see who liked you, and more.</p>
                        <a href="<?= SITE_URL ?>/subscription.php" class="btn btn-warning fw-700"><i class="bi bi-star-fill me-2"></i>Upgrade to Premium</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Deactivate -->
                <div class="tab-pane fade" id="deactivate">
                    <div class="profile-card p-4 border-danger">
                        <h5 class="fw-700 mb-2 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Deactivate Account</h5>
                        <p class="text-muted">Deactivating your account will hide your profile from all members. You can reactivate by logging in again.</p>
                        <div class="alert alert-danger">
                            <strong>Warning:</strong> This action will hide your profile immediately.
                        </div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account?')">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="deactivate">
                            <div class="mb-3">
                                <label class="form-label fw-600">Type <code>DELETE</code> to confirm</label>
                                <input type="text" class="form-control" name="confirm_deactivate" placeholder="Type DELETE" required>
                            </div>
                            <button type="submit" class="btn btn-danger fw-700"><i class="bi bi-trash me-2"></i>Deactivate My Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
