<?php
require_once __DIR__ . '/core/auth.php'; requireLogin();
require_once __DIR__ . '/core/dbConfig.php'; require_once __DIR__ . '/core/models.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$client = $id ? findClient($pdo, $id) : null;
if (!$client) { header('Location: index.php?error=error_missing'); exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Delete Client | Client Tracker</title><link rel="stylesheet" href="styles.css"></head><body>
<?php require __DIR__ . '/navbar.php'; ?><main class="container narrow">
<section class="page-heading"><p class="eyebrow">CONFIRM ACTION</p><h1>Delete this client?</h1><p class="subtitle">The client and its active automations will be moved to the Recycle Bin.</p></section>
<section class="card"><h2><?= e($client['business_name']) ?></h2><p class="muted"><?= e($client['industry']) ?> · <?= e($client['email']) ?></p>
<form action="core/handleForms.php" method="post" class="button-row">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="client_delete"><input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
<button class="button button-danger" type="submit">Delete Client and Automations</button><a class="button button-outline" href="index.php">Cancel</a>
</form></section></main></body></html>
