<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Get settings
$settingsResult = $db->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
foreach ($settingsResult->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$siteName = $settings['site_name'] ?? 'Getas Reality';
$tagline  = $settings['tagline'] ?? 'Premium Apartments in Addis Ababa';
$metaDesc = $settings['meta_description'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? $siteName) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
  <title><?= htmlspecialchars(isset($pageTitle) ? "$pageTitle | $siteName" : $siteName) ?></title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Top bar -->
<div class="topbar d-none d-md-block">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center py-2">
      <div class="d-flex align-items-center gap-3 small">
        <span><i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($settings['phone_1'] ?? '+251 911 234 567') ?></span>
        <span><i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($settings['email'] ?? 'info@getasreality.com') ?></span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if (!empty($settings['facebook'])): ?>
        <a href="<?= $settings['facebook'] ?>" target="_blank" class="topbar-social"><i class="bi bi-facebook"></i></a>
        <?php endif; ?>
        <?php if (!empty($settings['instagram'])): ?>
        <a href="<?= $settings['instagram'] ?>" target="_blank" class="topbar-social"><i class="bi bi-instagram"></i></a>
        <?php endif; ?>
        <?php if (!empty($settings['telegram'])): ?>
        <a href="<?= $settings['telegram'] ?>" target="_blank" class="topbar-social"><i class="bi bi-telegram"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm" id="mainNav">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= SITE_URL ?>/index.php">
      <div class="logo-icon">
        <i class="bi bi-buildings-fill"></i>
      </div>
      <div>
        <span class="brand-name">Getas</span>
        <span class="brand-accent"> Reality</span>
      </div>
    </a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav links -->
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $currentPage === 'properties' ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">Properties</a>
          <ul class="dropdown-menu shadow-sm border-0">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php">All Apartments</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header"><i class="bi bi-geo-alt-fill me-1 text-gold"></i>Summit 72</h6></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?site=Summit+72&type=1BR">1 Bedroom (61–65m²)</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?site=Summit+72&type=2BR">2 Bedroom (109–115m²)</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?site=Summit+72&type=3BR">3 Bedroom (144–151m²)</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header"><i class="bi bi-geo-alt-fill me-1 text-gold"></i>Kazanchis</h6></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?site=Kazanchis&type=1BR">1 Bedroom (61–65m²)</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?site=Kazanchis&type=2BR">2 Bedroom (109–115m²)</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?site=Kazanchis&type=3BR">3 Bedroom (144–151m²)</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="<?= SITE_URL ?>/about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contact.php">Contact</a>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <?php if (isLoggedIn()): ?>
          <?php if (isAdmin()): ?>
          <a href="<?= SITE_URL ?>/admin/index.php" class="btn btn-sm btn-outline-gold">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
          </a>
          <?php endif; ?>
          <a href="<?= SITE_URL ?>/logout.php" class="btn btn-sm btn-gold">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
          </a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/contact.php" class="btn btn-sm btn-gold">
            <i class="bi bi-telephone me-1"></i>Book Viewing
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
