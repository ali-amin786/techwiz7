<?php
require_once __DIR__ . '/functions.php';
$page_title = $placeholder_title ?? 'Coming next';
require_once __DIR__ . '/header.php';
?>
<div class="auth-card">
    <h1 class="h4"><?= e($page_title) ?></h1>
    <p class="mb-0 text-muted">This page is part of the next build step (Section B or C). Foundation, schema, design system, and auth are already live.</p>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
