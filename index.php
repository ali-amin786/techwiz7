<?php
require_once __DIR__ . '/includes/functions.php';

// Fetch Categories
$categories = $pdo->query('SELECT id, name, slug, description, default_thumbnail FROM categories ORDER BY name')->fetchAll();

// Fetch Trending / Popular Posts (Approved only)
$trendingStmt = $pdo->query('
    SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.username 
    FROM posts p 
    JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.status = "approved" 
    ORDER BY p.views DESC, p.popularity DESC 
    LIMIT 6
');
$trendingPosts = $trendingStmt->fetchAll();

// Fetch Latest Upcoming Events
$eventsStmt = $pdo->query('
    SELECT e.*, c.name AS category_name, c.slug AS category_slug 
    FROM events e 
    JOIN categories c ON e.category_id = c.id 
    WHERE e.event_date >= CURDATE() 
    ORDER BY e.event_date ASC 
    LIMIT 3
');
$upcomingEvents = $eventsStmt->fetchAll();
if (empty($upcomingEvents)) {
    // Fallback if dates passed in demo
    $upcomingEvents = $pdo->query('
        SELECT e.*, c.name AS category_name, c.slug AS category_slug 
        FROM events e 
        JOIN categories c ON e.category_id = c.id 
        ORDER BY e.event_date DESC 
        LIMIT 3
    ')->fetchAll();
}

// Fetch Upcoming Releases preview
$upcomingReleases = $pdo->query('
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM posts p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.status = "approved" AND p.is_upcoming = 1 
    ORDER BY p.release_date ASC 
    LIMIT 3
')->fetchAll();

$page_title = 'Home · Fandom Universe Hub';
require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section Updates (Anime Hero Image + Gradient Overlay for Readability) -->
<section class="hero-banner position-relative overflow-hidden mb-4">
    <div class="row align-items-center">
        <div class="col-12">
            <div class="hero-content">
                <span class="hero-pill">🌟 Fandom Universe Portal · Fan Hub Plus</span>
                <h1 class="hero-title">All Your Fandoms. One Immersive Dark Hub.</h1>
                <p class="hero-lead">
                    Explore curated universes across <strong>Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga, and Cosplay</strong>. 
                    Watch previews, discover character profiles, find local conventions, and join our creative fan community.
                </p>
                <div class="d-flex gap-3 flex-wrap align-items-center">
                    <a class="btn btn-accent px-4 py-2" href="<?= e(url('explore.php')) ?>">
                        Explore Content 🚀
                    </a>
                    <a class="btn btn-ghost px-3 py-2" href="<?= e(url('events-map.php')) ?>">
                        📍 Discover Events
                    </a>
                    <?php if (!is_logged_in()): ?>
                        <a class="btn btn-ghost px-3 py-2" href="<?= e(url('register.php')) ?>">
                            Join Fan Hub
                        </a>
                    <?php else: ?>
                        <a class="btn btn-ghost px-3 py-2" href="<?= e(url('submit-post.php')) ?>">
                            + Submit Fan Content
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Fandom Domains Responsive Grid (1-col mobile, 2-col tablet, 4-col desktop) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0 text-white">🌌 Explore by Fandom Category</h2>
        <small class="text-muted">Dive into your favorite creative dimension</small>
    </div>
    <a href="<?= e(url('explore.php')) ?>" class="small text-decoration-none">View All →</a>
</div>

<div class="responsive-grid-4 mb-5">
    <?php foreach ($categories as $cat): ?>
        <article class="media-card h-100">
            <?= render_card_visual($cat['name'], $cat['slug'], 'category', $cat['default_thumbnail']) ?>
            <div class="p-3 d-flex flex-column flex-grow-1">
                <h3 class="h6 mb-1 text-white text-truncate"><?= e($cat['name']) ?></h3>
                <p class="small text-muted mb-3 flex-grow-1 text-truncate" title="<?= e($cat['description']) ?>"><?= e($cat['description']) ?></p>
                <a class="btn btn-sm btn-ghost w-100 mt-auto" href="<?= e(url('explore.php?category=' . urlencode($cat['slug']))) ?>">Explore</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<!-- 6. Trending & Popular Content Responsive Grid (1-col mobile, 2-col tablet, 3-col desktop) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0 text-white">🔥 Trending & Most Popular Content</h2>
        <small class="text-muted">Top articles, videos, and media loved by fandom fans</small>
    </div>
    <a href="<?= e(url('explore.php?sort=popular')) ?>" class="small text-decoration-none">See Ranking →</a>
</div>

<div class="responsive-grid-3 mb-5">
    <?php if (empty($trendingPosts)): ?>
        <div class="col-12 text-muted">No content published yet.</div>
    <?php else: ?>
        <?php foreach ($trendingPosts as $post): ?>
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
                        <span class="badge-fandom">
                            <?= e($post['category_name']) ?>
                        </span>
                        <span class="small text-muted">
                            👁️ <?= number_format((int)$post['views']) ?> views
                        </span>
                    </div>
                    <h3 class="h6 mb-2">
                        <a href="<?= e(url('post.php?id=' . $post['id'])) ?>" class="text-white text-decoration-none">
                            <?= e($post['title']) ?>
                        </a>
                    </h3>
                    <p class="small text-muted mb-3 flex-grow-1">
                        <?= e(mb_strimwidth($post['description'] ?? '', 0, 100, '...')) ?>
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
    <?php endif; ?>
</div>

<!-- Upcoming Events & Releases Row -->
<div class="row g-4 mb-5">
    <!-- Events Spotlight -->
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="h5 mb-0 text-white">📍 Upcoming Conventions & Fandom Meets</h2>
                <small class="text-muted">Conventions, cosplay meets, and screening schedules</small>
            </div>
            <a href="<?= e(url('events-map.php')) ?>" class="small text-decoration-none">View Map →</a>
        </div>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($upcomingEvents as $ev): ?>
                <div class="event-card p-3">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="event-date-chip flex-shrink-0">
                            <span class="d-block fw-bold fs-5"><?= date('d', strtotime($ev['event_date'])) ?></span>
                            <span class="small text-uppercase"><?= date('M', strtotime($ev['event_date'])) ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <h3 class="h6 mb-1 text-white"><?= e($ev['title']) ?></h3>
                                <span class="badge bg-secondary"><?= e($ev['city']) ?></span>
                            </div>
                            <div class="event-meta-line mb-1">
                                <span>🏛️ <?= e($ev['venue_location'] ?: $ev['city']) ?></span>
                            </div>
                            <div class="event-meta-line mb-2 small">
                                <span>📅 <?= e($ev['event_day'] ?: date('l', strtotime($ev['event_date']))) ?></span>
                                <span class="ms-2">⏰ <?= e($ev['event_time'] ?: 'TBA') ?></span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= e(url('events-map.php?event_id=' . $ev['id'])) ?>" class="btn btn-sm btn-ghost">View on Map</a>
                                <?php if (!empty($ev['ticket_link'])): ?>
                                    <a href="<?= e($ev['ticket_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-accent">Tickets</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Upcoming Releases -->
    <div class="col-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="h5 mb-0 text-white">⏳ Anticipated Drops</h2>
                <small class="text-muted">Upcoming releases & debuts</small>
            </div>
            <a href="<?= e(url('upcoming.php')) ?>" class="small text-decoration-none">All Drops →</a>
        </div>
        <div class="d-flex flex-column gap-3">
            <?php if (empty($upcomingReleases)): ?>
                <div class="p-3 text-center text-muted border rounded" style="border-color: var(--border-color) !important;">
                    Upcoming schedules arriving soon.
                </div>
            <?php else: ?>
                <?php foreach ($upcomingReleases as $rel): ?>
                    <div class="p-3 rounded d-flex gap-3 align-items-center" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                        <div class="fs-2"><?= category_icon($rel['category_slug']) ?></div>
                        <div class="flex-grow-1">
                            <h3 class="h6 mb-1 text-white"><?= e($rel['title']) ?></h3>
                            <span class="small text-muted d-block"><?= e($rel['category_name']) ?> · Release: <?= e($rel['release_date'] ?: ($rel['release_year'] ?? 'TBA')) ?></span>
                        </div>
                        <a href="<?= e(url('post.php?id=' . $rel['id'])) ?>" class="btn btn-sm btn-ghost">Info</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Quick AI Assistant Banner -->
            <div class="ai-banner mt-2">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fs-5">🤖</span>
                    <strong class="text-white small">Need recommendations?</strong>
                </div>
                <p class="small text-muted mb-2">Our AI Assistant at the bottom right can guide you to content, explain features, and answer FAQs.</p>
                <button type="button" class="btn btn-sm btn-accent" onclick="document.getElementById('chatbotToggle')?.click();">Open Chatbot</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
