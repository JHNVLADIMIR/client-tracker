<?php
require_once __DIR__ . '/core/auth.php'; requireLogin();
require_once __DIR__ . '/core/dbConfig.php'; require_once __DIR__ . '/core/models.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$client = $id ? findClient($pdo, $id) : null;
if (!$client) { header('Location: index.php?error=error_missing'); exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Client | Client Tracker</title><link rel="stylesheet" href="styles.css"></head><body>
<?php require __DIR__ . '/navbar.php'; ?><main class="container narrow">
<section class="page-heading"><p class="eyebrow">CLIENT PROFILE</p><h1>Edit client</h1><p class="subtitle">Update this business client's information.</p></section>
<section class="card"><form action="core/handleForms.php" method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="client_update"><input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
<label>Business name<input name="business_name" maxlength="120" required value="<?= e($client['business_name']) ?>"></label>
<label>Industry<input name="industry" maxlength="100" required value="<?= e($client['industry']) ?>"></label>
<label>Contact person<input name="contact_person" maxlength="120" required value="<?= e($client['contact_person']) ?>"></label>
<label>Email<input type="email" name="email" maxlength="100" required value="<?= e($client['email']) ?>"></label>
<label>Contact number<input name="contact_no" maxlength="30" value="<?= e($client['contact_no']) ?>"></label>
<div class="full-span button-row"><button class="button" type="submit">Save Changes</button><a class="button button-outline" href="index.php">Cancel</a></div>
</form></section></main></body></html>
