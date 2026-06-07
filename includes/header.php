<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$settingsResult = $db->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
foreach ($settingsResult->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$siteName = $settings['site_name'] ?? 'Getas Real Estate';
$metaDesc = $settings['meta_description'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
  <title><?= htmlspecialchars(isset($pageTitle) ? "$pageTitle | $siteName" : $siteName) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Top bar -->
<div class="topbar d-none d-md-block">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center py-2">
      <div class="d-flex align-items-center gap-3 small">
        <span><i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($settings['phone_1'] ?? '') ?></span>
        <span><i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($settings['email'] ?? '') ?></span>
        <span class="text-gold"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($settings['working_hours'] ?? '') ?></span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php foreach (['facebook','instagram','telegram'] as $soc):
          if (!empty($settings[$soc])): ?>
          <a href="<?= $settings[$soc] ?>" target="_blank" class="topbar-social">
            <i class="bi bi-<?= $soc ?>"></i>
          </a>
        <?php endif; endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm" id="mainNav">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= SITE_URL ?>/index.php">
      <div class="logo-icon"><i class="bi bi-buildings-fill"></i></div>
      <div>
        <span class="brand-name">GETAS</span>
        <span class="brand-accent"> Real Estate</span>
        <div style="font-size:0.62rem;color:var(--gray-600);line-height:1;margin-top:1px;letter-spacing:0.06em;text-transform:uppercase">City Gate</div>
      </div>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage==='index'?'active':'' ?>" href="<?= SITE_URL ?>/index.php">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $currentPage==='properties'?'active':'' ?>" href="#" data-bs-toggle="dropdown">Apartments</a>
          <ul class="dropdown-menu shadow-sm border-0" style="min-width:260px">
            <li><a class="dropdown-item fw-600" href="<?= SITE_URL ?>/properties.php">All Apartments</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header text-gold"><i class="bi bi-building me-1"></i>City Gate 1 (Wing 1)</h6></li>
            <li><a class="dropdown-item small" href="<?= SITE_URL ?>/properties.php?tower=City+Gate+1&bedrooms=2">2 Bedroom (115–117m²)</a></li>
            <li><a class="dropdown-item small" href="<?= SITE_URL ?>/properties.php?tower=City+Gate+1&bedrooms=3">3 Bedroom (137–144m²)</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header text-gold"><i class="bi bi-building me-1"></i>City Gate 2 (Wing 2)</h6></li>
            <li><a class="dropdown-item small" href="<?= SITE_URL ?>/properties.php?tower=City+Gate+2&bedrooms=2">2 Bedroom (115–117m²)</a></li>
            <li><a class="dropdown-item small" href="<?= SITE_URL ?>/properties.php?tower=City+Gate+2&bedrooms=3">3 Bedroom (137–144m²)</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header text-gold"><i class="bi bi-building me-1"></i>City Gate 3 (Wing 3)</h6></li>
            <li><a class="dropdown-item small" href="<?= SITE_URL ?>/properties.php?tower=City+Gate+3&bedrooms=2">2 Bedroom (132–149m²)</a></li>
            <li><a class="dropdown-item small" href="<?= SITE_URL ?>/properties.php?tower=City+Gate+3&bedrooms=3">3 Bedroom (159m²)</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= SITE_URL ?>/index.php#payment-plans" onclick="document.getElementById('towerTab')&&document.getElementById('towerTab').scrollIntoView()">Payment Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage==='about'?'active':'' ?>" href="<?= SITE_URL ?>/about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage==='contact'?'active':'' ?>" href="<?= SITE_URL ?>/contact.php">Contact</a>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <?php if (isLoggedIn() && isAdmin()): ?>
          <a href="<?= SITE_URL ?>/admin/index.php" class="btn btn-sm btn-outline-gold"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
          <a href="<?= SITE_URL ?>/logout.php" class="btn btn-sm btn-gold"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/contact.php" class="btn btn-sm btn-gold"><i class="bi bi-telephone me-1"></i>Book Viewing</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
