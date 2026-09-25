<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$values = [
    'name' => '',
    'username' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $values['name'] = trim((string) ($_POST['name'] ?? ''));
    $values['username'] = trim((string) ($_POST['username'] ?? ''));
    $values['email'] = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if ($values['name'] === '' || mb_strlen($values['name']) > 100) {
        $errors[] = 'Enter a name up to 100 characters.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $values['username'])) {
        $errors[] = 'Username must be 3–50 letters, numbers, or underscores.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1');
        $check->execute([$values['username'], $values['email']]);
        $existing = $check->fetch();
        if ($existing) {
            if (strcasecmp($existing['username'], $values['username']) === 0) {
                $errors[] = 'That username is already taken.';
            }
            if (strcasecmp($existing['email'], $values['email']) === 0) {
                $errors[] = 'That email is already registered.';
            }
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare(
            'INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)'
        );
        $insert->execute([$values['name'], $values['username'], $values['email'], $hash, 'user']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $pdo->lastInsertId();
        $_SESSION['username'] = $values['username'];
        $_SESSION['role'] = 'user';
        flash_set('success', 'Account created. Welcome to Fan Hub Plus.');
        redirect('index.php');
    }
}

$page_title = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-card">
    <h1 class="h3 mb-3">Create an account</h1>
    <p class="text-muted">Registered users can bookmark, rate, and submit posts for approval.</p>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" id="registerForm" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input class="form-control" id="name" name="name" value="<?= e($values['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= e($values['username']) ?>" required>
            <div class="form-text" id="usernameStatus"></div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($values['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirm">Confirm password</label>
            <input class="form-control" type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <button class="btn btn-accent w-100" type="submit">Register</button>
    </form>
    <p class="mt-3 mb-0"><a href="<?= e(url('login.php')) ?>">Already have an account?</a></p>
</div>
<script>
(() => {
  const input = document.getElementById('username');
  const status = document.getElementById('usernameStatus');
  if (!input) return;
  let timer;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
      const username = input.value.trim();
      if (username.length < 3) {
        status.textContent = '';
        return;
      }
      const res = await fetch('check-username.php?username=' + encodeURIComponent(username));
      const data = await res.json();
      status.textContent = data.available ? 'Username is available.' : 'Username is already taken.';
    }, 300);
  });
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
