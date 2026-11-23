<?php
require_once __DIR__ . '/core/auth.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
$messages = ['registered' => 'Your account is ready. Please log in.', 'error_credentials' => 'The username/email or password is incorrect.',
    'error_duplicate' => 'That account is already registered.', 'error_csrf' => 'The form expired. Please try again.'];
$notice = $messages[requestCode(array_keys($messages))] ?? '';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Log in | Client Tracker</title><link rel="stylesheet" href="styles.css"></head>
<body class="auth-page"><main class="auth-card">
    <a class="brand auth-brand" href="login.php"><span class="brand-mark">CT</span><span>Client Tracker <small>Client Management</small></span></a>
    <p class="eyebrow">CLIENT TRACKER</p><h1>Log in</h1><p class="subtitle">Sign in to continue.</p>
    <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
    <form action="core/handleForms.php" method="post" class="stack-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="login">
        <label>Username or email<input name="login" required autocomplete="username"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <button class="button" type="submit">Log in</button>
    </form>
    <p class="auth-switch">No account yet? <a href="register.php">Register</a></p>
</main></body></html>
