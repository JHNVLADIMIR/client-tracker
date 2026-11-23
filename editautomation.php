<?php
require_once __DIR__ . '/core/auth.php'; requireLogin();
require_once __DIR__ . '/core/dbConfig.php'; require_once __DIR__ . '/core/models.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$automation = $id ? findAutomation($pdo, $id) : null;
if (!$automation) { header('Location: index.php?error=error_missing'); exit; }
$types = ['Lead Capture', 'Speed-to-Lead', 'CRM Sync', 'Chatbot'];
$tools = ['Zapier', 'Make', 'n8n', 'GoHighLevel'];
$statuses = ['Planning', 'Building', 'Live', 'Paused'];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Project | Client Tracker</title><link rel="stylesheet" href="styles.css"></head><body>
<?php require __DIR__ . '/navbar.php'; ?><main class="container narrow">
<section class="page-heading"><p class="eyebrow">AUTOMATION DETAILS</p><h1>Edit automation</h1><p class="subtitle">Update <?= e($automation['automation_name']) ?>.</p></section>
<section class="card"><form action="core/handleForms.php" method="post" class="stack-form">
<input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="automation_update">
<input type="hidden" name="automation_id" value="<?= (int) $automation['id'] ?>"><input type="hidden" name="client_id" value="<?= (int) $automation['client_id'] ?>">
<label>Automation name<input name="automation_name" maxlength="150" required value="<?= e($automation['automation_name']) ?>"></label>
<label>Type<select name="type"><?php foreach ($types as $type): ?><option <?= $automation['type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
<label>Tool used<select name="tool_used"><?php foreach ($tools as $tool): ?><option <?= $automation['tool_used'] === $tool ? 'selected' : '' ?>><?= e($tool) ?></option><?php endforeach; ?></select></label>
<label>Monthly fee<input type="number" name="monthly_fee" min="0" max="99999999.99" step="0.01" required value="<?= e($automation['monthly_fee']) ?>"></label>
<label>Status<select name="status"><?php foreach ($statuses as $status): ?><option <?= $automation['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
<div class="button-row"><button class="button" type="submit">Save Changes</button><a class="button button-outline" href="automations.php?client_id=<?= (int) $automation['client_id'] ?>">Cancel</a></div>
</form></section></main></body></html>
