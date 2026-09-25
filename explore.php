<?php
require_once __DIR__ . '/includes/functions.php';

// Categories for filter
$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();

// Genres list
$genresStmt = $pdo->query('SELECT DISTINCT genre FROM posts WHERE genre IS NOT NULL AND genre != "" ORDER BY genre');
$availableGenres = $genresStmt->fetchAll(PDO::FETCH_COLUMN);

// Content Types
$contentTypes = ['article', 'video', 'audio', 'image'];

// Current filter parameters
$q = trim((string) ($_GET['q'] ?? ''));
$categorySlug = trim((string) ($_GET['category'] ?? ''));
$type = trim((string) ($_GET['type'] ?? ''));
$genre = trim((string) ($_GET['genre'] ?? ''));
$year = trim((string) ($_GET['year'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'latest'));

// Build Query
$where = ['p.status = "approved"'];
$params = [];

if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.description LIKE ? OR p.genre LIKE ?)';
    $term = '%' . $q . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($type !== '' && in_array($type, $contentTypes, true)) {
    $where[] = 'p.type = ?';
    $params[] = $type;
}

if ($genre !== '') {
    $where[] = 'p.genre = ?';
    $params[] = $genre;
}

if ($year !== '' && is_numeric($year)) {
    $where[] = 'p.release_year = ?';
    $params[] = (int) $year;
}

$orderBy = 'p.created_at DESC';
if ($sort === 'popular') {
    $orderBy = 'p.views DESC, p.popularity DESC';
} elseif ($sort === 'alpha') {
    $orderBy = 'p.title ASC';
} elseif ($sort === 'oldest') {
    $orderBy = 'p.created_at ASC';
}

$sql = '
    SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.username 
    FROM posts p 
    JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE ' . implode(' AND ', $where) . ' 
    ORDER BY ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Breadcrumbs
$crumbs = ['Home' => 'index.php', 'Explore' => null];
if ($categorySlug !== '') {
    $matchedCat = array_filter($categories, fn($c) => $c['slug'] === $categorySlug);
    if (!empty($matchedCat)) {
        $cName = reset($matchedCat)['name'];
        $crumbs = ['Home' => 'index.php', 'Explore' => 'explore.php', $cName => null];
    }
}

$page_title = 'Explore Content · Fandom Universe';
require_once __DIR__ . '/includes/header.php';
?>

<?= render_breadcrumbs($crumbs) ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 text-white">Fandom Content Explorer</h1>
        <p class="text-muted small mb-0">Browse and filter curated articles, videos, soundtracks, and media across 8 fandom domains.</p>
    </div>
    <?php if (is_logged_in()): ?>
        <a href="<?= e(url('submit-post.php')) ?>" class="btn btn-accent btn-sm">+ Submit Your Content</a>
    <?php endif; ?>
</div>

<!-- Multi-level Filter Form -->
<form method="get" action="<?= e(url('explore.php')) ?>" class="filter-bar mb-4">
    <div class="row g-2 align-items-center">
        <!-- Search Keyword -->
        <div class="col-md-3">
            <input type="search" name="q" class="form-control form-control-sm" placeholder="Search title or tags…" value="<?= e($q) ?>">
        </div>

        <!-- Category Dropdown -->
        <div class="col-6 col-md-2">
            <select name="category" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Content Type -->
        <div class="col-6 col-md-2">
            <select name="type" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="">All Types</option>
                <option value="article" <?= $type === 'article' ? 'selected' : '' ?>>📄 Article</option>
                <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>🎬 Video</option>
                <option value="audio" <?= $type === 'audio' ? 'selected' : '' ?>>🎵 Audio</option>
                <option value="image" <?= $type === 'image' ? 'selected' : '' ?>>🖼️ Image</option>
            </select>
        </div>

        <!-- Genre -->
        <div class="col-6 col-md-2">
            <select name="genre" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="">All Genres</option>
                <?php foreach ($availableGenres as $g): ?>
                    <option value="<?= e($g) ?>" <?= $genre === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Sort Option -->
        <div class="col-6 col-md-2">
            <select name="sort" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>⚡ Latest</option>
                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>🔥 Most Popular</option>
                <option value="alpha" <?= $sort === 'alpha' ? 'selected' : '' ?>>🔤 Alphabetical (A-Z)</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-accent w-100">Filter</button>
            <?php if ($q !== '' || $categorySlug !== '' || $type !== '' || $genre !== '' || $year !== '' || $sort !== 'latest'): ?>
                <a href="<?= e(url('explore.php')) ?>" class="btn btn-sm btn-ghost" title="Reset Filters">✕</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Content Grid (Responsive: 1-col mobile, 2-col tablet, 4-col desktop) -->
<?php if (empty($posts)): ?>
    <div class="text-center py-5">
        <div class="fs-1 mb-2">🔍</div>
        <h2 class="h5 text-white">No content found</h2>
        <p class="text-muted">Try adjusting your search criteria or clear your filters.</p>
        <a href="<?= e(url('explore.php')) ?>" class="btn btn-sm btn-accent">Show All Content</a>
    </div>
<?php else: ?>
    <div class="mb-3">
        <small class="text-muted">Showing <?= count($posts) ?> content item(s)</small>
    </div>
    <div class="responsive-grid-4 mb-5">
        <?php foreach ($posts as $post): ?>
            <?php 
                $isSaved = is_logged_in() ? is_bookmarked($pdo, (int)$post['id'], current_user_id()) : false;
                $rating = get_post_rating_summary($pdo, (int)$post['id']);
            ?>
            <article class="media-card h-100">
                <a href="<?= e(url('post.php?id=' . $post['id'])) ?>" class="text-decoration-none">
                    <?= render_card_visual($post['category_name'], $post['category_slug'], $post['type'], $post['thumbnail']) ?>
                </a>
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="<?= e(url('explore.php?category=' . urlencode($post['category_slug']))) ?>" 
                           class="badge-fandom text-decoration-none">
                            <?= e($post['category_name']) ?>
                        </a>
                        <span class="small text-muted">
                            👁️ <?= number_format((int)$post['views']) ?>
                        </span>
                    </div>
                    <h2 class="h6 mb-2">
                        <a href="<?= e(url('post.php?id=' . $post['id'])) ?>" class="text-white text-decoration-none">
                            <?= e($post['title']) ?>
                        </a>
                    </h2>
                    <div class="small text-muted mb-2">
                        <?php if (!empty($post['genre'])): ?>
                            <span class="badge bg-dark border text-muted me-1"><?= e($post['genre']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($post['release_year'])): ?>
                            <span><?= e($post['release_year']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="small text-muted mb-3 flex-grow-1">
                        <?= e(mb_strimwidth($post['description'] ?? '', 0, 95, '...')) ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: var(--border-color) !important;">
                        <span class="small text-warning">
                            ★ <?= $rating['avg'] > 0 ? $rating['avg'] : 'Unrated' ?>
                        </span>
                        <div class="d-flex gap-2">
                            <a href="<?= e(url('post.php?id=' . $post['id'])) ?>" class="btn btn-sm btn-ghost">View</a>
                            <?php if (is_logged_in()): ?>
                                <button type="button" 
                                        class="btn btn-sm <?= $isSaved ? 'btn-accent is-saved' : 'btn-ghost' ?> js-bookmark" 
                                        data-post-id="<?= (int)$post['id'] ?>">
                                    <?= $isSaved ? '★ Saved' : '☆ Save' ?>
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="btn btn-sm btn-ghost js-guest-guard" 
                                        data-guard="save">
                                    ☆ Save
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>