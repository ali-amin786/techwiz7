<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Login required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$postId = (int) ($_POST['post_id'] ?? 0);
if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post ID']);
    exit();
}

$userId = current_user_id();

// Verify post exists and is approved
$stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ? LIMIT 1');
$stmt->execute([$postId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit();
}

// Check existing bookmark
$check = $pdo->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ? LIMIT 1');
$check->execute([$userId, $postId]);
$existing = $check->fetch();

if ($existing) {
    // Unbookmark
    $del = $pdo->prepare('DELETE FROM bookmarks WHERE id = ?');
    $del->execute([$existing['id']]);
    $status = 'unbookmarked';
} else {
    // Bookmark
    $ins = $pdo->prepare('INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)');
    $ins->execute([$userId, $postId]);
    $status = 'saved';
}

$newCount = bookmark_count($pdo, $userId);

echo json_encode([
    'status' => $status,
    'total_bookmarks' => $newCount,
]);
