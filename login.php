<?php
require_once __DIR__ . '/config.php';
if (current_user()) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === '' || $password === '') $error = 'Username dan password wajib diisi.';
    else {
        $stmt = db()->prepare('SELECT id, name, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            redirect('index.php');
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login - Inventori</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="login-page"><form class="login-card" method="post"><h1>Inventori Management</h1><p class="muted">Masuk ke sistem management inventori</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Username</label><input name="username" autocomplete="username" required autofocus><label>Password</label><input type="password" name="password" autocomplete="current-password" required><button class="btn primary full">Login</button><p class="muted small">
