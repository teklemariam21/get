<?php
require_once 'includes/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $db = getDB();
        $action = $_POST['action'] ?? 'profile';

        if ($action === 'photo') {
            // Upload new photo
            if (!empty($_FILES['photo']['name'])) {
                $filename = uploadPhoto($_FILES['photo'], $currentUser['id']);
                if ($filename) {
                    // Save to photos table
                    $isProfile = empty($currentUser['profile_photo']) ? 1 : (int)($_POST['set_profile'] ?? 0);
                    $db->prepare("INSERT INTO photos (user_id, filename, is_profile) VALUES (?,?,?)")->execute([$currentUser['id'], $filename, $isProfile]);
                    if ($isProfile) {
                        $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")->execute([$filename, $currentUser['id']]);
                    }
                    flash('success', 'Photo uploaded successfully!');
                } else {
                    flash('error', 'Photo upload failed. Max 5MB, JPG/PNG only.');
                }
            }
        } elseif ($action === 'delete_photo') {
            $photoId = (int)($_POST['photo_id'] ?? 0);
            $photo   = $db->prepare("SELECT * FROM photos WHERE id = ? AND user_id = ?");
            $photo->execute([$photoId, $currentUser['id']]);
            $photo = $photo->fetch();
            if ($photo) {
                @unlink(UPLOAD_PATH . $photo['filename']);
                $db->prepare("DELETE FROM photos WHERE id = ?")->execute([$photoId]);
                if ($photo['is_profile']) {
                    // Assign next photo as profile
                    $next = $db->prepare("SELECT filename FROM photos WHERE user_id = ? ORDER BY id DESC LIMIT 1");
                    $next->execute([$currentUser['id']]);
                    $next = $next->fetchColumn();
                    $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?")->execute([$next ?: null, $currentUser['id']]);
                }
                flash('success', 'Photo deleted.');
            }
        } else {
            // Update profile fields
            $fields = [
                'city'          => sanitize($_POST['city'] ?? ''),
                'region'        => sanitize($_POST['region'] ?? ''),
                'country'       => sanitize($_POST['country'] ?? 'Ethiopia'),
                'ethnicity'     => sanitize($_POST['ethnicity'] ?? ''),
                'religion'      => sanitize($_POST['religion'] ?? ''),
                'marital_status'=> in_array($_POST['marital_status'] ?? '', ['single','divorced','widowed','separated']) ? $_POST['marital_status'] : 'single',
                'education'     => sanitize($_POST['education'] ?? ''),
                'occupation'    => sanitize(substr($_POST['occupation'] ?? '', 0, 150)),
                'height_cm'     => (int)($_POST['height_cm'] ?? 0) ?: null,
                'body_type'     => sanitize($_POST['body_type'] ?? ''),
                'skin_tone'     => sanitize($_POST['skin_tone'] ?? ''),
                'eye_color'     => sanitize($_POST['eye_color'] ?? ''),
                'hair_type'     => sanitize($_POST['hair_type'] ?? ''),
                'languages'     => sanitize(substr($_POST['languages'] ?? '', 0, 200)),
                'about_me'      => sanitize(substr($_POST['about_me'] ?? '', 0, 1000)),
                'ideal_partner' => sanitize(substr($_POST['ideal_partner'] ?? '', 0, 1000)),
                'hobbies'       => sanitize(substr($_POST['hobbies'] ?? '', 0, 300)),
                'smoking'       => in_array($_POST['smoking'] ?? '', ['never','occasionally','regularly']) ? $_POST['smoking'] : 'never',
                'drinking'      => in_array($_POST['drinking'] ?? '', ['never','occasionally','regularly']) ? $_POST['drinking'] : 'never',
                'children'      => in_array($_POST['children'] ?? '', ['none','have_children','want_children','dont_want']) ? $_POST['children'] : 'none',
                'seeking'       => in_array($_POST['seeking'] ?? '', ['male','female','both']) ? $_POST['seeking'] : 'both',
                'hide_age'      => isset($_POST['hide_age']) ? 1 : 0,
                'hide_location' => isset($_POST['hide_location']) ? 1 : 0,
            ];

            $sets   = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($fields)));
            $values = array_values($fields);
            $values[] = $currentUser['id'];
            $db->prepare("UPDATE users SET $sets WHERE id = ?")->execute($values);
            flash('success', 'Profile updated successfully!');
        }
        redirect(SITE_URL . '/edit-profile.php');
    }
}

