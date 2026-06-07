<?php
$pageTitle  = 'Settings';
$breadcrumb = 'Settings';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['site_name','tagline','sub_tagline','phone_1','phone_2','email','address','working_hours','facebook','instagram','telegram','youtube','about_short','meta_description'];
    foreach ($fields as $key) {
        $val = sanitize($_POST[$key] ?? '');
        $db->prepare("INSERT INTO settings (setting_key,setting_value,updated_at) VALUES (:k,:v,NOW()) ON DUPLICATE KEY UPDATE setting_value=:v2,updated_at=NOW()")->execute([':k'=>$key,':v'=>$val,':v2'=>$val]);
    }
    flash('success','Settings saved successfully!');
    redirect(SITE_URL . '/admin/settings.php');
}

$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$s = [];
foreach ($stmt->fetchAll() as $row) $s[$row['setting_key']] = $row['setting_value'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0" style="color:var(--dark)">Site Settings</h4>
</div>

<form method="POST">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="form-card mb-4">
        <div class="form-section-title">Branding</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Site Name</label>
            <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($s['site_name']??'Getas Reality') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Tagline</label>
            <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($s['tagline']??'') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Sub-tagline</label>
            <input type="text" name="sub_tagline" class="form-control" value="<?= htmlspecialchars($s['sub_tagline']??'') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">About (Short)</label>
            <textarea name="about_short" class="form-control" rows="3"><?= htmlspecialchars($s['about_short']??'') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Meta Description (SEO)</label>
            <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($s['meta_description']??'') ?></textarea>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-section-title">Contact Information</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Phone 1</label>
            <input type="text" name="phone_1" class="form-control" value="<?= htmlspecialchars($s['phone_1']??'') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone 2</label>
            <input type="text" name="phone_2" class="form-control" value="<?= htmlspecialchars($s['phone_2']??'') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email']??'') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Working Hours</label>
            <input type="text" name="working_hours" class="form-control" value="<?= htmlspecialchars($s['working_hours']??'') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Office Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($s['address']??'') ?>">
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-section-title">Social Media</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-facebook me-1 text-gold"></i>Facebook URL</label>
            <input type="url" name="facebook" class="form-control" value="<?= htmlspecialchars($s['facebook']??'') ?>" placeholder="https://facebook.com/...">
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-instagram me-1 text-gold"></i>Instagram URL</label>
            <input type="url" name="instagram" class="form-control" value="<?= htmlspecialchars($s['instagram']??'') ?>" placeholder="https://instagram.com/...">
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-telegram me-1 text-gold"></i>Telegram URL</label>
            <input type="url" name="telegram" class="form-control" value="<?= htmlspecialchars($s['telegram']??'') ?>" placeholder="https://t.me/...">
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-youtube me-1 text-gold"></i>YouTube URL</label>
            <input type="url" name="youtube" class="form-control" value="<?= htmlspecialchars($s['youtube']??'') ?>" placeholder="https://youtube.com/...">
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-gold btn-lg px-5">
        <i class="bi bi-check-circle me-2"></i>Save All Settings
      </button>
    </div>

    <div class="col-lg-4">
      <div class="form-card" style="background:var(--gold-bg);border-color:var(--gold-light)">
        <div class="form-section-title">Quick Info</div>
        <div class="mb-3">
          <div class="small fw-600 text-muted mb-1">Admin Login</div>
          <code class="small d-block">admin@getasreality.com</code>
        </div>
        <div class="mb-3">
          <div class="small fw-600 text-muted mb-1">Website URL</div>
          <a href="<?= SITE_URL ?>" target="_blank" class="small" style="color:var(--gold)"><?= SITE_URL ?></a>
        </div>
        <div class="mb-3">
          <div class="small fw-600 text-muted mb-1">Database</div>
          <code class="small">getas_realty</code>
        </div>
        <hr>
        <a href="<?= SITE_URL ?>/index.php" target="_blank" class="btn btn-outline-gold w-100 btn-sm">
          <i class="bi bi-box-arrow-up-right me-1"></i>Preview Website
        </a>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
