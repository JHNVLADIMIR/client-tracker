<?php
require_once __DIR__ . '/core/auth.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
$messages = ['error_invalid' => 'Check the fields and enter valid information. Passwords must be at least 8 characters.',
    'error_duplicate' => 'That username or email is already in use.', 'error_csrf' => 'The form expired. Please try again.'];
$notice = $messages[requestCode(array_keys($messages))] ?? '';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register | Client Tracker</title><link rel="stylesheet" href="styles.css"></head>
<body class="auth-page"><main class="auth-card auth-card-wide">
    <a class="brand auth-brand" href="login.php"><span class="brand-mark">CT</span><span>Client Tracker <small>Client Management</small></span></a>
    <p class="eyebrow">REGISTER</p><h1>Create an account</h1><p class="subtitle">Enter your details below to get started.</p>
    <?php if ($notice !== ''): ?><div class="notice notice-error"><?= e($notice) ?></div><?php endif; ?>
    <form action="core/handleForms.php" method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="register">
        <label>Username<input name="username" minlength="3" maxlength="50" required autocomplete="username"></label>
        <label>Email<input type="email" name="email" maxlength="100" required autocomplete="email"></label>
        <label>First name<input name="first_name" maxlength="100" required autocomplete="given-name"></label>
        <label>Last name<input name="last_name" maxlength="100" required autocomplete="family-name"></label>
        <label>Password<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
        <label>Age<input type="number" name="age" min="1" max="120" required></label>
        <label class="full-span">Address<input name="address" maxlength="255" autocomplete="street-address"></label>
        <label>Phone<input name="phone" maxlength="30" autocomplete="tel"></label>
        <div class="full-span"><button class="button" type="submit">Register</button></div>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Log in</a></p>
</main></body></html>
