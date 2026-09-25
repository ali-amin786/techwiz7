<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    require_once __DIR__ . '/functions.php';
}
$page_title = $page_title ?? 'Fan Hub Plus';
$saved_count = is_logged_in() ? bookmark_count($pdo, current_user_id()) : 0;
?>
<!DOCTYPE html>
<html lang="en" class="<?= e(theme_class()) ?> font-<?= e(font_scale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> · Fan Hub Plus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="app-body" data-auth="<?= is_logged_in() ? '1' : '0' ?>">
<div class="app-shell">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<div class="main-wrap">
    <header class="topbar">
        <button class="btn btn-icon d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Open menu">
            <span class="hamburger"></span>
        </button>
        <form class="top-search" action="<?= e(url('explore.php')) ?>" method="get" role="search">
            <input type="search" name="q" class="form-control" placeholder="Search titles, genres, characters…" value="<?= e($_GET['q'] ?? '') ?>">
        </form>
        <div class="topbar-actions">
            <button type="button" class="btn btn-ghost" id="themeToggle" data-theme="<?= e(theme_class() === 'theme-light' ? 'light' : 'dark') ?>">
                <?= theme_class() === 'theme-light' ? 'Dark' : 'Light' ?>
            </button>
            <button type="button" class="btn btn-ghost" id="fontToggle" data-font="<?= e(font_scale()) ?>">Aa</button>
            <?php if (is_logged_in()): ?>
                <a class="btn btn-accent" href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
            <?php else: ?>
                <a class="btn btn-accent" href="<?= e(url('login.php')) ?>">Login</a>
            <?php endif; ?>
        </div>
    </header>
    <main class="content-area">
        <?php $flash = flash_get(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> glass-alert">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
