<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>HabeshaConnect Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
    body { font-family: 'Poppins', sans-serif; }
    .admin-sidebar .brand { padding: 1.2rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.08); }
    .admin-nav-section { padding: 0.75rem 1.25rem 0.35rem; font-size: .65rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.3); font-weight: 600; }
    .badge-sidebar { font-size:.6rem; padding:2px 6px; border-radius:8px; }
    </style>
</head>
<body>