// Reload user
$db     = getDB();
$user   = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$currentUser['id']]);
$user   = $user->fetch();

$photos = $db->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY is_profile DESC, id DESC");
$photos->execute([$currentUser['id']]);
$photos = $photos->fetchAll();

$pageTitle = 'Edit Profile';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar Nav -->
        <div class="col-lg-3">
            <div class="profile-card p-3">
                <div class="text-center mb-3">
                    <img src="<?= getProfilePhoto($user['profile_photo'], $user['gender']) ?>"
                         class="rounded-circle" width="80" height="80" style="object-fit:cover" alt="">
                    <div class="fw-700 mt-2"><?= htmlspecialchars($user['username']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <hr>
                <nav class="nav flex-column gap-1">
                    <a class="nav-link py-2 px-3 rounded active" href="#photos" data-bs-toggle="tab"><i class="bi bi-images me-2"></i>Photos</a>
                    <a class="nav-link py-2 px-3 rounded" href="#basic" data-bs-toggle="tab"><i class="bi bi-person me-2"></i>Basic Info</a>
                    <a class="nav-link py-2 px-3 rounded" href="#appearance" data-bs-toggle="tab"><i class="bi bi-eye me-2"></i>Appearance</a>
                    <a class="nav-link py-2 px-3 rounded" href="#lifestyle" data-bs-toggle="tab"><i class="bi bi-heart me-2"></i>Lifestyle</a>
                    <a class="nav-link py-2 px-3 rounded" href="#aboutme" data-bs-toggle="tab"><i class="bi bi-chat-text me-2"></i>About & Bio</a>
                    <a class="nav-link py-2 px-3 rounded" href="#privacy" data-bs-toggle="tab"><i class="bi bi-lock me-2"></i>Privacy</a>
                </nav>
                <hr>
                <a href="<?= SITE_URL ?>/profile.php" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i>View My Profile
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="tab-content">

                <!-- PHOTOS TAB -->
                <div class="tab-pane fade show active" id="photos">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-images me-2 text-primary"></i>My Photos</h5>

                        <!-- Upload Form -->
                        <form method="POST" enctype="multipart/form-data" class="mb-4">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="photo">
                            <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center" id="uploadArea">
                                <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
                                <h6 class="mt-2 mb-1">Upload a Photo</h6>
                                <p class="text-muted small mb-3">JPG or PNG, max 5MB</p>
                                <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp"
                                       data-preview="photoPreview" class="d-none">
                                <img id="photoPreview" src="" class="img-fluid rounded mb-3 d-none" style="max-height:200px" alt="">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('photoInput').click()">
                                        <i class="bi bi-folder me-1"></i>Choose Photo
                                    </button>
                                    <?php if (count($photos) === 0): ?>
                                    <label class="form-check d-flex align-items-center gap-2 mb-0">
                                        <input type="checkbox" name="set_profile" value="1" class="form-check-input" checked>
                                        <span class="small">Set as profile photo</span>
                                    </label>
                                    <?php else: ?>
                                    <label class="form-check d-flex align-items-center gap-2 mb-0">
                                        <input type="checkbox" name="set_profile" value="1" class="form-check-input">
                                        <span class="small">Set as profile photo</span>
                                    </label>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm px-4">
                                        <i class="bi bi-upload me-1"></i>Upload
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Photo Grid -->
                        <div class="row g-3">
                            <?php if (empty($photos)): ?>
                            <div class="col-12 text-center text-muted py-4">
                                <i class="bi bi-camera fs-1 opacity-25"></i>
                                <p class="mt-2">No photos yet. Upload your first photo!</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($photos as $photo): ?>
                            <div class="col-4 col-md-3">
                                <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio:1">
                                    <img src="<?= UPLOAD_URL . htmlspecialchars($photo['filename']) ?>"
                                         class="w-100 h-100" style="object-fit:cover" alt="">
                                    <?php if ($photo['is_profile']): ?>
                                    <span class="position-absolute top-0 start-0 badge-online m-1 px-2">Profile</span>
                                    <?php endif; ?>
                                    <div class="position-absolute bottom-0 start-0 end-0 p-2 d-flex gap-1"
                                         style="background:linear-gradient(transparent,rgba(0,0,0,.6))">
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this photo?')">
                                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="action" value="delete_photo">
                                            <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm py-1 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 text-muted small"><?= count($photos) ?> / <?= getSetting('max_photos', '6') ?> photos used</div>
                    </div>
                </div>

                <!-- BASIC INFO TAB -->
                <div class="tab-pane fade" id="basic">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-person me-2 text-primary"></i>Basic Information</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="profile">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Looking For</label>
                                    <select class="form-select" name="seeking">
                                        <option value="male"   <?= $user['seeking']==='male'?'selected':'' ?>>Man</option>
                                        <option value="female" <?= $user['seeking']==='female'?'selected':'' ?>>Woman</option>
                                        <option value="both"   <?= $user['seeking']==='both'?'selected':'' ?>>Either</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marital Status</label>
                                    <select class="form-select" name="marital_status">
                                        <?php foreach (['single','divorced','widowed','separated'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $user['marital_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City / Location</label>
                                    <select class="form-select" name="city">
                                        <option value="">-- Select City --</option>
                                        <?php foreach (ETHIOPIAN_CITIES as $city => $region): ?>
                                        <option value="<?= $city ?>" <?= $user['city']===$city?'selected':'' ?>><?= $city ?> (<?= $region ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <input type="text" class="form-control" name="country" value="<?= htmlspecialchars($user['country'] ?? 'Ethiopia') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ethnicity</label>
                                    <select class="form-select" name="ethnicity">
                                        <option value="">-- Select --</option>
                                        <?php foreach (ETHIOPIAN_ETHNICITIES as $eth): ?>
                                        <option value="<?= $eth ?>" <?= $user['ethnicity']===$eth?'selected':'' ?>><?= $eth ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <select class="form-select" name="religion">
                                        <option value="">-- Select --</option>
                                        <?php foreach (ETHIOPIAN_RELIGIONS as $rel): ?>
                                        <option value="<?= $rel ?>" <?= $user['religion']===$rel?'selected':'' ?>><?= $rel ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Education</label>
                                    <select class="form-select" name="education">
                                        <option value="">-- Select --</option>
                                        <?php foreach (EDUCATION_LEVELS as $edu): ?>
                                        <option value="<?= $edu ?>" <?= $user['education']===$edu?'selected':'' ?>><?= $edu ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" class="form-control" name="occupation" value="<?= htmlspecialchars($user['occupation'] ?? '') ?>" maxlength="150">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Languages</label>
                                    <input type="text" class="form-control" name="languages" value="<?= htmlspecialchars($user['languages'] ?? '') ?>" placeholder="e.g. Amharic, English, Oromiffa" maxlength="200">
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- APPEARANCE TAB -->
                <div class="tab-pane fade" id="appearance">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-eye me-2 text-primary"></i>Appearance</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="profile">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Height (cm)</label>
                                    <input type="number" class="form-control" name="height_cm" value="<?= $user['height_cm'] ?>" min="100" max="220" placeholder="e.g. 170">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Body Type</label>
                                    <select class="form-select" name="body_type">
                                        <option value="">-- Select --</option>
                                        <?php foreach (BODY_TYPES as $bt): ?>
                                        <option value="<?= $bt ?>" <?= $user['body_type']===$bt?'selected':'' ?>><?= $bt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Skin Tone</label>
                                    <select class="form-select" name="skin_tone">
                                        <option value="">-- Select --</option>
                                        <?php foreach (SKIN_TONES as $st): ?>
                                        <option value="<?= $st ?>" <?= $user['skin_tone']===$st?'selected':'' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Eye Color</label>
                                    <select class="form-select" name="eye_color">
                                        <option value="">-- Select --</option>
                                        <?php foreach (EYE_COLORS as $ec): ?>
                                        <option value="<?= $ec ?>" <?= $user['eye_color']===$ec?'selected':'' ?>><?= $ec ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Hair Type</label>
                                    <select class="form-select" name="hair_type">
                                        <option value="">-- Select --</option>
                                        <?php foreach (HAIR_TYPES as $ht): ?>
                                        <option value="<?= $ht ?>" <?= $user['hair_type']===$ht?'selected':'' ?>><?= $ht ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- LIFESTYLE TAB -->
                <div class="tab-pane fade" id="lifestyle">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-heart me-2 text-primary"></i>Lifestyle</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="profile">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Smoking</label>
                                    <select class="form-select" name="smoking">
                                        <option value="never"        <?= $user['smoking']==='never'?'selected':'' ?>>Never</option>
                                        <option value="occasionally" <?= $user['smoking']==='occasionally'?'selected':'' ?>>Occasionally</option>
                                        <option value="regularly"    <?= $user['smoking']==='regularly'?'selected':'' ?>>Regularly</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Drinking</label>
                                    <select class="form-select" name="drinking">
                                        <option value="never"        <?= $user['drinking']==='never'?'selected':'' ?>>Never</option>
                                        <option value="occasionally" <?= $user['drinking']==='occasionally'?'selected':'' ?>>Occasionally</option>
                                        <option value="regularly"    <?= $user['drinking']==='regularly'?'selected':'' ?>>Regularly</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Children</label>
                                    <select class="form-select" name="children">
                                        <option value="none"         <?= $user['children']==='none'?'selected':'' ?>>None</option>
                                        <option value="have_children"<?= $user['children']==='have_children'?'selected':'' ?>>Have Children</option>
                                        <option value="want_children"<?= $user['children']==='want_children'?'selected':'' ?>>Want Children</option>
                                        <option value="dont_want"   <?= $user['children']==='dont_want'?'selected':'' ?>>Don't Want</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Hobbies & Interests</label>
                                    <input type="text" class="form-control" name="hobbies" value="<?= htmlspecialchars($user['hobbies'] ?? '') ?>"
                                           placeholder="e.g. Coffee ceremony, Hiking, Football, Reading, Music..." maxlength="300">
                                    <div class="form-text">Separate with commas</div>
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ABOUT TAB -->
                <div class="tab-pane fade" id="aboutme">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-chat-text me-2 text-primary"></i>About Me & Bio</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="profile">
                            <div class="mb-3">
                                <label class="form-label">About Me <small class="text-muted fw-400">(max 1000 chars)</small></label>
                                <textarea class="form-control" name="about_me" rows="5" maxlength="1000"
                                          placeholder="Tell others about yourself..."><?= htmlspecialchars($user['about_me'] ?? '') ?></textarea>
                                <div class="form-text char-count" data-target="about_me">0 / 1000</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">My Ideal Partner</label>
                                <textarea class="form-control" name="ideal_partner" rows="4" maxlength="1000"
                                          placeholder="Describe your ideal partner..."><?= htmlspecialchars($user['ideal_partner'] ?? '') ?></textarea>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PRIVACY TAB -->
                <div class="tab-pane fade" id="privacy">
                    <div class="profile-card p-4">
                        <h5 class="fw-700 mb-4"><i class="bi bi-lock me-2 text-primary"></i>Privacy Settings</h5>
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="profile">
                            <div class="mb-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="hide_age" id="hideAge" value="1" <?= $user['hide_age']?'checked':'' ?>>
                                    <label class="form-check-label fw-600" for="hideAge">Hide my age from profile</label>
                                    <div class="text-muted small">Others will see "Age hidden" instead of your age.</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="hide_location" id="hideLocation" value="1" <?= $user['hide_location']?'checked':'' ?>>
                                    <label class="form-check-label fw-600" for="hideLocation">Hide my location</label>
                                    <div class="text-muted small">Others won't see your city or region.</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary fw-700 px-4"><i class="bi bi-save me-2"></i>Save Privacy Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div><!-- end tab-content -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
// Tab persistence
const hash = window.location.hash || '#photos';
document.querySelector(`[href="${hash}"]`)?.click();
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(el => {
    el.addEventListener('shown.bs.tab', e => { history.replaceState(null, '', e.target.getAttribute('href')); });
});
</script>
