<?php
$saved_count = $saved_count ?? 0;
$nav_inner = function () use ($saved_count) {
    ?>
    <a class="brand" href="<?= e(url('index.php')) ?>">
        <span class="brand-mark">FH</span>
        <span class="brand-text">Fan Hub Plus</span>
    </a>
    <nav class="sidebar-nav">
        <a class="nav-link<?= active_nav('index.php') ?>" href="<?= e(url('index.php')) ?>">Home</a>
        <a class="nav-link<?= active_nav('explore.php') ?>" href="<?= e(url('explore.php')) ?>">Explore</a>
        <a class="nav-link" href="<?= e(url('explore.php')) ?>">Categories</a>
        <a class="nav-link<?= active_nav('characters.php') ?>" href="<?= e(url('characters.php')) ?>">Character Profiles</a>
        <a class="nav-link<?= active_nav('merch.php') ?>" href="<?= e(url('merch.php')) ?>">Merch Showcase</a>
        <a class="nav-link<?= active_nav('upcoming.php') ?>" href="<?= e(url('upcoming.php')) ?>">Upcoming Releases</a>
        <a class="nav-link<?= active_nav('events-map.php') ?>" href="<?= e(url('events-map.php')) ?>">Event Map</a>
        <a class="nav-link<?= active_nav('feedback.php') ?>" href="<?= e(url('feedback.php')) ?>">Feedback</a>
        <a class="nav-link<?= active_nav('sitemap.php') ?>" href="<?= e(url('sitemap.php')) ?>">Sitemap</a>
        <?php if (is_logged_in()): ?>
            <a class="nav-link<?= active_nav('saved-items.php') ?>" href="<?= e(url('saved-items.php')) ?>">Saved Content (<span id="sidebarSavedCount"><?= (int) $saved_count ?></span>)</a>
            <a class="nav-link<?= active_nav('dashboard.php') ?>" href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
            <a class="nav-link<?= active_nav('submit-post.php') ?>" href="<?= e(url('submit-post.php')) ?>">Submit Post</a>
            <?php if (is_admin()): ?>
                <a class="nav-link nav-admin-link" href="<?= e(url('admin/index.php')) ?>"><span class="badge bg-danger me-1">Admin</span> Control Panel</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
        <button type="button" class="nav-link btn-reset" id="a11ySettings">Settings / Accessibility</button>
        <?php if (is_logged_in()): ?>
            <a class="nav-link" href="<?= e(url('logout.php')) ?>">Logout</a>
        <?php else: ?>
            <a class="nav-link<?= active_nav('login.php') ?>" href="<?= e(url('login.php')) ?>">Login</a>
        <?php endif; ?>
    </div>
    <?php
};
?>
<aside class="sidebar d-none d-md-flex">
    <?php $nav_inner(); ?>
</aside>
<div class="offcanvas offcanvas-start sidebar-offcanvas d-md-none" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header">
        <h2 class="h6 mb-0">Menu</h2>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body sidebar d-flex">
        <?php $nav_inner(); ?>
    </div>
</div>
