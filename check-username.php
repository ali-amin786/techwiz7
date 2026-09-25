<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$username = trim((string) ($_GET['username'] ?? ''));
if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
    echo json_encode(['available' => false, 'reason' => 'invalid']);
    exit();
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
echo json_encode(['available' => !$stmt->fetch()]);
