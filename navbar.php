<?php
declare(strict_types=1);
require_once __DIR__ . '/core/auth.php';
requireLogin();
?>
<header class="topbar">
    <a class="brand" href="index.php">
        <span class="brand-mark">CT</span>
        <span>Client Tracker <small>Client Management</small></span>
    </a>
    <nav>
        <a href="index.php">Clients</a>
        <a href="deleted.php">Recycle Bin</a>
    </nav>
    <div class="account">
        <span>Logged in as <strong><?= e($_SESSION['username'] ?? 'Unknown') ?></strong></span>
        <form action="core/handleForms.php" method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to log out?');">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="logout">
            <button class="button button-outline button-small" type="submit">Logout</button>
        </form>
    </div>
</header>
