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
        return 'https://ui-avatars.com/api/?name=' . rawurlencode($username) . '&background=10b981&color=0d0e12';
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

function is_bookmarked(PDO $pdo, int $postId, ?int $userId): bool
{
    if (!$userId) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ? LIMIT 1');
    $stmt->execute([$userId, $postId]);
    return (bool) $stmt->fetchColumn();
}

function increment_post_views(PDO $pdo, int $postId): void
{
    $sessionKey = 'viewed_post_' . $postId;
    if (empty($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = true;
        $stmt = $pdo->prepare('UPDATE posts SET views = views + 1, popularity = popularity + 1 WHERE id = ?');
        $stmt->execute([$postId]);
    }
}

function get_post_rating_summary(PDO $pdo, int $postId): array
{
    $stmt = $pdo->prepare('SELECT COUNT(*) as count, AVG(rating_stars) as avg_rating FROM media_ratings WHERE post_id = ?');
    $stmt->execute([$postId]);
    $row = $stmt->fetch();
    return [
        'count' => (int) ($row['count'] ?? 0),
        'avg' => $row['avg_rating'] !== null ? round((float) $row['avg_rating'], 1) : 0,
    ];
}

function get_user_rating(PDO $pdo, int $postId, ?int $userId): ?int
{
    if (!$userId) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT rating_stars FROM media_ratings WHERE user_id = ? AND post_id = ? LIMIT 1');
    $stmt->execute([$userId, $postId]);
    $stars = $stmt->fetchColumn();
    return $stars !== false ? (int) $stars : null;
}

function category_color(string $slug): string
{
    $map = [
        'anime'    => 'from-emerald to-cyan',
        'gaming'   => 'from-cyan to-blue',
        'movies'   => 'from-amber to-rose',
        'tv-shows' => 'from-sky to-indigo',
        'k-pop'    => 'from-teal to-emerald',
        'comics'   => 'from-red to-orange',
        'manga'    => 'from-emerald to-teal',
        'cosplay'  => 'from-cyan to-emerald',
    ];
    return $map[strtolower($slug)] ?? 'from-emerald to-cyan';
}

function category_icon(string $slug): string
{
    $map = [
        'anime'    => '✨',
        'gaming'   => '🎮',
        'movies'   => '🎬',
        'tv-shows' => '📺',
        'k-pop'    => '🎵',
        'comics'   => '💥',
        'manga'    => '📖',
        'cosplay'  => '🎭',
    ];
    return $map[strtolower($slug)] ?? '🌟';
}

function render_card_visual(string $categoryName, string $categorySlug, string $type = 'article', ?string $thumbnail = null): string
{
    $file = trim((string) $thumbnail);
    $real = $file !== '' ? UPLOAD_PATH . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . $file : '';
    if ($file !== '' && $file !== 'default-category.jpg' && is_file($real)) {
        return '<img class="card-img-top" src="' . e(url('uploads/thumbnails/' . rawurlencode($file))) . '" alt="' . e($categoryName) . '">';
    }

    $icon = category_icon($categorySlug);
    $gradClass = 'grad-' . preg_replace('/[^a-z0-9_-]/', '', strtolower($categorySlug));
    $typeLabel = strtoupper($type);

    return '
    <div class="card-art-cover ' . e($gradClass) . '">
        <div class="card-art-pattern"></div>
        <div class="card-art-badge">' . e($typeLabel) . '</div>
        <div class="card-art-center">
            <span class="card-art-icon">' . $icon . '</span>
            <span class="card-art-cat">' . e($categoryName) . '</span>
        </div>
    </div>';
}

function render_breadcrumbs(array $crumbs): string
{
    if (empty($crumbs)) {
        return '';
    }
    $html = '<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb glass-breadcrumb mb-0">';
    $total = count($crumbs);
    $i = 0;
    foreach ($crumbs as $label => $link) {
        $i++;
        if ($i === $total || $link === null) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . e($label) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . e(url($link)) . '">' . e($label) . '</a></li>';
        }
    }
    $html .= '</ol></nav>';
    return $html;
}

