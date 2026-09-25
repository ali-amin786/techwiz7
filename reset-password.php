<?php
require_once __DIR__ . '/includes/functions.php';

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$done = false;
$resetRow = null;

if ($token === '') {
    $error = 'Reset token is missing.';
} else {
    $stmt = $pdo->prepare('SELECT id, email, expires_at FROM password_resets WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $resetRow = $stmt->fetch();
    if (!$resetRow || strtotime($resetRow['expires_at']) < time()) {
        $error = 'This reset link is invalid or has expired.';
        $resetRow = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetRow) {
    csrf_require();
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Password confirmation does not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([$hash, $resetRow['email']]);
        $pdo->prepare('DELETE FROM password_resets WHERE id = ?')->execute([$resetRow['id']]);
        $done = true;
        $resetRow = null;
        flash_set('success', 'Password updated. You can log in now.');
        redirect('login.php');
    }
}

$page_title = 'Reset password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card">
    <h1 class="h3 mb-3">Reset password</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($resetRow && !$done): ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label class="form-label" for="password">New password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirm">Confirm new password</label>
                <input class="form-control" type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button class="btn btn-accent w-100" type="submit">Save password</button>
        </form>
    <?php endif; ?>
    <p class="mt-3 mb-0"><a href="<?= e(url('login.php')) ?>">Back to login</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
