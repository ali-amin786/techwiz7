<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

$notice = '';
$debug = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $email = trim((string) ($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notice = 'Enter the email address on your account.';
    } else {
        $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $userStmt->execute([$email]);
        $exists = (bool) $userStmt->fetch();
        $notice = 'If that email exists, a reset link has been prepared.';

        if ($exists) {
            $token = bin2hex(random_bytes(32));
            $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
            $insert = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $insert->execute([$email, $token]);
            $resetUrl = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http')
                . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                . url('reset-password.php?token=' . urlencode($token));
            $debug = send_password_reset_email($email, $resetUrl);
        }
    }
}

$page_title = 'Forgot password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card">
    <h1 class="h3 mb-3">Forgot password</h1>
    <p class="text-muted">Enter your account email. On this XAMPP build the reset link is shown in a debug banner instead of SMTP.</p>
    <?php if ($notice): ?>
        <div class="alert alert-info"><?= e($notice) ?></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required>
        </div>
        <button class="btn btn-accent w-100" type="submit">Generate reset link</button>
    </form>
    <?php if ($debug): ?>
        <div class="debug-banner">
            <strong>Local Test Debug Banner</strong>
            <p class="mb-2"><?= e($debug['message']) ?></p>
            <p class="mb-1">Account: <?= e($debug['email']) ?></p>
            <a href="<?= e($debug['resetUrl']) ?>"><?= e($debug['resetUrl']) ?></a>
        </div>
    <?php endif; ?>
    <p class="mt-3 mb-0"><a href="<?= e(url('login.php')) ?>">Back to login</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
