<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $metaDesc ?? 'Habesha Connect – Ethiopia\'s premier dating and matchmaking platform. Find your perfect Ethiopian match today.' ?>">
    <meta name="keywords" content="Ethiopian dating, Habesha dating, Ethiopia singles, Ethiopian match, Habesha connect">
    <meta property="og:title" content="<?= $pageTitle ?? 'Habesha Connect' ?>">
    <meta property="og:description" content="Find your perfect Ethiopian match">
    <meta property="og:image" content="<?= SITE_URL ?>/assets/images/og-image.jpg">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Habesha Connect</title>

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?= $extraHead ?? '' ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">
