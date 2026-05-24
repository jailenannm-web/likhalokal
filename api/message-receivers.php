<?php

declare(strict_types=1);

ob_start();
require_once __DIR__ . '/_init.php';

function receiver_json(array $payload, int $code = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    json_response($payload, $code);
}

function receiver_result(
    string $id,
    string $label,
    string $role,
    string $roleLabel,
    string $meta,
    string $conversationType,
    int $receiverId,
    ?int $businessId,
    string $redirect
): array {
    return [
        'id' => $id,
        'label' => $label,
        'role' => $role,
        'role_label' => $roleLabel,
        'meta' => $meta,
        'conversation_type' => $conversationType,
        'receiver_id' => $receiverId,
        'business_id' => $businessId,
        'redirect' => $redirect,
    ];
}

try {
    api_require_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        receiver_json(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    $uid = (int) current_user_id();
    $role = (string) current_user_role();
    $q = trim((string) ($_GET['q'] ?? ''));
    $like = '%' . $q . '%';
    $results = [];
    $limit = 10;

    if ($role === 'admin') {
        $sql = "SELECT id, full_name, email, role
                FROM users
                WHERE id != ?
                  AND role IN ('seller', 'local_user')
                  AND status = 'active'";
        $params = [$uid];
        if ($q !== '') {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR role LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= " ORDER BY full_name ASC LIMIT {$limit}";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $row) {
            $receiverId = (int) $row['id'];
            $receiverTab = $row['role'] === 'seller' ? 'sellers' : 'users';
            $results[] = receiver_result(
                'admin_support:' . $receiverId,
                (string) $row['full_name'],
                (string) $row['role'],
                role_display_label((string) $row['role']),
                (string) ($row['email'] ?? ''),
                'admin_support',
                $receiverId,
                null,
                ADMIN_URL . 'messages.php?tab=' . $receiverTab . '&peer_id=' . $receiverId
            );
        }
    } elseif ($role === 'seller') {
        $adminId = first_admin_user_id();
        if ($adminId && ($q === '' || stripos('Tourism Admin Official Support Admin', $q) !== false)) {
            $results[] = receiver_result(
                'admin_support:' . $adminId,
                'Tourism Admin',
                'admin',
                'Admin',
                'Official Support',
                'admin_support',
                (int) $adminId,
                null,
                SELLER_URL . 'messages.php?view=admin'
            );
        }

        $bst = db()->prepare("SELECT id FROM businesses WHERE user_id = ? AND status = 'approved' ORDER BY id ASC LIMIT 1");
        $bst->execute([$uid]);
        $businessId = (int) ($bst->fetchColumn() ?: 0);

        if ($businessId > 0 && count($results) < $limit) {
            $sql = "SELECT id, full_name, email
                    FROM users
                    WHERE id != ?
                      AND role = 'local_user'
                      AND status = 'active'";
            $params = [$uid];
            if ($q !== '') {
                $sql .= " AND (full_name LIKE ? OR email LIKE ? OR role LIKE ?)";
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            $remaining = $limit - count($results);
            $sql .= " ORDER BY full_name ASC LIMIT {$remaining}";
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $row) {
                $receiverId = (int) $row['id'];
                $results[] = receiver_result(
                    'business_user:' . $businessId . ':' . $receiverId,
                    (string) $row['full_name'],
                    'local_user',
                    'Local User',
                    (string) ($row['email'] ?? ''),
                    'business_inquiry',
                    $receiverId,
                    $businessId,
                    SELLER_URL . 'messages.php?view=customers&customer_id=' . $receiverId
                );
            }
        }
    } elseif ($role === 'local_user') {
        $adminId = first_admin_user_id();
        if ($adminId && ($q === '' || stripos('Tourism Admin Official Support Admin', $q) !== false)) {
            $results[] = receiver_result(
                'admin_support:' . $adminId,
                'Tourism Admin',
                'admin',
                'Admin',
                'Official Support',
                'admin_support',
                (int) $adminId,
                null,
                USER_DASH_URL . 'messages.php?view=admin'
            );
        }

        if (count($results) < $limit) {
            $sql = "SELECT b.id AS business_id, b.business_name, b.business_type, b.business_category,
                           u.id AS owner_id, u.full_name AS owner_name
                    FROM businesses b
                    JOIN users u ON u.id = b.user_id
                    WHERE b.status = 'approved'
                      AND u.id != ?";
            $params = [$uid];
            if ($q !== '') {
                $sql .= " AND (
                    b.business_name LIKE ?
                    OR b.business_type LIKE ?
                    OR COALESCE(b.business_category, '') LIKE ?
                    OR u.full_name LIKE ?
                    OR 'seller' LIKE ?
                )";
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            $remaining = $limit - count($results);
            $sql .= " ORDER BY b.business_name ASC LIMIT {$remaining}";
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $row) {
                $businessId = (int) $row['business_id'];
                $ownerId = (int) $row['owner_id'];
                $category = trim((string) ($row['business_category'] ?? ''));
                if ($category === '') {
                    $category = business_type_label((string) ($row['business_type'] ?? ''));
                }
                $results[] = receiver_result(
                    'business:' . $businessId,
                    (string) $row['business_name'],
                    'seller',
                    'Seller / Local Business',
                    $category . ' - ' . (string) $row['owner_name'],
                    'business_inquiry',
                    $ownerId,
                    $businessId,
                    USER_DASH_URL . 'messages.php?view=sellers&business_id=' . $businessId
                );
            }
        }
    } else {
        receiver_json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    receiver_json(['success' => true, 'message' => '', 'data' => ['receivers' => $results]]);
} catch (Throwable $e) {
    error_log('message-receivers.php: ' . $e->getMessage());
    receiver_json(['success' => false, 'message' => APP_DEBUG ? $e->getMessage() : 'Server error'], 500);
}
