<?php
declare(strict_types=1);

function findUserForLogin(PDO $pdo, string $login): ?array
{
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username OR email = :email LIMIT 1');
    $stmt->execute(['username' => $login, 'email' => $login]);
    return $stmt->fetch() ?: null;
}

function createUser(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('INSERT INTO users (username, first_name, last_name, email, password, address, age, phone)
        VALUES (:username, :first_name, :last_name, :email, :password, :address, :age, :phone)');
    $stmt->execute($data);
}

function searchPattern(string $query): string
{
    return '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
}

function activeClients(PDO $pdo, string $search = ''): array
{
    $sql = "SELECT c.id, c.business_name, c.industry, c.contact_person, c.email, c.contact_no,
                   c.date_added, c.last_updated, c.last_updated_by AS last_updated_by_id,
                   COALESCE(a.username, 'Unknown') AS added_by_name,
                   COALESCE(u.username, 'Unknown') AS last_updated_by_name,
                   (SELECT COUNT(*) FROM automations x WHERE x.client_id = c.id AND x.deleted_at IS NULL) AS automation_count
            FROM clients c
            LEFT JOIN users a ON a.id = c.added_by
            LEFT JOIN users u ON u.id = c.last_updated_by
            WHERE c.deleted_at IS NULL";
    $parameters = [];
    if ($search !== '') {
        $sql .= " AND CONCAT_WS(' ', c.business_name, c.industry, c.contact_person, c.email, c.contact_no)
                  LIKE :search ESCAPE '!'";
        $parameters['search'] = searchPattern($search);
    }
    $sql .= ' ORDER BY c.business_name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parameters);
    return $stmt->fetchAll();
}

function findClient(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, business_name, industry, contact_person, email, contact_no
                           FROM clients WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function insertClient(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('INSERT INTO clients
        (business_name, industry, contact_person, email, contact_no, added_by, last_updated_by)
        VALUES (:business_name, :industry, :contact_person, :email, :contact_no, :added_by, :last_updated_by)');
    $stmt->execute($data);
}

function updateClient(PDO $pdo, array $data): bool
{
    $stmt = $pdo->prepare('UPDATE clients SET business_name = :business_name, industry = :industry,
        contact_person = :contact_person, email = :email, contact_no = :contact_no, last_updated_by = :user_id
        WHERE id = :id AND deleted_at IS NULL');
    $stmt->execute([
        'business_name' => $data['business_name'],
        'industry' => $data['industry'],
        'contact_person' => $data['contact_person'],
        'email' => $data['email'],
        'contact_no' => $data['contact_no'] ?: null,
        'user_id' => $data['user_id'],
        'id' => $data['id'],
    ]);
    if ($stmt->rowCount() > 0) {
        return true;
    }
    $check = $pdo->prepare('SELECT 1 FROM clients WHERE id = ? AND deleted_at IS NULL');
    $check->execute([$data['id']]);
    return (bool) $check->fetchColumn();
}

function activeAutomations(PDO $pdo, int $clientId): array
{
    return searchAutomations($pdo, $clientId);
}

function searchAutomations(PDO $pdo, int $clientId, string $search = ''): array
{
    $sql = "SELECT x.id, x.client_id, x.automation_name, x.type, x.tool_used, x.monthly_fee,
                                  x.status, x.image_path, x.date_added, x.last_updated,
                                  x.last_updated_by AS last_updated_by_id,
                                  COALESCE(a.username, 'Unknown') AS added_by_name,
                                  COALESCE(u.username, 'Unknown') AS last_updated_by_name
                           FROM automations x
                           LEFT JOIN users a ON a.id = x.added_by
                           LEFT JOIN users u ON u.id = x.last_updated_by
                           INNER JOIN clients c ON c.id = x.client_id
                           WHERE x.client_id = :client_id AND x.deleted_at IS NULL";
    $parameters = ['client_id' => $clientId];
    if ($search !== '') {
        $sql .= " AND (CONCAT_WS(' ', x.automation_name, x.type, x.tool_used, x.status)
                       LIKE :search ESCAPE '!'
                       OR c.business_name LIKE :client_search ESCAPE '!')";
        $pattern = searchPattern($search);
        $parameters['search'] = $pattern;
        $parameters['client_search'] = $pattern;
    }
    $sql .= ' ORDER BY x.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parameters);
    return $stmt->fetchAll();
}

function findAutomation(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, client_id, automation_name, type, tool_used, monthly_fee, status, image_path
                           FROM automations WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function insertAutomation(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('INSERT INTO automations
        (client_id, automation_name, type, tool_used, monthly_fee, status, image_path, added_by, last_updated_by)
        VALUES (:client_id, :automation_name, :type, :tool_used, :monthly_fee, :status, :image_path, :added_by, :last_updated_by)');
    $stmt->execute($data);
    return (int) $pdo->lastInsertId();
}

function updateAutomation(PDO $pdo, array $data): bool
{
    $stmt = $pdo->prepare('UPDATE automations SET automation_name = :automation_name, type = :type,
        tool_used = :tool_used, monthly_fee = :monthly_fee, status = :status, last_updated_by = :user_id
        WHERE id = :id AND deleted_at IS NULL');
    $stmt->execute([
        'automation_name' => $data['automation_name'],
        'type' => $data['type'],
        'tool_used' => $data['tool_used'],
        'monthly_fee' => $data['monthly_fee'],
        'status' => $data['status'],
        'user_id' => $data['user_id'],
        'id' => $data['id'],
    ]);
    if ($stmt->rowCount() > 0) {
        return true;
    }
    $check = $pdo->prepare('SELECT 1 FROM automations WHERE id = ? AND deleted_at IS NULL');
    $check->execute([$data['id']]);
    return (bool) $check->fetchColumn();
}

function softDeleteClient(PDO $pdo, int $id, int $userId): void
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE automations SET deleted_by = ?, deleted_at = CURRENT_TIMESTAMP
                               WHERE client_id = ? AND deleted_at IS NULL');
        $stmt->execute([$userId, $id]);
        $stmt = $pdo->prepare('UPDATE clients SET deleted_by = ?, deleted_at = CURRENT_TIMESTAMP
                               WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$userId, $id]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Client not found.');
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function softDeleteAutomation(PDO $pdo, int $id, int $userId): bool
{
    $stmt = $pdo->prepare('UPDATE automations SET deleted_by = ?, deleted_at = CURRENT_TIMESTAMP
                           WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$userId, $id]);
    return $stmt->rowCount() === 1;
}

function restoreAutomation(PDO $pdo, int $id, int $userId): bool
{
    $stmt = $pdo->prepare('UPDATE automations x
                           INNER JOIN clients c ON c.id = x.client_id AND c.deleted_at IS NULL
                           SET x.deleted_by = NULL, x.deleted_at = NULL, x.last_updated_by = ?
                           WHERE x.id = ? AND x.deleted_at IS NOT NULL');
    $stmt->execute([$userId, $id]);
    return $stmt->rowCount() === 1;
}

function deletedClients(PDO $pdo): array
{
    $sql = "SELECT c.business_name, c.email, c.deleted_at,
                   COALESCE(a.username, 'Unknown') AS added_by_name,
                   COALESCE(d.username, 'Unknown') AS deleted_by_name
            FROM clients c
            LEFT JOIN users a ON a.id = c.added_by
            LEFT JOIN users d ON d.id = c.deleted_by
            WHERE c.deleted_at IS NOT NULL ORDER BY c.deleted_at DESC";
    return $pdo->query($sql)->fetchAll();
}

function deletedAutomations(PDO $pdo): array
{
    $sql = "SELECT x.id, x.image_path, x.automation_name, c.business_name, x.deleted_at,
                   COALESCE(a.username, 'Unknown') AS added_by_name,
                   COALESCE(d.username, 'Unknown') AS deleted_by_name
            FROM automations x
            LEFT JOIN clients c ON c.id = x.client_id
            LEFT JOIN users a ON a.id = x.added_by
            LEFT JOIN users d ON d.id = x.deleted_by
            WHERE x.deleted_at IS NOT NULL ORDER BY x.deleted_at DESC";
    return $pdo->query($sql)->fetchAll();
}
