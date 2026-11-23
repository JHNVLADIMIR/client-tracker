<?php
require_once __DIR__ . '/core/auth.php';
requireLogin();

require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/models.php';

$clientId = filter_var(
    $_GET['client_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$client = $clientId ? findClient($pdo, $clientId) : null;

if (!$client) {
    header('Location: index.php?error=error_missing');
    exit;
}

$search = isset($_GET['search']) && is_string($_GET['search'])
    ? trim($_GET['search'])
    : '';
$automations = searchAutomations($pdo, $clientId, $search);

$messages = [
    'automation_added' => 'Automation added successfully.',
    'automation_updated' => 'Automation updated.',
    'automation_deleted' => 'Automation moved to deleted records.',
    'error_invalid' => 'Please check the information you entered.',
    'error_missing' => 'The requested record could not be found.',
    'error_general' => 'The request could not be completed.',
    'error_duplicate' => 'That value is already in use.',
];
$notice = $messages[requestCode(array_keys($messages))] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Automations | Client Tracker</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
<main class="container wide">
    <p class="back-link"><a href="index.php">← All Clients</a></p>

    <section class="page-heading">
        <p class="eyebrow">CLIENT AUTOMATIONS</p>
        <h1><?= e($client['business_name']) ?></h1>
        <p class="subtitle">
            <?= e($client['industry']) ?> ·
            <?= e($client['contact_person']) ?> ·
            <?= e($client['email']) ?>
        </p>
        <a class="button button-outline" href="editclient.php?id=<?= (int) $client['id'] ?>">
            Edit Client
        </a>
    </section>

    <?php if ($notice !== ''): ?>
        <div class="notice"><?= e($notice) ?></div>
    <?php endif; ?>

    <section class="card">
        <div class="section-heading">
            <h2>Add an Automation</h2>
        </div>
        <form action="core/handleForms.php" method="post" enctype="multipart/form-data" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="automation_create">
            <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">

            <label>
                Automation name
                <input name="automation_name" maxlength="150" required placeholder="Facebook Lead to CRM">
            </label>
            <label>
                Type
                <select name="type" required>
                    <option>Lead Capture</option>
                    <option>Speed-to-Lead</option>
                    <option>CRM Sync</option>
                    <option>Chatbot</option>
                </select>
            </label>
            <label>
                Tool used
                <select name="tool_used" required>
                    <option>Zapier</option>
                    <option>Make</option>
                    <option>n8n</option>
                    <option>GoHighLevel</option>
                </select>
            </label>
            <label>
                Monthly fee
                <input type="number" name="monthly_fee" min="0" max="99999999.99" step="0.01" value="0.00" required>
            </label>
            <label>
                Status
                <select name="status" required>
                    <option>Planning</option>
                    <option>Building</option>
                    <option>Live</option>
                    <option>Paused</option>
                </select>
            </label>
            <label class="full-span">
                Issue / reference image (JPG, PNG, WebP, max 2 MB)
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" required>
            </label>
            <div class="form-action">
                <button class="button" type="submit">Add Automation</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="section-heading">
            <h2>Automations</h2>
            <span class="count-pill"><?= count($automations) ?> total</span>
        </div>

        <form action="automations.php" method="get" class="form-grid compact search-form">
            <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
            <label for="automation-search">
                Search automations
                <input
                    id="automation-search"
                    type="search"
                    name="search"
                    maxlength="150"
                    value="<?= e($search) ?>"
                    placeholder="Automation, type, tool, or status"
                >
            </label>
            <div class="form-action">
                <button class="button" type="submit">Search</button>
            </div>
            <?php if ($search !== ''): ?>
                <div class="form-action">
                    <a class="button button-outline" href="automations.php?client_id=<?= (int) $client['id'] ?>">
                        Clear search
                    </a>
                </div>
            <?php endif; ?>
        </form>

        <?php if (!$automations): ?>
            <p class="empty-state">
                <?= $search !== ''
                    ? 'No automations matched “' . e($search) . '”.'
                    : 'No automations for this client yet.' ?>
            </p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Automation</th>
                            <th>Type</th>
                            <th>Tool</th>
                            <th>Monthly Fee</th>
                            <th>Status</th>
                            <th>Added By</th>
                            <th>Date Added</th>
                            <th>Last Updated By</th>
                            <th>Last Updated By ID</th>
                            <th>Last Updated</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($automations as $automation): ?>
                            <tr>
                                <td><strong><?= e($automation['automation_name']) ?></strong></td>
                                <td><?= e($automation['type']) ?></td>
                                <td><?= e($automation['tool_used']) ?></td>
                                <td>$<?= e(number_format((float) $automation['monthly_fee'], 2)) ?></td>
                                <td><span class="status-pill"><?= e($automation['status']) ?></span></td>
                                <td><?= e($automation['added_by_name']) ?></td>
                                <td><?= e($automation['date_added']) ?></td>
                                <td><?= e($automation['last_updated_by_name']) ?></td>
                                <td><?= e($automation['last_updated_by_id'] ?? '—') ?></td>
                                <td><?= e($automation['last_updated']) ?></td>
                                <td>
                                    <?php if (!empty($automation['image_path'])): ?>
                                        <a href="<?= e($automation['image_path']) ?>" target="_blank">
                                            <img class="thumb" src="<?= e($automation['image_path']) ?>" alt="Automation image">
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="editautomation.php?id=<?= (int) $automation['id'] ?>">Edit</a>
                                    <a class="danger-link" href="deleteautomation.php?id=<?= (int) $automation['id'] ?>">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
