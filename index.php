<?php
require_once __DIR__ . '/includes/functions.php';

$categories = $pdo->query('SELECT id, name, slug, default_thumbnail FROM categories ORDER BY name')->fetchAll();

$page_title = 'Home';
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-banner">
    <p class="status-pill mb-3">Drameeo · Fan Hub Plus</p>
    <h1>Multi-fandom entertainment, one dark streaming hub.</h1>
    <p>Browse anime, gaming, movies, TV, K-Pop, comics, manga, and cosplay. Save and submit after you log in.</p>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-accent" href="<?= e(url('explore.php')) ?>">Explore</a>
        <?php if (!is_logged_in()): ?>
            <a class="btn btn-ghost" href="<?= e(url('register.php')) ?>">Register</a>
        <?php endif; ?>
    </div>
</section>

<h2 class="h5 mb-3">Categories</h2>
<div class="h-scroll">
    <?php foreach ($categories as $cat): ?>
        <article class="media-card">
            <img class="card-img-top" src="<?= e(thumbnail_url($cat['default_thumbnail'])) ?>" alt="<?= e($cat['name']) ?>">
            <div class="p-3">
                <h3 class="h6 mb-1"><?= e($cat['name']) ?></h3>
                <a href="<?= e(url('explore.php?category=' . urlencode($cat['slug']))) ?>">Open</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
