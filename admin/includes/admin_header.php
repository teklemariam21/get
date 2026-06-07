<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$flash = getFlash();
$db = getDB();
$adminPage = basename($_SERVER['PHP_SELF'], '.php');

// Counts for sidebar badges
$newInquiries = $db->query("SELECT COUNT(*) FROM inquiries WHERE status='new'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle).' | Admin' : 'Admin Panel' ?> — Getas Reality</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <div class="logo-icon" style="width:34px;height:34px;font-size:1rem"><i class="bi bi-buildings-fill"></i></div>
    <div>
      <span style="font-family:'Playfair Display',serif">Getas Reality</span>
      <small>Admin Panel</small>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section">Main</div>
    <a href="<?= SITE_URL ?>/admin/index.php" class="<?= $adminPage==='index'?'active':'' ?>">
      <i class="bi bi-speedometer2"></i>Dashboard
    </a>

    <div class="sidebar-section">Properties</div>
    <a href="<?= SITE_URL ?>/admin/properties.php" class="<?= in_array($adminPage,['properties','property-add','property-edit'])?'active':'' ?>">
      <i class="bi bi-buildings"></i>All Apartments
    </a>
    <a href="<?= SITE_URL ?>/admin/property-add.php" class="<?= $adminPage==='property-add'?'active':'' ?>">
      <i class="bi bi-plus-circle"></i>Add Apartment
    </a>

    <div class="sidebar-section">CRM</div>
    <a href="<?= SITE_URL ?>/admin/inquiries.php" class="<?= $adminPage==='inquiries'?'active':'' ?>">
      <i class="bi bi-inbox"></i>Inquiries
      <?php if ($newInquiries > 0): ?>
      <span class="ms-auto badge" style="background:var(--gold);font-size:0.65rem"><?= $newInquiries ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= SITE_URL ?>/admin/testimonials.php" class="<?= $adminPage==='testimonials'?'active':'' ?>">
      <i class="bi bi-chat-quote"></i>Testimonials
    </a>

    <div class="sidebar-section">System</div>
    <a href="<?= SITE_URL ?>/admin/users.php" class="<?= $adminPage==='users'?'active':'' ?>">
      <i class="bi bi-people"></i>Users
    </a>
    <a href="<?= SITE_URL ?>/admin/settings.php" class="<?= $adminPage==='settings'?'active':'' ?>">
      <i class="bi bi-gear"></i>Settings
    </a>

    <div class="sidebar-section">Quick Links</div>
    <a href="<?= SITE_URL ?>/index.php" target="_blank">
      <i class="bi bi-box-arrow-up-right"></i>View Website
    </a>
    <a href="<?= SITE_URL ?>/logout.php" style="color:#ef4444 !important">
      <i class="bi bi-box-arrow-right"></i>Logout
    </a>
  </nav>
</aside>

<!-- Main -->
<div class="admin-main">
  <!-- Top bar -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-light d-lg-none" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
      </button>
      <nav aria-label="breadcrumb" class="d-none d-sm-block">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/admin/index.php" class="text-decoration-none" style="color:var(--gold)">Dashboard</a></li>
          <?php if (isset($breadcrumb)): ?>
          <li class="breadcrumb-item active text-muted"><?= htmlspecialchars($breadcrumb) ?></li>
          <?php endif; ?>
        </ol>
      </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
      <?php if ($newInquiries > 0): ?>
      <a href="<?= SITE_URL ?>/admin/inquiries.php" class="btn btn-sm position-relative" style="background:var(--gold-bg);color:var(--gold)">
        <i class="bi bi-bell-fill"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:var(--gold);font-size:0.6rem;transform:translate(-50%,-20%)!important"><?= $newInquiries ?></span>
      </a>
      <?php endif; ?>
      <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:30px;height:30px;background:var(--gold);font-size:0.8rem">
            <?= strtoupper(substr($_SESSION['user_name']??'A',0,1)) ?>
          </div>
          <span class="d-none d-sm-inline small fw-600"><?= htmlspecialchars($_SESSION['user_name']??'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
          <li><h6 class="dropdown-header"><?= htmlspecialchars($_SESSION['user_name']??'') ?></h6></li>
          <li><a class="dropdown-item small" href="<?= SITE_URL ?>/index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item small text-danger" href="<?= SITE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Flash message -->
  <?php if ($flash): ?>
  <div class="px-3 pt-3">
    <div class="alert alert-<?= $flash['type']==='success'?'success':($flash['type']==='danger'?'danger':'info') ?> alert-auto-dismiss d-flex align-items-center gap-2 mb-0">
      <i class="bi bi-<?= $flash['type']==='success'?'check-circle-fill':'exclamation-circle' ?>"></i>
      <?= htmlspecialchars($flash['message']) ?>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  </div>
  <?php endif; ?>

  <div class="admin-content">
