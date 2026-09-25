<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('explore.php');
}

// Fetch post
$stmt = $pdo->prepare('
    SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.username, u.name AS author_name 
    FROM posts p 
    JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.id = ? AND (p.status = "approved" OR p.user_id = ? OR ?)
    LIMIT 1
');
$isAdmin = is_admin() ? 1 : 0;
$stmt->execute([$id, current_user_id() ?? -1, $isAdmin]);
$post = $stmt->fetch();

if (!$post) {
    flash_set('error', 'Content item not found or pending moderation.');
    redirect('explore.php');
}

// Increment views
if ($post['status'] === 'approved') {
    increment_post_views($pdo, $id);
}

// Rating info
$rating = get_post_rating_summary($pdo, $id);
$userRating = is_logged_in() ? get_user_rating($pdo, $id, current_user_id()) : null;
$isSaved = is_logged_in() ? is_bookmarked($pdo, $id, current_user_id()) : false;

// Related posts in same category
$relatedStmt = $pdo->prepare('
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM posts p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.status = "approved" 
    ORDER BY p.views DESC 
    LIMIT 4
');
$relatedStmt->execute([$post['category_id'], $id]);
$relatedPosts = $relatedStmt->fetchAll();

// Breadcrumbs
$crumbs = [
    'Home' => 'index.php',
    'Explore' => 'explore.php',
    $post['category_name'] => 'explore.php?category=' . urlencode($post['category_slug']),
    $post['title'] => null,
];

$page_title = e($post['title']) . ' · ' . e($post['category_name']);
require_once __DIR__ . '/includes/header.php';
?>

<?= render_breadcrumbs($crumbs) ?>

<?php if ($post['status'] !== 'approved'): ?>
    <div class="alert alert-warning mb-4">
        <strong>Moderation Notice:</strong> This post is currently <strong><?= strtoupper(e($post['status'])) ?></strong> and is not yet publicly visible on the live portal until an admin approves it.
    </div>
<?php endif; ?>

<article class="post-detail-wrap mb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Media Container -->
            <?php if (!empty($post['media_url'])): ?>
                <div class="player-box">
                    <?php if ($post['type'] === 'video'): ?>
                        <?php if (strpos($post['media_url'], 'youtube.com') !== false || strpos($post['media_url'], 'youtu.be') !== false): ?>
                            <?php 
                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $post['media_url'], $matches);
                                $ytId = $matches[1] ?? '';
                            ?>
                            <iframe src="https://www.youtube.com/embed/<?= e($ytId) ?>" allowfullscreen></iframe>
                        <?php else: ?>
                            <video controls poster="<?= e(thumbnail_url($post['thumbnail'])) ?>">
                                <source src="<?= e(media_file_url($post['media_url'])) ?>" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        <?php endif; ?>
                    <?php elseif ($post['type'] === 'audio'): ?>
                        <div class="p-4 text-center">
                            <span class="fs-1 mb-2 d-block">🎵</span>
                            <audio controls class="w-100">
                                <source src="<?= e(media_file_url($post['media_url'])) ?>" type="audio/mpeg">
                                Your browser does not support HTML5 audio.
                            </audio>
                        </div>
                    <?php else: ?>
                        <img src="<?= e(media_file_url($post['media_url'])) ?>" alt="<?= e($post['title']) ?>" class="img-fluid rounded w-100" style="max-height: 480px; object-fit: cover;">
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Stylish CSS Card Visual Header -->
                <div class="rounded-3 overflow-hidden mb-4" style="border: 1px solid var(--border-color);">
                    <?= render_card_visual($post['category_name'], $post['category_slug'], $post['type'], $post['thumbnail']) ?>
                </div>
            <?php endif; ?>

            <!-- Post Title & Meta -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <span class="badge-fandom mb-2">
                        <?= e($post['category_name']) ?> · <?= strtoupper(e($post['type'])) ?>
                    </span>
                    <h1 class="h2 text-white mb-1"><?= e($post['title']) ?></h1>
                    <div class="text-muted small">
                        <span>Submitted by <?= e($post['author_name'] ?: ($post['username'] ?: 'Official Editorial')) ?></span>
                        <span class="mx-2">•</span>
                        <span><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                        <?php if (!empty($post['genre'])): ?>
                            <span class="mx-2">•</span>
                            <span class="text-info"><?= e($post['genre']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if (is_logged_in()): ?>
                        <button type="button" 
                                class="btn <?= $isSaved ? 'btn-accent is-saved' : 'btn-ghost' ?> js-bookmark px-3" 
                                data-post-id="<?= (int)$post['id'] ?>">
                            <?= $isSaved ? '★ Saved' : '☆ Save' ?>
                        </button>
                    <?php else: ?>
                        <button type="button" 
                                class="btn btn-ghost js-guest-guard px-3" 
                                data-guard="save">
                            ☆ Save
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Content Description / Article Body -->
            <div class="p-4 rounded-3 mb-4" style="background: var(--bg-card); border: 1px solid var(--border-color); line-height: 1.8;">
                <h2 class="h5 text-white mb-3">Overview & Storyline</h2>
                <div class="post-content text-light">
                    <?= nl2br(e($post['description'])) ?>
                </div>
            </div>

            <!-- Rating & Feedback Section -->
            <div class="p-4 rounded-3" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="h6 text-white mb-1">Fan Rating & Feedback</h2>
                        <span class="text-muted small" id="avgRating_<?= $id ?>">
                            <?= $rating['avg'] > 0 ? "★ {$rating['avg']} / 5 ({$rating['count']} votes)" : 'No ratings yet. Be the first!' ?>
                        </span>
                    </div>

                    <!-- 5 Star Interactive Rating -->
                    <div class="rating-container" data-post-id="<?= $id ?>">
                        <div class="star-rating">
                            <?php for ($s = 5; $s >= 1; $s--): ?>
                                <input type="radio" id="star<?= $s ?>_<?= $id ?>" name="rating_<?= $id ?>" value="<?= $s ?>" <?= $userRating === $s ? 'checked' : '' ?>>
                                <label for="star<?= $s ?>_<?= $id ?>" title="<?= $s ?> stars">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div id="userRatingFeedback_<?= $id ?>" class="small text-accent fw-bold <?= $userRating ? '' : 'd-none' ?>">
                    <?= $userRating ? "You rated this {$userRating} ★" : '' ?>
                </div>
                <?php if (!is_logged_in()): ?>
                    <small class="text-muted d-block mt-2">
                        <a href="<?= e(url('login.php')) ?>" class="text-decoration-none">Sign in</a> to rate this media and save it to your bookmarks.
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar / Meta Info -->
        <div class="col-lg-4">
            <div class="p-4 rounded-3 mb-4" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <h2 class="h6 text-white mb-3 border-bottom pb-2" style="border-color: var(--border-color) !important;">Information</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-color) !important;">
                        <span class="text-muted">Category:</span>
                        <strong class="text-white"><?= e($post['category_name']) ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-color) !important;">
                        <span class="text-muted">Content Type:</span>
                        <span class="text-uppercase text-white"><?= e($post['type']) ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-color) !important;">
                        <span class="text-muted">Release Year:</span>
                        <span class="text-white"><?= e($post['release_year'] ?: 'N/A') ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-color) !important;">
                        <span class="text-muted">Views:</span>
                        <span class="text-white">👁️ <?= number_format((int)$post['views']) ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Popularity Score:</span>
                        <span class="text-white">🔥 <?= number_format((int)$post['popularity']) ?></span>
                    </li>
                </ul>
            </div>

            <!-- Related Posts in Category -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="p-4 rounded-3" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                    <h2 class="h6 text-white mb-3 border-bottom pb-2" style="border-color: var(--border-color) !important;">More in <?= e($post['category_name']) ?></h2>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($relatedPosts as $rel): ?>
                            <a href="<?= e(url('post.php?id=' . $rel['id'])) ?>" class="text-decoration-none text-white d-flex gap-2 align-items-center">
                                <span class="fs-4"><?= category_icon($rel['category_slug']) ?></span>
                                <div class="overflow-hidden">
                                    <div class="small fw-bold text-truncate"><?= e($rel['title']) ?></div>
                                    <div class="text-muted small">👁️ <?= number_format((int)$rel['views']) ?> views</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
