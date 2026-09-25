<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Login required to rate media']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$postId = (int) ($_POST['post_id'] ?? 0);
$stars = (int) ($_POST['rating_stars'] ?? 0);
$comment = trim((string) ($_POST['review_comment'] ?? ''));

if ($postId <= 0 || $stars < 1 || $stars > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid rating. Please select 1 to 5 stars.']);
    exit();
}

$userId = current_user_id();

// Check if post exists
$stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ? LIMIT 1');
$stmt->execute([$postId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit();
}

// Upsert rating
$check = $pdo->prepare('SELECT id FROM media_ratings WHERE user_id = ? AND post_id = ? LIMIT 1');
$check->execute([$userId, $postId]);
$existing = $check->fetch();

if ($existing) {
    $update = $pdo->prepare('UPDATE media_ratings SET rating_stars = ?, review_comment = ?, created_at = CURRENT_TIMESTAMP WHERE id = ?');
    $update->execute([$stars, $comment ?: null, $existing['id']]);
} else {
    $insert = $pdo->prepare('INSERT INTO media_ratings (user_id, post_id, rating_stars, review_comment) VALUES (?, ?, ?, ?)');
    $insert->execute([$userId, $postId, $stars, $comment ?: null]);
}

$summary = get_post_rating_summary($pdo, $postId);

echo json_encode([
    'status' => 'success',
    'user_rating' => $stars,
    'avg_rating' => $summary['avg'],
    'total_ratings' => $summary['count'],
]);
