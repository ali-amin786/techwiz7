<?php
require_once __DIR__ . '/includes/functions.php';

// Fetch Categories
$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();

// Filters
$categorySlug = trim((string) ($_GET['category'] ?? ''));
$tag = trim((string) ($_GET['tag'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));

$where = ['1=1'];
$params = [];

if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($tag !== '' && in_array($tag, ['Limited Edition', 'Pre-Order', 'Collectible'], true)) {
    $where[] = 'm.tag = ?';
    $params[] = $tag;
}

if ($q !== '') {
    $where[] = '(m.title LIKE ? OR m.description LIKE ?)';
    $term = '%' . $q . '%';
    $params[] = $term;
    $params[] = $term;
}

$sql = '
    SELECT m.*, c.name AS category_name, c.slug AS category_slug 
    FROM merchandise_items m 
    JOIN categories c ON m.category_id = c.id 
    WHERE ' . implode(' AND ', $where) . ' 
    ORDER BY m.views DESC, m.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$crumbs = ['Home' => 'index.php', 'Merch Showcase' => null];

$page_title = 'Merchandise Showcase · Fan Hub Plus';
require_once __DIR__ . '/includes/header.php';
?>

<?= render_breadcrumbs($crumbs) ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 text-white">Merchandise Showcase & Resource Library</h1>
        <p class="text-muted small mb-0">Discover authentic collectibles, pre-order drops, and limited edition fandom gear for discovery and community tracking.</p>
    </div>
</div>

<div class="alert alert-info py-2 px-3 small mb-4" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); color: var(--text-main);">
    ℹ️ <strong>Discovery Notice:</strong> Items shown are for community discovery and fandom appreciation. Fan Hub Plus does not process payments or sell merchandise directly.
</div>

<!-- Filters Toolbar -->
<form method="get" action="<?= e(url('merch.php')) ?>" class="filter-bar mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control form-control-sm" placeholder="Search gear, pins, apparel…" value="<?= e($q) ?>">
        </div>
        <div class="col-6 col-md-3">
            <select name="category" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="tag" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="">All Tags</option>
                <option value="Limited Edition" <?= $tag === 'Limited Edition' ? 'selected' : '' ?>>⭐ Limited Edition</option>
                <option value="Pre-Order" <?= $tag === 'Pre-Order' ? 'selected' : '' ?>>⏳ Pre-Order</option>
                <option value="Collectible" <?= $tag === 'Collectible' ? 'selected' : '' ?>>💎 Collectible</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-accent w-100">Filter</button>
            <?php if ($q !== '' || $categorySlug !== '' || $tag !== ''): ?>
                <a href="<?= e(url('merch.php')) ?>" class="btn btn-sm btn-ghost" title="Reset">✕</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Merchandise Grid (Responsive: 1-col mobile, 2-col tablet, 4-col desktop) -->
<?php if (empty($items)): ?>
    <div class="text-center py-5">
        <div class="fs-1 mb-2">🎁</div>
        <h2 class="h5 text-white">No merchandise items found</h2>
        <p class="text-muted">Try adjusting your filters or search keywords.</p>
        <a href="<?= e(url('merch.php')) ?>" class="btn btn-sm btn-accent">View All Merchandise</a>
    </div>
<?php else: ?>
    <div class="responsive-grid-4 mb-5">
        <?php foreach ($items as $item): ?>
            <?php 
                $tagClass = match($item['tag']) {
                    'Limited Edition' => 'bg-warning text-dark',
                    'Pre-Order' => 'bg-info text-dark',
                    'Collectible' => 'bg-primary text-white',
                    default => 'bg-secondary text-white',
                };
            ?>
            <article class="media-card h-100 d-flex flex-column">
                <div class="card-art-cover grad-<?= e(preg_replace('/[^a-z0-9_-]/', '', strtolower($item['category_slug']))) ?>">
                    <div class="card-art-pattern"></div>
                    <div class="card-art-badge"><?= e($item['tag'] ?: 'Merch') ?></div>
                    <div class="card-art-center">
                        <span class="card-art-icon">🎁</span>
                        <span class="card-art-cat"><?= e($item['category_name']) ?></span>
                    </div>
                </div>
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge-fandom">
                            <?= e($item['category_name']) ?>
                        </span>
                        <?php if (!empty($item['tag'])): ?>
                            <span class="badge <?= $tagClass ?>"><?= e($item['tag']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="h6 text-white mb-2"><?= e($item['title']) ?></h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        <?= e(mb_strimwidth($item['description'] ?? '', 0, 95, '...')) ?>
                    </p>
                    <?php if (!empty($item['release_date'])): ?>
                        <div class="small text-info mb-2">
                            📅 Drop Date: <?= date('M d, Y', strtotime($item['release_date'])) ?>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto" style="border-color: var(--border-color) !important;">
                        <span class="small text-muted">👁️ <?= number_format((int)$item['views']) ?> views</span>
                        <button type="button" class="btn btn-sm btn-ghost" data-bs-toggle="modal" data-bs-target="#merchModal<?= $item['id'] ?>">
                            Details
                        </button>
                    </div>
                </div>
            </article>

            <!-- Merch Detail Modal -->
            <div class="modal fade" id="merchModal<?= $item['id'] ?>" tabindex="-1" aria-labelledby="merchLabel<?= $item['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-modal">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <span class="badge <?= $tagClass ?> mb-1"><?= e($item['tag'] ?: 'Merchandise') ?></span>
                                <h3 class="modal-title h5 text-white" id="merchLabel<?= $item['id'] ?>"><?= e($item['title']) ?></h3>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-light" style="line-height: 1.7;"><?= nl2br(e($item['description'])) ?></p>
                            <hr style="border-color: var(--border-color);">
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Fandom: <strong class="text-white"><?= e($item['category_name']) ?></strong></span>
                                <span>Views: <strong class="text-white"><?= number_format((int)$item['views']) ?></strong></span>
                            </div>
                            <?php if (!empty($item['release_date'])): ?>
                                <div class="mt-2 small text-info">
                                    Expected Delivery / Drop: <strong><?= date('F j, Y', strtotime($item['release_date'])) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-sm btn-ghost" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>