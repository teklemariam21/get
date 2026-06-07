<?php $currentPage = basename($_SERVER['PHP_SELF'], '.php'); ?>
<nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>/">
            <span class="brand-icon"><i class="fas fa-microchip"></i></span>
            <span class="brand-text">Hitech<span class="brand-accent">Computer</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= SITE_URL ?>/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'courses' ? 'active' : '' ?>" href="<?= SITE_URL ?>/courses.php">Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'tutorials' ? 'active' : '' ?>" href="<?= SITE_URL ?>/tutorials.php">Tutorials</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contact.php">Contact</a>
                </li>
            </ul>
            <a href="tel:0968752100" class="btn btn-glow ms-3">
                <i class="fas fa-phone-alt me-1"></i> Call Now
            </a>
        </div>
    </div>
</nav>
