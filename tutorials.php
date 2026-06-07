<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Tutorials & Tips';
$pageDesc = 'Free tech tutorials, tips and tricks from Hitechcomputer. Learn mobile phone repair, computer repair, and more.';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="page-hero">
    <div class="container" data-aos="fade-up">
        <p class="text-uppercase" style="color:var(--cyan);font-size:0.8rem;letter-spacing:2px;margin-bottom:0.5rem">Free Learning</p>
        <h1>Tutorials &amp; <span class="neon-text">Tips</span></h1>
        <p>Expert tech guides, tips &amp; tricks from our certified technicians</p>
    </div>
</div>

<section>
    <div class="container">
        <!-- Search -->
        <div class="search-bar-wrap" data-aos="fade-up">
            <input type="text" id="search-input" placeholder="Search tutorials, tips...">
            <button id="search-btn"><i class="fas fa-search"></i></button>
        </div>

        <!-- Filter -->
        <div class="filter-tabs" data-aos="fade-up">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="tutorial">Tutorials</button>
            <button class="filter-btn" data-filter="tip">Tips</button>
            <button class="filter-btn" data-filter="trick">Tricks</button>
            <button class="filter-btn" data-filter="news">News</button>
        </div>

        <!-- Posts Container -->
        <div class="row gy-4" id="posts-container">
            <!-- Loaded via AJAX -->
            <div class="col-12 loading-spinner"><div class="spinner-ring"></div></div>
        </div>
    </div>
</section>

<script>
// Auto-load posts on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch(`${SITE_URL}/ajax/load-posts.php?category=all&search=`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('posts-container').innerHTML = html;
            AOS.refresh();
        });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
