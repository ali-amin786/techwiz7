<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/csrf.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path === '' ? '' : '/' . $path);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_role(): string
{
    return $_SESSION['role'] ?? 'guest';
}

function is_admin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function auth_check(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . url('login.php'));
        exit();
    }
}

function admin_check(): void
{
    auth_check();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . url('index.php'));
        exit();
    }
}

function theme_class(): string
{
    return (isset($_COOKIE['fh_theme']) && $_COOKIE['fh_theme'] === 'light') ? 'theme-light' : 'theme-dark';
}

function font_scale(): string
{
    $scale = $_COOKIE['fh_font'] ?? 'md';
    return in_array($scale, ['sm', 'md', 'lg'], true) ? $scale : 'md';
}

function avatar_url(?string $profilePic, string $username): string
{
    $file = trim((string) $profilePic);
    $path = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $file;
    if ($file === '' || $file === 'default-avatar.png' || !is_file($path)) {
        return 'https://ui-avatars.com/api/?name=' . rawurlencode($username) . '&background=8b5cf6&color=fff';
    }
    return url('uploads/avatars/' . rawurlencode($file));
}

function thumbnail_url(?string $thumbnail): string
{
    $file = trim((string) $thumbnail);
    if ($file === '') {
        $file = 'default-category.jpg';
    }
    $real = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . $file;
    if (is_file($real)) {
        return url('uploads/thumbnails/' . rawurlencode($file));
    }
    return url('uploads/thumbnails/default-category.jpg');
}

function media_file_url(?string $mediaUrl): string
{
    $file = trim((string) $mediaUrl);
    if ($file === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $file)) {
        return $file;
    }
    return url(ltrim($file, '/'));
}

function bookmark_count(PDO $pdo, ?int $userId): int
{
    if (!$userId) {
        return 0;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookmarks WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function active_nav(string $file): string
{
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return $current === $file ? ' active' : '';
}

function category_default_thumbnail(PDO $pdo, int $categoryId): string
{
    $stmt = $pdo->prepare('SELECT default_thumbnail FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $row = $stmt->fetch();
    return $row['default_thumbnail'] ?? 'default-category.jpg';
}
