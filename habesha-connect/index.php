<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/browse.php');
}

$db = getDB();

// Stats
$totalUsers   = (int) $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$totalMatches = (int) $db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$onlineNow    = (int) $db->query("SELECT COUNT(*) FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND is_active=1")->fetchColumn();

// Featured members (6 newest with photos)
$featured = $db->query("
    SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age
    FROM users WHERE is_active=1 AND email_verified=1
    ORDER BY created_at DESC LIMIT 6
")->fetchAll();

$pageTitle = 'Ethiopian Dating & Matchmaking';
$bodyClass = 'page-home';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- ===================== HERO ===================== -->
<section class="hero-section">
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <div class="hero-badge fade-in-up">
                    <i class="bi bi-fire"></i> Ethiopia's #1 Dating Platform
                </div>
                <h1 class="hero-title fade-in-up delay-1">
                    Find Your <span class="highlight">Habesha</span><br>Soulmate Today
                </h1>
                <p class="hero-subtitle fade-in-up delay-2">
                    Connect with thousands of Ethiopian singles across the nation and diaspora.
                    Real profiles, real connections, built on shared culture and values.
                </p>

                <div class="hero-stats fade-in-up delay-2">
                    <div>
                        <div class="hero-stat-number" data-count="<?= $totalUsers ?>"><?= number_format($totalUsers) ?>+</div>
                        <div class="hero-stat-label">Members</div>
                    </div>
                    <div>
                        <div class="hero-stat-number" data-count="<?= $totalMatches ?>"><?= number_format($totalMatches) ?>+</div>
                        <div class="hero-stat-label">Matches Made</div>
                    </div>
                    <div>
                        <div class="hero-stat-number" data-count="<?= $onlineNow ?>"><?= $onlineNow ?>+</div>
                        <div class="hero-stat-label">Online Now</div>
                    </div>
                </div>

                <div class="hero-cta d-flex gap-3 flex-wrap fade-in-up delay-3">
                    <a href="register.php" class="btn btn-warning btn-lg fw-700 px-4">
                        <i class="bi bi-heart-fill me-2"></i>Join Free Today
                    </a>
                    <a href="browse.php" class="btn btn-outline-light btn-lg px-4">
                        Browse Members
                    </a>
                </div>

                <div class="mt-3 d-flex align-items-center gap-2 fade-in-up delay-3">
                    <div class="d-flex">
                        <?php foreach (array_slice($featured, 0, 4) as $i => $m): ?>
                        <img src="<?= getProfilePhoto($m['profile_photo'], $m['gender']) ?>"
                             class="rounded-circle border-2 border-white"
                             width="34" height="34"
                             style="object-fit:cover;margin-left:<?= $i > 0 ? '-10px' : '0' ?>;border:2px solid #fff"
                             alt="">
                        <?php endforeach; ?>
                    </div>
                    <span class="text-white-75 small fw-500">Join <?= number_format($totalUsers) ?>+ singles already waiting</span>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-flex justify-content-end">
                <div class="hero-profiles-grid">
                    <?php foreach (array_slice($featured, 0, 6) as $i => $m): ?>
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $m['id'] ?>"
                       class="hero-profile-card <?= $i === 1 ? 'mt-4' : '' ?> <?= $i === 3 ? 'mt-4' : '' ?>">
                        <img src="<?= getProfilePhoto($m['profile_photo'], $m['gender']) ?>"
                             alt="<?= htmlspecialchars($m['username']) ?>">
                        <div class="card-overlay">
                            <div>
                                <div class="name"><?= htmlspecialchars($m['username']) ?></div>
                                <div class="age"><?= $m['age'] ?> • <?= htmlspecialchars($m['city'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php if (isOnline($m['last_active'])): ?>
                        <span class="badge-online position-absolute" style="top:8px;right:8px">Online</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== HOW IT WORKS ===================== -->
<section class="py-6 bg-white" style="padding:80px 0">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Simple Steps</span>
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Start your love journey in just 3 easy steps</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <span class="step-number">1</span>
                    <div class="step-icon"><i class="bi bi-person-plus-fill"></i></div>
                    <h5 class="step-title">Create Your Profile</h5>
                    <p class="step-desc">Sign up free in minutes. Add photos, share your story, and tell us what you're looking for in a partner.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <span class="step-number">2</span>
                    <div class="step-icon"><i class="bi bi-search-heart"></i></div>
                    <h5 class="step-title">Find Your Match</h5>
                    <p class="step-desc">Browse thousands of profiles. Use our smart filters to find singles by ethnicity, city, religion, and more.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <span class="step-number">3</span>
                    <div class="step-icon"><i class="bi bi-chat-heart-fill"></i></div>
                    <h5 class="step-title">Connect & Chat</h5>
                    <p class="step-desc">Like profiles, get matched, and start chatting. Take the first step towards your forever partner.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FEATURED MEMBERS ===================== -->
<section style="padding:80px 0;background:var(--surface)">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5">
            <div>
                <span class="section-badge">New Members</span>
                <h2 class="section-title mb-0">Meet Our Singles</h2>
            </div>
            <a href="<?= SITE_URL ?>/browse.php" class="btn btn-outline-primary fw-600">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3">
            <?php foreach ($featured as $member): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="member-card">
                    <div class="member-photo-wrap">
                        <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>">
                            <img src="<?= getProfilePhoto($member['profile_photo'], $member['gender']) ?>"
                                 alt="<?= htmlspecialchars($member['username']) ?>">
                        </a>
                        <div class="member-badge">
                            <?php if (isOnline($member['last_active'])): ?>
                            <span class="badge-online">Online</span>
                            <?php endif; ?>
                            <?php if ($member['is_premium']): ?>
                            <span class="badge-premium"><i class="bi bi-star-fill"></i> VIP</span>
                            <?php endif; ?>
                        </div>
                        <div class="photo-overlay">
                            <div class="member-actions">
                                <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>" class="btn btn-light btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/login.php" class="btn btn-like btn-sm">
                                    <i class="bi bi-heart"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="member-info">
                        <a href="<?= SITE_URL ?>/profile.php?id=<?= $member['id'] ?>" class="member-name">
                            <?= htmlspecialchars($member['username']) ?>
                            <?php if ($member['is_verified']): ?>
                            <i class="bi bi-patch-check-fill text-primary" style="font-size:0.8rem"></i>
                            <?php endif; ?>
                        </a>
                        <div class="member-meta">
                            <span><i class="bi bi-calendar2"></i><?= $member['age'] ?></span>
                            <?php if ($member['city']): ?>
                            <span><i class="bi bi-geo-alt"></i><?= htmlspecialchars($member['city']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($member['ethnicity']): ?>
                        <div class="member-tags">
                            <span class="member-tag"><?= htmlspecialchars($member['ethnicity']) ?></span>
                            <?php if ($member['religion']): ?>
                            <span class="member-tag"><?= htmlspecialchars($member['religion']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-lg fw-700 px-5">
                <i class="bi bi-heart-fill me-2"></i>Join Free & See More
            </a>
        </div>
    </div>
</section>

<!-- ===================== STATS ===================== -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-number" data-count="<?= $totalUsers ?>"><?= number_format($totalUsers) ?>+</span>
                    <span class="stat-label">Registered Members</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-number" data-count="<?= $totalMatches ?>"><?= number_format($totalMatches) ?>+</span>
                    <span class="stat-label">Successful Matches</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-number" data-count="11"><?= 11 ?></span>
                    <span class="stat-label">Ethiopian Regions</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <span class="stat-number" data-count="<?= $onlineNow ?>"><?= $onlineNow ?>+</span>
                    <span class="stat-label">Online Right Now</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FEATURES ===================== -->
<section style="padding:80px 0;background:#fff">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <span class="section-badge">Why Choose Us</span>
                <h2 class="section-title">Built for Ethiopians,<br>by Ethiopians</h2>
                <p class="text-muted mb-4">We understand Ethiopian culture and values. Our platform is designed to help you find meaningful connections.</p>
                <div class="d-flex flex-column gap-2">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <div class="feature-title">Verified & Safe Profiles</div>
                            <div class="feature-desc">We manually review profiles and verify photos to keep our community safe and authentic.</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <div class="feature-title">Search by Ethiopian City & Region</div>
                            <div class="feature-desc">Find matches in Addis Ababa, Mekelle, Gondar, Dire Dawa, or anywhere in the diaspora.</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="feature-title">Filter by Ethnicity & Religion</div>
                            <div class="feature-desc">Match with Amhara, Oromo, Tigrayan, and more. Find someone who shares your faith and background.</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-chat-dots-fill"></i></div>
                        <div>
                            <div class="feature-title">Real-Time Messaging</div>
                            <div class="feature-desc">Chat privately and securely with your matches. No third party reads your conversations.</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-phone-fill"></i></div>
                        <div>
                            <div class="feature-title">Pay via TeleBirr & CBE Birr</div>
                            <div class="feature-desc">Upgrade to Premium using local Ethiopian payment methods — no international card needed.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 text-center card-hover">
                            <i class="bi bi-heart-fill text-danger fs-1 mb-2"></i>
                            <div class="fw-700">Smart Matching</div>
                            <small class="text-muted">AI-powered compatibility</small>
                        </div>
                    </div>
                    <div class="col-6 mt-4">
                        <div class="card border-0 shadow-sm rounded-3 p-3 text-center card-hover">
                            <i class="bi bi-camera-fill text-primary fs-1 mb-2"></i>
                            <div class="fw-700">Photo Albums</div>
                            <small class="text-muted">Up to 6 photos per profile</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 text-center card-hover">
                            <i class="bi bi-bell-fill text-warning fs-1 mb-2"></i>
                            <div class="fw-700">Instant Alerts</div>
                            <small class="text-muted">Know when you're liked</small>
                        </div>
                    </div>
                    <div class="col-6 mt-4">
                        <div class="card border-0 shadow-sm rounded-3 p-3 text-center card-hover">
                            <i class="bi bi-lock-fill text-success fs-1 mb-2"></i>
                            <div class="fw-700">Private & Secure</div>
                            <small class="text-muted">Your data stays safe</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section style="padding:80px 0;background:var(--surface)">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Success Stories</span>
            <h2 class="section-title">Love Stories from Our Members</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"I found my husband on HabeshaConnect! We met online, talked for months, and got married in Addis Ababa last year. This platform changed my life!"</p>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= SITE_URL ?>/assets/images/default-female.png" class="rounded-circle" alt="">
                        <div>
                            <div class="testimonial-name">Tigist B.</div>
                            <div class="testimonial-loc"><i class="bi bi-geo-alt me-1"></i>Addis Ababa</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"As a diaspora Ethiopian in the US, I struggled to find someone who understood my culture. HabeshaConnect connected me with my fiancée in Gondar!"</p>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= SITE_URL ?>/assets/images/default-male.png" class="rounded-circle" alt="">
                        <div>
                            <div class="testimonial-name">Solomon T.</div>
                            <div class="testimonial-loc"><i class="bi bi-geo-alt me-1"></i>Washington DC, USA</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"The filters made it so easy to find someone who shares my Orthodox faith and Tigrayan background. We've been together 2 years now!"</p>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= SITE_URL ?>/assets/images/default-female.png" class="rounded-circle" alt="">
                        <div>
                            <div class="testimonial-name">Mekdes H.</div>
                            <div class="testimonial-loc"><i class="bi bi-geo-alt me-1"></i>Mekelle</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CTA BANNER ===================== -->
<section style="background:var(--gradient);padding:70px 0">
    <div class="container text-center">
        <h2 class="text-white fw-800 fs-1 mb-3">Ready to Find Your Match?</h2>
        <p class="text-white-75 fs-5 mb-4 mx-auto" style="max-width:500px">Join thousands of Ethiopian singles. Create your free profile in minutes.</p>
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-warning btn-lg fw-700 px-5 py-3" style="font-size:1.1rem">
            <i class="bi bi-heart-fill me-2"></i>Create Free Profile
        </a>
        <div class="mt-3 text-white-50 small">No credit card required • 100% Free to join</div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
