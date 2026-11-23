<?php
require_once __DIR__ . '/core/auth.php'; requireLogin();
require_once __DIR__ . '/core/dbConfig.php'; require_once __DIR__ . '/core/models.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$automation = $id ? findAutomation($pdo, $id) : null;
if (!$automation) { header('Location: index.php?error=error_missing'); exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Delete Project | Client Tracker</title><link rel="stylesheet" href="styles.css"></head><body>
<?php require __DIR__ . '/navbar.php'; ?><main class="container narrow"><section class="page-heading"><p class="eyebrow">CONFIRM ACTION</p><h1>Delete this automation?</h1><p class="subtitle">The record will be hidden from the active list and kept in the audit history.</p></section>
<section class="card"><h2><?= e($automation['automation_name']) ?></h2><p class="muted"><?= e($automation['type']) ?> · <?= e($automation['tool_used']) ?></p><form action="core/handleForms.php" method="post" class="button-row">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="automation_delete"><input type="hidden" name="automation_id" value="<?= (int) $automation['id'] ?>">
<button class="button button-danger" type="submit">Delete Automation</button><a class="button button-outline" href="automations.php?client_id=<?= (int) $automation['client_id'] ?>">Cancel</a></form></section></main></body></html>
