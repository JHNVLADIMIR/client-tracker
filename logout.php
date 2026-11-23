<?php
require_once __DIR__ . '/core/auth.php';
requireLogin();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Log out | Client Tracker</title><link rel="stylesheet" href="styles.css"></head><body>
<?php require __DIR__ . '/navbar.php'; ?><main class="container narrow">
<section class="page-heading"><p class="eyebrow">ACCOUNT</p><h1>Log out?</h1><p class="subtitle">You will need to sign in again to use the system.</p></section>
<section class="card"><form action="core/handleForms.php" method="post" class="button-row">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="logout">
<button class="button button-danger" type="submit">Log Out</button><a class="button button-outline" href="index.php">Cancel</a>
</form></section></main></body></html>
