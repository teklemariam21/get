<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $fields = [
        'site_name', 'site_tagline', 'site_email', 'site_phone',
        'weekly_price', 'monthly_price', 'quarterly_price', 'annual_price',
        'max_photos', 'require_email_verify', 'maintenance_mode',
        'telebirr_enabled', 'cbebirr_enabled', 'bank_transfer_enabled',
    ];
    foreach ($fields as $key) {
        if (isset($_POST[$key])) {
            $val = sanitize($_POST[$key]);
            $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key, $val, $val]);
        }
    }
    flash('success', 'Settings saved successfully.');
    redirect(SITE_URL . '/admin/settings.php');
}

// Load all settings
$settings = [];
foreach ($db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$pageTitle = 'Site Settings';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex">
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content flex-grow-1">
    <div class="admin-topbar">
        <h6 class="mb-0 fw-700"><i class="bi bi-gear me-2"></i>Site Settings</h6>
    </div>
    <div class="container-fluid p-4">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <div class="row g-4">

                <!-- General Settings -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white fw-700 py-3"><i class="bi bi-globe me-2 text-primary"></i>General Settings</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Site Name</label>
                                <input type="text" class="form-control" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'Habesha Connect') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Tagline</label>
                                <input type="text" class="form-control" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Admin Email</label>
                                <input type="email" class="form-control" name="site_email" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Contact Phone</label>
                                <input type="text" class="form-control" name="site_phone" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Max Photos per User</label>
                                <input type="number" class="form-control" name="max_photos" value="<?= (int)($settings['max_photos'] ?? 6) ?>" min="1" max="20">
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="require_email_verify" value="1" id="req_email"
                                        <?= ($settings['require_email_verify']??'0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-600" for="req_email">Require Email Verification</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" id="maint"
                                        <?= ($settings['maintenance_mode']??'0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-600 text-danger" for="maint">Maintenance Mode</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Settings -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white fw-700 py-3"><i class="bi bi-cash-stack me-2 text-success"></i>Pricing (ETB)</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Weekly Plan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="weekly_price" value="<?= (int)($settings['weekly_price']??99) ?>">
                                    <span class="input-group-text">ETB / 7 days</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Monthly Plan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="monthly_price" value="<?= (int)($settings['monthly_price']??299) ?>">
                                    <span class="input-group-text">ETB / month</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Quarterly Plan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="quarterly_price" value="<?= (int)($settings['quarterly_price']??699) ?>">
                                    <span class="input-group-text">ETB / 3 months</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-600 small">Annual Plan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="annual_price" value="<?= (int)($settings['annual_price']??1999) ?>">
                                    <span class="input-group-text">ETB / year</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white fw-700 py-3"><i class="bi bi-credit-card me-2 text-warning"></i>Payment Methods</div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="telebirr_enabled" value="1" id="telebirr"
                                        <?= ($settings['telebirr_enabled']??'1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-600" for="telebirr"><i class="bi bi-phone me-2 text-primary"></i>TeleBirr</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="cbebirr_enabled" value="1" id="cbebirr"
                                        <?= ($settings['cbebirr_enabled']??'1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-600" for="cbebirr"><i class="bi bi-bank me-2 text-success"></i>CBE Birr</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="bank_transfer_enabled" value="1" id="bankt"
                                        <?= ($settings['bank_transfer_enabled']??'1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-600" for="bankt"><i class="bi bi-building me-2 text-warning"></i>Bank Transfer</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg fw-700 px-5">
                        <i class="bi bi-save me-2"></i>Save All Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
