<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

$action = $_POST['action'] ?? '';
if (!in_array($action, ['register', 'login'], true)) {
    requireLogin('../login.php');
}
if (!verifyCsrfToken()) {
    $page = $action === 'register' ? 'register.php' : 'index.php';
    header('Location: ../' . $page . '?error=error_csrf');
    exit;
}

require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/upload.php';

function formRedirect(string $page, string $code): never
{
    $separator = str_contains($page, '?') ? '&' : '?';
    $parameter = str_starts_with($code, 'error_') ? 'error=' : 'success=';
    header('Location: ../' . $page . $separator . $parameter . rawurlencode($code));
    exit;
}

function field(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function validClientFields(): array|false
{
    $data = [
        'business_name' => field('business_name'),
        'industry' => field('industry'),
        'contact_person' => field('contact_person'),
        'email' => strtolower(field('email')),
        'contact_no' => field('contact_no'),
        'added_by' => currentUserId(),
        'last_updated_by' => currentUserId(),
    ];
    if ($data['business_name'] === '' || strlen($data['business_name']) > 120
        || $data['industry'] === '' || strlen($data['industry']) > 100
        || $data['contact_person'] === '' || strlen($data['contact_person']) > 120
        || strlen($data['email']) > 100 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        || strlen($data['contact_no']) > 30) {
        return false;
    }
    return $data;
}

function validAutomationFields(): array|false
{
    $monthlyFee = field('monthly_fee');
    $fee = filter_var($monthlyFee, FILTER_VALIDATE_FLOAT);
    $types = ['Lead Capture', 'Speed-to-Lead', 'CRM Sync', 'Chatbot'];
    $tools = ['Zapier', 'Make', 'n8n', 'GoHighLevel'];
    $statuses = ['Planning', 'Building', 'Live', 'Paused'];
    $data = [
        'client_id' => filter_var($_POST['client_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
        'automation_name' => field('automation_name'),
        'type' => field('type'),
        'tool_used' => field('tool_used'),
        'monthly_fee' => $fee,
        'status' => field('status'),
        'added_by' => currentUserId(),
        'last_updated_by' => currentUserId(),
    ];
    if (!$data['client_id'] || $data['automation_name'] === '' || strlen($data['automation_name']) > 150
        || !in_array($data['type'], $types, true) || !in_array($data['tool_used'], $tools, true)
        || $fee === false || $fee < 0 || $fee > 99999999.99
        || !in_array($data['status'], $statuses, true)) {
        return false;
    }
    return $data;
}

try {
    switch ($action) {
        case 'register':
            $data = [
                'username' => field('username'),
                'first_name' => field('first_name'),
                'last_name' => field('last_name'),
                'email' => strtolower(field('email')),
                'password' => (string) ($_POST['password'] ?? ''),
                'address' => field('address'),
                'age' => field('age'),
                'phone' => field('phone'),
            ];
            if (strlen($data['username']) < 3 || strlen($data['username']) > 50
                || strlen($data['first_name']) < 1 || strlen($data['first_name']) > 100
                || strlen($data['last_name']) < 1 || strlen($data['last_name']) > 100
                || strlen($data['email']) > 100 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                || strlen($data['password']) < 8 || strlen($data['address']) > 255
                || !ctype_digit($data['age']) || (int) $data['age'] < 1 || (int) $data['age'] > 120
                || strlen($data['phone']) > 30) {
                formRedirect('register.php', 'error_invalid');
            }
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['age'] = (int) $data['age'];
            $data['address'] = $data['address'] ?: null;
            $data['phone'] = $data['phone'] ?: null;
            createUser($pdo, $data);
            formRedirect('login.php', 'registered');

        case 'login':
            $login = field('login');
            $password = (string) ($_POST['password'] ?? '');
            $user = $login !== '' ? findUserForLogin($pdo, $login) : null;
            if (!$user || !password_verify($password, $user['password'])) {
                formRedirect('login.php', 'error_credentials');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            formRedirect('index.php', 'welcome');

        case 'client_create':
            $data = validClientFields();
            if ($data === false) {
                formRedirect('index.php', 'error_invalid');
            }
            insertClient($pdo, $data);
            formRedirect('index.php', 'client_added');

        case 'client_update':
            $id = filter_var($_POST['client_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $data = validClientFields();
            if (!$id || $data === false) {
                formRedirect('index.php', 'error_invalid');
            }
            $data['id'] = $id;
            $data['user_id'] = currentUserId();
            formRedirect('index.php', updateClient($pdo, $data) ? 'client_updated' : 'error_missing');

        case 'automation_create':
            $data = validAutomationFields();
            if ($data === false || !findClient($pdo, (int) $data['client_id'])) {
                formRedirect('index.php', 'error_invalid');
            }
            $data['image_path'] = saveAutomationImage($_FILES['image'] ?? []);
            if ($data['image_path'] === null) {
                formRedirect('automations.php?client_id=' . (int) $data['client_id'], 'error_invalid');
            }
            insertAutomation($pdo, $data);
            formRedirect('automations.php?client_id=' . (int) $data['client_id'], 'automation_added');

        case 'automation_restore':
            $id = filter_var($_POST['automation_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id || !restoreAutomation($pdo, $id, currentUserId())) {
                formRedirect('deleted.php', 'error_restore');
            }
            formRedirect('deleted.php', 'automation_restored');

        case 'automation_update':
            $id = filter_var($_POST['automation_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $existing = $id ? findAutomation($pdo, $id) : null;
            $data = validAutomationFields();
            if (!$existing || $data === false || (int) $data['client_id'] !== (int) $existing['client_id']) {
                formRedirect('index.php', 'error_invalid');
            }
            $data['id'] = $id;
            $data['user_id'] = currentUserId();
            $updated = updateAutomation($pdo, $data);
            formRedirect('automations.php?client_id=' . (int) $existing['client_id'], $updated ? 'automation_updated' : 'error_missing');

        case 'client_delete':
            $id = filter_var($_POST['client_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id) {
                formRedirect('index.php', 'error_invalid');
            }
            softDeleteClient($pdo, $id, currentUserId());
            formRedirect('index.php', 'client_deleted');

        case 'automation_delete':
            $id = filter_var($_POST['automation_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $automation = $id ? findAutomation($pdo, $id) : null;
            if (!$automation || !softDeleteAutomation($pdo, $id, currentUserId())) {
                formRedirect('index.php', 'error_missing');
            }
            formRedirect('automations.php?client_id=' . (int) $automation['client_id'], 'automation_deleted');

        case 'logout':
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            header('Location: ../login.php');
            exit;

        default:
            formRedirect('index.php', 'error_invalid');
    }
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        formRedirect($action === 'register' ? 'register.php' : 'index.php', 'error_duplicate');
    }
    error_log($exception->getMessage());
    formRedirect('index.php', 'error_general');
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    formRedirect('index.php', 'error_general');
}
