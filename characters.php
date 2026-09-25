<?php
require_once __DIR__ . '/includes/functions.php';

// Fetch Categories
$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();

// Filters
$categorySlug = trim((string) ($_GET['category'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));

$where = ['1=1'];
$params = [];

if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($q !== '') {
    $where[] = '(cp.name LIKE ? OR cp.bio LIKE ? OR cp.role_type LIKE ?)';
    $term = '%' . $q . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql = '
    SELECT cp.*, c.name AS category_name, c.slug AS category_slug 
    FROM character_profiles cp 
    JOIN categories c ON cp.category_id = c.id 
    WHERE ' . implode(' AND ', $where) . ' 
    ORDER BY cp.name ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$characters = $stmt->fetchAll();

$crumbs = ['Home' => 'index.php', 'Character Profiles' => null];

$page_title = 'Character Profiles Hub · Fan Hub Plus';
require_once __DIR__ . '/includes/header.php';
?>

<?= render_breadcrumbs($crumbs) ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 text-white">Character Profiles Hub</h1>
        <p class="text-muted small mb-0">Discover iconic protagonists, idols, commanders, and fandom personalities across all domains.</p>
    </div>
</div>

<!-- Filter Toolbar -->
<form method="get" action="<?= e(url('characters.php')) ?>" class="filter-bar mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="search" name="q" class="form-control form-control-sm" placeholder="Search character name, role, or lore…" value="<?= e($q) ?>">
        </div>
        <div class="col-md-5">
            <select name="category" class="form-select form-select-sm" style="background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-accent w-100">Filter</button>
            <?php if ($q !== '' || $categorySlug !== ''): ?>
                <a href="<?= e(url('characters.php')) ?>" class="btn btn-sm btn-ghost" title="Reset">✕</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Character Cards Grid (Responsive: 1-col mobile, 2-col tablet, 4-col desktop) -->
<?php if (empty($characters)): ?>
    <div class="text-center py-5">
        <div class="fs-1 mb-2">👤</div>
        <h2 class="h5 text-white">No character profiles match your criteria</h2>
        <p class="text-muted">Try choosing another category or keyword.</p>
        <a href="<?= e(url('characters.php')) ?>" class="btn btn-sm btn-accent">Show All Characters</a>
    </div>
<?php else: ?>
    <div class="responsive-grid-4 mb-5">
        <?php foreach ($characters as $char): ?>
            <article class="media-card h-100 d-flex flex-column">
                <div class="card-art-cover grad-<?= e(preg_replace('/[^a-z0-9_-]/', '', strtolower($char['category_slug']))) ?>">
                    <div class="card-art-pattern"></div>
                    <div class="card-art-badge"><?= e($char['role_type'] ?: 'Profile') ?></div>
                    <div class="card-art-center">
                        <span class="card-art-icon"><?= category_icon($char['category_slug']) ?></span>
                        <span class="card-art-cat"><?= e($char['category_name']) ?></span>
                    </div>
                </div>
                <div class="p-3 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge-fandom">
                            <?= e($char['category_name']) ?>
                        </span>
                        <span class="small text-muted"><?= e($char['role_type'] ?: 'Fandom Icon') ?></span>
                    </div>
                    <h2 class="h5 text-white mb-2"><?= e($char['name']) ?></h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        <?= e(mb_strimwidth($char['bio'] ?? '', 0, 110, '...')) ?>
                    </p>
                    <button type="button" class="btn btn-sm btn-ghost w-100 mt-auto" data-bs-toggle="modal" data-bs-target="#charModal<?= $char['id'] ?>">
                        View Lore & Bio
                    </button>
                </div>
            </article>

            <!-- Modal for Character Lore -->
            <div class="modal fade" id="charModal<?= $char['id'] ?>" tabindex="-1" aria-labelledby="charModalLabel<?= $char['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-modal">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <span class="badge-fandom mb-1">
                                    <?= e($char['category_name']) ?> · <?= e($char['role_type'] ?: 'Character') ?>
                                </span>
                                <h3 class="modal-title h5 text-white" id="charModalLabel<?= $char['id'] ?>"><?= e($char['name']) ?></h3>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h4 class="h6 text-muted mb-2">Character Biography & Lore:</h4>
                            <p class="text-light" style="line-height: 1.7;"><?= nl2br(e($char['bio'])) ?></p>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <a href="<?= e(url('explore.php?category=' . urlencode($char['category_slug']))) ?>" class="btn btn-sm btn-accent">
                                Explore <?= e($char['category_name']) ?> Content
                            </a>
                            <button type="button" class="btn btn-sm btn-ghost" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>