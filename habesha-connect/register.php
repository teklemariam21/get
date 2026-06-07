<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/browse.php');

$errors = [];
$step = (int)($_POST['step'] ?? $_GET['step'] ?? 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    } else {
        $step = (int)($_POST['step'] ?? 1);

        if ($step === 1) {
            // Step 1: Basic info
            $username  = trim($_POST['username'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $password  = $_POST['password'] ?? '';
            $confirm   = $_POST['confirm_password'] ?? '';
            $gender    = $_POST['gender'] ?? '';
            $seeking   = $_POST['seeking'] ?? '';
            $birthdate = $_POST['birthdate'] ?? '';

            if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_]+$/', $username))
                $errors[] = 'Username must be 3-30 characters, letters/numbers/underscore only.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors[] = 'Please enter a valid email address.';
            if (strlen($password) < 8)
                $errors[] = 'Password must be at least 8 characters.';
            if ($password !== $confirm)
                $errors[] = 'Passwords do not match.';
            if (!in_array($gender, ['male', 'female']))
                $errors[] = 'Please select your gender.';
            if (!in_array($seeking, ['male', 'female', 'both']))
                $errors[] = 'Please select who you are looking for.';
            if (empty($birthdate) || getAge($birthdate) < 18)
                $errors[] = 'You must be at least 18 years old to join.';

            if (empty($errors)) {
                $db = getDB();
                if ($db->prepare("SELECT id FROM users WHERE email=?")->execute([$email]) &&
                    $db->prepare("SELECT id FROM users WHERE email=?")->execute([$email]) &&
                    $db->query("SELECT id FROM users WHERE email='" . $db->quote($email) . "'")->fetchColumn()) {
                    $errors[] = 'An account with this email already exists.';
                }
                // Check via prepared statement
                $check = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                $check->execute([$email, $username]);
                $existing = $check->fetch();
                if ($existing) $errors[] = 'Email or username is already taken.';
            }

            if (empty($errors)) {
                $_SESSION['reg'] = compact('username', 'email', 'password', 'gender', 'seeking', 'birthdate');
                $step = 2;
            }
        } elseif ($step === 2) {
            if (empty($_SESSION['reg'])) { redirect(SITE_URL . '/register.php'); }
            // Step 2: Location & background
            $_SESSION['reg']['city']      = sanitize($_POST['city'] ?? '');
            $_SESSION['reg']['ethnicity'] = sanitize($_POST['ethnicity'] ?? '');
            $_SESSION['reg']['religion']  = sanitize($_POST['religion'] ?? '');
            $_SESSION['reg']['education'] = sanitize($_POST['education'] ?? '');
            $_SESSION['reg']['occupation']= sanitize($_POST['occupation'] ?? '');
            $step = 3;
        } elseif ($step === 3) {
            if (empty($_SESSION['reg'])) { redirect(SITE_URL . '/register.php'); }
            // Step 3: About you — finalise registration
            $_SESSION['reg']['about_me']      = sanitize(substr($_POST['about_me'] ?? '', 0, 1000));
            $_SESSION['reg']['ideal_partner'] = sanitize(substr($_POST['ideal_partner'] ?? '', 0, 1000));
            $_SESSION['reg']['hobbies']       = sanitize(substr($_POST['hobbies'] ?? '', 0, 300));

            $reg = $_SESSION['reg'];
            $db  = getDB();

            $stmt = $db->prepare("
                INSERT INTO users (username, email, password, gender, seeking, birthdate, city, ethnicity, religion, education, occupation, about_me, ideal_partner, hobbies, last_active)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
            ");
            $stmt->execute([
                $reg['username'], $reg['email'], hashPassword($reg['password']),
                $reg['gender'], $reg['seeking'], $reg['birthdate'],
                $reg['city'] ?? null, $reg['ethnicity'] ?? null, $reg['religion'] ?? null,
                $reg['education'] ?? null, $reg['occupation'] ?? null,
                $reg['about_me'] ?? null, $reg['ideal_partner'] ?? null, $reg['hobbies'] ?? null,
            ]);

            $userId = (int)$db->lastInsertId();
            unset($_SESSION['reg']);
            $_SESSION['user_id'] = $userId;

            flash('success', 'Welcome to HabeshaConnect! Complete your profile and add photos.');
            redirect(SITE_URL . '/edit-profile.php');
        }
    }
}

$pageTitle = 'Join Free - Create Your Profile';
$bodyClass = 'page-auth bg-light';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <!-- Progress Steps -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <?php foreach ([1=>'Basic Info', 2=>'Background', 3=>'About You'] as $s => $label): ?>
                <div class="d-flex align-items-center gap-2 <?= $s < 3 ? 'flex-grow-1' : '' ?>">
                    <div class="d-flex align-items-center justify-content-center rounded-circle fw-700"
                         style="width:36px;height:36px;background:<?= $step >= $s ? 'var(--primary)' : '#e5e7eb' ?>;color:<?= $step >= $s ? '#fff' : '#9ca3af' ?>;font-size:.85rem">
                        <?= $step > $s ? '✓' : $s ?>
                    </div>
                    <span class="d-none d-sm-inline small fw-600 <?= $step >= $s ? 'text-success' : 'text-muted' ?>"><?= $label ?></span>
                    <?php if ($s < 3): ?>
                    <div class="flex-grow-1 border-top border-2 <?= $step > $s ? 'border-success' : 'border-light' ?>" style="height:2px"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="auth-card">
                <div class="text-center mb-4">
                    <div class="logo-icon mx-auto mb-2"><i class="bi bi-heart-fill"></i></div>
                    <h4 class="fw-800">Join HabeshaConnect</h4>
                    <p class="text-muted small">
                        <?= ['', 'Create your account', 'Tell us about yourself', 'Share your story'][$step] ?>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2">
                    <?php foreach ($errors as $e): ?>
                    <div class="small"><i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="step" value="<?= $step ?>">

                    <?php if ($step === 1): ?>
                    <!-- STEP 1: Basic Info -->
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" name="username"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   placeholder="e.g. selam_addis" maxlength="30" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="you@email.com" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" name="password" id="password" placeholder="Min 8 chars" required>
                            </div>
                            <div class="password-strength strength-0 mt-1" id="passwordStrength"></div>
                            <small id="passwordStrengthText" class="fw-500"></small>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Repeat password" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">I am a *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check flex-fill border rounded p-3 <?= ($_POST['gender']??'') === 'male' ? 'border-success bg-success bg-opacity-10' : '' ?>">
                                <input class="form-check-input" type="radio" name="gender" id="gMale" value="male" <?= ($_POST['gender']??'') === 'male' ? 'checked' : '' ?> required>
                                <label class="form-check-label fw-600 w-100" for="gMale"><i class="bi bi-gender-male text-primary me-1"></i> Man</label>
                            </div>
                            <div class="form-check flex-fill border rounded p-3 <?= ($_POST['gender']??'') === 'female' ? 'border-success bg-success bg-opacity-10' : '' ?>">
                                <input class="form-check-input" type="radio" name="gender" id="gFemale" value="female" <?= ($_POST['gender']??'') === 'female' ? 'checked' : '' ?>>
                                <label class="form-check-label fw-600 w-100" for="gFemale"><i class="bi bi-gender-female text-danger me-1"></i> Woman</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Looking for *</label>
                        <select class="form-select" name="seeking" required>
                            <option value="">-- Select --</option>
                            <option value="male" <?= ($_POST['seeking']??'') === 'male' ? 'selected' : '' ?>>Man</option>
                            <option value="female" <?= ($_POST['seeking']??'') === 'female' ? 'selected' : '' ?>>Woman</option>
                            <option value="both" <?= ($_POST['seeking']??'') === 'both' ? 'selected' : '' ?>>Either</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Date of Birth * <small class="text-muted fw-400">(Must be 18+)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            <input type="date" class="form-control" name="birthdate"
                                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                   value="<?= htmlspecialchars($_POST['birthdate'] ?? '') ?>" required>
                        </div>
                    </div>

                    <?php elseif ($step === 2): ?>
                    <!-- STEP 2: Background -->
                    <div class="mb-3">
                        <label class="form-label">City / Location</label>
                        <select class="form-select" name="city">
                            <option value="">-- Select City --</option>
                            <?php foreach (ETHIOPIAN_CITIES as $city => $region): ?>
                            <option value="<?= $city ?>"><?= $city ?> (<?= $region ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ethnicity</label>
                        <select class="form-select" name="ethnicity">
                            <option value="">-- Select Ethnicity --</option>
                            <?php foreach (ETHIOPIAN_ETHNICITIES as $eth): ?>
                            <option value="<?= $eth ?>"><?= $eth ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Religion</label>
                        <select class="form-select" name="religion">
                            <option value="">-- Select Religion --</option>
                            <?php foreach (ETHIOPIAN_RELIGIONS as $rel): ?>
                            <option value="<?= $rel ?>"><?= $rel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Education Level</label>
                        <select class="form-select" name="education">
                            <option value="">-- Select --</option>
                            <?php foreach (EDUCATION_LEVELS as $edu): ?>
                            <option value="<?= $edu ?>"><?= $edu ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Occupation</label>
                        <input type="text" class="form-control" name="occupation" placeholder="e.g. Engineer, Teacher, Doctor..." maxlength="150">
                    </div>

                    <?php elseif ($step === 3): ?>
                    <!-- STEP 3: About -->
                    <div class="mb-3">
                        <label class="form-label">About Me <small class="text-muted fw-400">(max 1000 chars)</small></label>
                        <textarea class="form-control" name="about_me" rows="4" maxlength="1000"
                                  placeholder="Tell others about yourself, your personality, interests, and what makes you unique..."><?= htmlspecialchars($_POST['about_me'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">My Ideal Partner</label>
                        <textarea class="form-control" name="ideal_partner" rows="3" maxlength="1000"
                                  placeholder="Describe the qualities you're looking for in your ideal partner..."><?= htmlspecialchars($_POST['ideal_partner'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Hobbies & Interests</label>
                        <input type="text" class="form-control" name="hobbies"
                               placeholder="e.g. Coffee ceremony, Hiking, Reading, Music, Football..."
                               value="<?= htmlspecialchars($_POST['hobbies'] ?? '') ?>" maxlength="300">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                        <label class="form-check-label small" for="agreeTerms">
                            I agree to the <a href="#" class="text-success">Terms of Service</a> and <a href="#" class="text-success">Privacy Policy</a>
                        </label>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-700">
                            <?= $step < 3 ? 'Continue <i class="bi bi-arrow-right ms-1"></i>' : '<i class="bi bi-heart-fill me-2"></i>Create My Profile' ?>
                        </button>
                    </div>

                    <?php if ($step > 1): ?>
                    <a href="<?= SITE_URL ?>/register.php?step=<?= $step - 1 ?>" class="btn btn-link w-100 mt-2 text-muted small">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                    <?php endif; ?>
                </form>

                <div class="text-center mt-4 pt-3 border-top small text-muted">
                    Already have an account? <a href="<?= SITE_URL ?>/login.php" class="text-success fw-600">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
