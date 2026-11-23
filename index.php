<?php
require_once __DIR__ . '/core/auth.php';
requireLogin();
require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/models.php';
$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$clients = activeClients($pdo, $search);
$messages = ['welcome' => 'You are now logged in.', 'client_added' => 'Client added successfully.',
    'client_updated' => 'Client updated successfully.', 'client_deleted' => 'Client and active automations moved to deleted records.',
    'error_invalid' => 'Please check the information you entered.', 'error_duplicate' => 'That value is already in use.',
    'error_missing' => 'The requested record could not be found.', 'error_general' => 'The request could not be completed. Please try again.',
    'error_csrf' => 'The form expired. Please try again.'];
$notice = $messages[requestCode(array_keys($messages))] ?? '';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Clients | Client Tracker</title><link rel="stylesheet" href="styles.css"></head><body>
<?php require __DIR__ . '/navbar.php'; ?>
<main class="container wide">
    <section class="page-heading"><p class="eyebrow">CLIENTS</p><h1>Client list</h1><p class="subtitle">Add and manage your clients.</p></section>
    <?php if ($notice !== ''): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
    <section class="card"><div class="section-heading"><div><h2>Add a Client</h2></div></div>
        <form action="core/handleForms.php" method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="client_create">
            <label>Business name<input name="business_name" maxlength="120" required placeholder="Sunrise Realty"></label>
            <label>Industry<input name="industry" maxlength="100" required placeholder="Real Estate"></label>
            <label>Contact person<input name="contact_person" maxlength="120" required></label>
            <label>Email<input type="email" name="email" maxlength="100" required></label>
            <label>Contact number<input name="contact_no" maxlength="30"></label>
            <div class="form-action"><button class="button" type="submit">Add Client</button></div>
        </form>
    </section>
    <section class="card"><div class="section-heading"><div><h2>Client list</h2></div><span class="count-pill"><?= count($clients) ?> <?= $search !== '' ? 'matches' : 'clients' ?></span></div>
        <form action="index.php" method="get" class="form-grid compact search-form">
            <label for="client-search">Search clients<input id="client-search" type="search" name="search" maxlength="150" value="<?= e($search) ?>" placeholder="Business, industry, contact, or email"></label>
            <div class="form-action"><button class="button" type="submit">Search</button></div>
            <?php if ($search !== ''): ?><div class="form-action"><a class="button button-outline" href="index.php">Clear search</a></div><?php endif; ?>
        </form>
        <?php if (!$clients && $search === ''): ?><p class="empty-state">No clients yet. Add your first business client above.</p><?php elseif (!$clients): ?><p class="empty-state">No clients matched “<?= e($search) ?>”.</p><?php else: ?>
        <div class="table-wrap"><table class="audit-table"><thead><tr><th>Business</th><th>Industry</th><th>Contact</th><th>Email</th><th>Automations</th><th>Added By</th><th>Date Added</th><th>Last Updated By</th><th>Last Updated By ID</th><th>Last Updated</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($clients as $client): ?><tr>
            <td><strong><?= e($client['business_name']) ?></strong></td><td><?= e($client['industry']) ?></td><td><?= e($client['contact_person']) ?><?php if ($client['contact_no']): ?><small class="table-description"><?= e($client['contact_no']) ?></small><?php endif; ?></td><td><?= e($client['email']) ?></td><td><?= (int) $client['automation_count'] ?></td>
            <td><?= e($client['added_by_name']) ?></td><td><?= e($client['date_added']) ?></td><td><?= e($client['last_updated_by_name']) ?></td><td><?= e($client['last_updated_by_id'] ?? '—') ?></td><td><?= e($client['last_updated']) ?></td>
            <td class="actions"><a href="automations.php?client_id=<?= (int) $client['id'] ?>">Automations</a><a href="editclient.php?id=<?= (int) $client['id'] ?>">Edit</a><a class="danger-link" href="deleteclient.php?id=<?= (int) $client['id'] ?>">Delete</a></td>
        </tr><?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </section>
</main></body></html>
