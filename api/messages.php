<?php

declare(strict_types=1);

ob_start();
require_once __DIR__ . '/_init.php';

function messages_json(array $payload, int $code = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    json_response($payload, $code);
}

function business_owner_id(int $businessId): ?int
{
    $stmt = db()->prepare('SELECT user_id FROM businesses WHERE id = ? LIMIT 1');
    $stmt->execute([$businessId]);
    $row = $stmt->fetch();
    return $row ? (int) $row['user_id'] : null;
}

function message_deleted_after(int $userId, string $conversationType, ?int $businessId, int $peerUserId): string
{
    $sql = 'SELECT deleted_at FROM message_conversation_deletions
            WHERE user_id = ? AND conversation_type = ? AND peer_user_id = ?';
    $params = [$userId, $conversationType, $peerUserId];
    if ($businessId === null) {
        $sql .= ' AND business_id IS NULL';
    } else {
        $sql .= ' AND business_id = ?';
        $params[] = $businessId;
    }
    $sql .= ' ORDER BY deleted_at DESC LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $deletedAt = $stmt->fetchColumn();
    return $deletedAt ? (string) $deletedAt : '1000-01-01 00:00:00';
}

function hide_conversation_for_user(int $userId, string $conversationType, ?int $businessId, int $peerUserId): void
{
    restore_conversation_for_user($userId, $conversationType, $businessId, $peerUserId);
    $businessExpr = $businessId === null ? 'NULL' : '?';
    $sql = "INSERT INTO message_conversation_deletions
            (user_id, conversation_type, business_id, peer_user_id, deleted_at)
            VALUES (?, ?, {$businessExpr}, ?, NOW())";
    $params = [$userId, $conversationType];
    if ($businessId !== null) {
        $params[] = $businessId;
    }
    $params[] = $peerUserId;
    db()->prepare($sql)->execute($params);
}

function restore_conversation_for_user(int $userId, string $conversationType, ?int $businessId, int $peerUserId): void
{
    $sql = 'DELETE FROM message_conversation_deletions
            WHERE user_id = ? AND conversation_type = ? AND peer_user_id = ?';
    $params = [$userId, $conversationType, $peerUserId];
    if ($businessId === null) {
        $sql .= ' AND business_id IS NULL';
    } else {
        $sql .= ' AND business_id = ?';
        $params[] = $businessId;
    }
    db()->prepare($sql)->execute($params);
}

function message_receiver_options(int $userId, string $role): array
{
    if ($role === 'admin') {
        $rows = db()->query(
            "SELECT id, full_name AS label, role, NULL AS business_id, NULL AS meta
             FROM users
             WHERE role IN ('seller','local_user') AND status = 'active' AND id != " . (int) $userId . "
             ORDER BY role DESC, full_name ASC"
        )->fetchAll();
        return array_map(static function (array $row): array {
            return [
                'value' => 'admin_support:' . (int) $row['id'],
                'label' => $row['label'],
                'role' => $row['role'],
                'meta' => role_display_label((string) $row['role']),
            ];
        }, $rows);
    }

    if ($role === 'seller') {
        $options = [];
        $adminId = first_admin_user_id();
        if ($adminId) {
            $options[] = [
                'value' => 'admin_support:' . $adminId,
                'label' => 'Tourism Admin',
                'role' => 'admin',
                'meta' => 'Official Support',
            ];
        }
        $stmt = db()->prepare(
            "SELECT u.id, u.full_name, MAX(m.created_at) AS last_at
             FROM messages m
             JOIN businesses b ON b.id = m.business_id
             JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
             WHERE b.user_id = ?
               AND m.conversation_type = 'business_inquiry'
               AND u.role = 'local_user'
             GROUP BY u.id, u.full_name
             ORDER BY last_at DESC, u.full_name ASC"
        );
        $stmt->execute([$userId, $userId]);
        foreach ($stmt->fetchAll() as $row) {
            $options[] = [
                'value' => 'user:' . (int) $row['id'],
                'label' => $row['full_name'],
                'role' => 'local_user',
                'meta' => 'Customer',
            ];
        }
        return $options;
    }

    if ($role === 'local_user') {
        $options = [];
        $adminId = first_admin_user_id();
        if ($adminId) {
            $options[] = [
                'value' => 'admin_support:' . $adminId,
                'label' => 'Tourism Admin',
                'role' => 'admin',
                'meta' => 'Official Support',
            ];
        }
        $rows = db()->query(
            "SELECT b.id AS business_id, b.business_name AS label, u.id AS owner_id, u.full_name AS owner_name
             FROM businesses b
             JOIN users u ON u.id = b.user_id
             WHERE b.status = 'approved'
             ORDER BY b.business_name ASC"
        )->fetchAll();
        foreach ($rows as $row) {
            if ((int) $row['owner_id'] === $userId) {
                continue;
            }
            $options[] = [
                'value' => 'business:' . (int) $row['business_id'],
                'label' => $row['label'],
                'role' => 'seller',
                'meta' => 'Seller: ' . $row['owner_name'],
            ];
        }
        return $options;
    }

    return [];
}

function fetch_message_row(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
         FROM messages m
         JOIN users us ON us.id = m.sender_id
         JOIN users ur ON ur.id = m.receiver_id
         WHERE m.id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? enrich_message_row($row) : null;
}

function enrich_message_row(array $row): array
{
    if (!empty($row['attachment_path'])) {
        $row['attachment_path'] = normalize_message_attachment_path((string) $row['attachment_path']);
        $row['attachment_url'] = message_attachment_url($row['attachment_path']);
    }
    $row['product'] = null;
    if (!empty($row['product_id'])) {
        $stmt = db()->prepare(
            'SELECT p.id, p.product_name, p.price, p.image, p.availability, p.category, p.business_id, b.business_name
             FROM products p
             INNER JOIN businesses b ON b.id = p.business_id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->execute([(int) $row['product_id']]);
        $product = $stmt->fetch();
        if ($product) {
            $row['product'] = [
                'id' => (int) $product['id'],
                'name' => (string) $product['product_name'],
                'price' => (float) $product['price'] > 0 ? 'PHP ' . number_format((float) $product['price'], 2) : 'Contact seller',
                'image_url' => media_url($product['image'] ?? null, asset_url('images/likhalokal-logo.png')),
                'availability' => ucfirst((string) ($product['availability'] ?? 'available')),
                'category' => product_category_label((string) ($product['category'] ?? 'other')),
                'business_id' => (int) $product['business_id'],
                'business_name' => (string) $product['business_name'],
                'url' => vendor_profile_url((int) $product['business_id']),
                'shop_url' => vendor_profile_url((int) $product['business_id']),
            ];
        }
    }
    if (!$row['product'] && ($row['inquiry_context'] ?? '') === 'product_inquiry') {
        $row['product'] = [
            'id' => null,
            'name' => preg_replace('/^Inquiring about:\s*/i', '', (string) ($row['message_content'] ?? 'Product inquiry')),
            'price' => '',
            'image_url' => asset_url('images/likhalokal-logo.png'),
            'availability' => 'Product unavailable',
            'category' => '',
            'business_id' => (int) ($row['business_id'] ?? 0),
            'business_name' => '',
            'url' => !empty($row['business_id']) ? vendor_profile_url((int) $row['business_id']) : '',
            'shop_url' => !empty($row['business_id']) ? vendor_profile_url((int) $row['business_id']) : '',
        ];
    }
    return $row;
}

/** @param array<int, array<string, mixed>> $rows */
function enrich_message_rows(array $rows): array
{
    return array_map('enrich_message_row', $rows);
}

function insert_message(
    int $senderId,
    int $receiverId,
    ?int $businessId,
    ?int $productId,
    string $text,
    string $conversationType,
    bool $isAutoReply,
    ?string $attachmentPath,
    ?string $attachmentType,
    ?string $inquiryContext
): int {
    $messageType = $isAutoReply
        ? 'auto_reply'
        : ($inquiryContext === 'product_inquiry' ? 'product_inquiry' : ($attachmentPath ? 'image' : 'text'));
    $hasMessageType = db_column_exists('messages', 'message_type');
    $columns = 'sender_id, receiver_id, business_id, product_id, ' . ($hasMessageType ? 'message_type, ' : '') . 'message_content, is_read, is_auto_reply, attachment_path, attachment_type, inquiry_context, conversation_type, created_at';
    $marks = '?,?,?,?,?' . ($hasMessageType ? ',?' : '') . ',0,?,?,?,?,?,NOW()';
    $params = [
        $senderId,
        $receiverId,
        $businessId,
        $productId,
    ];
    if ($hasMessageType) {
        $params[] = $messageType;
    }
    $params = array_merge($params, [
        $text,
        $isAutoReply ? 1 : 0,
        $attachmentPath,
        $attachmentType,
        $inquiryContext,
        $conversationType,
    ]);
    $stmt = db()->prepare("INSERT INTO messages ($columns) VALUES ($marks)");
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}

function valid_product_for_business(int $productId, int $businessId): ?array
{
    if ($productId < 1 || $businessId < 1) {
        return null;
    }
    $stmt = db()->prepare(
        'SELECT p.*, b.business_name, b.user_id AS owner_user_id
         FROM products p
         INNER JOIN businesses b ON b.id = p.business_id
         WHERE p.id = ? AND p.business_id = ? AND b.status = \'approved\'
         LIMIT 1'
    );
    $stmt->execute([$productId, $businessId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function ensure_product_inquiry_message(int $customerId, int $sellerId, int $businessId, int $productId): ?array
{
    $product = valid_product_for_business($productId, $businessId);
    if (!$product || (int) $product['owner_user_id'] !== $sellerId) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id FROM messages
         WHERE sender_id = ? AND receiver_id = ? AND business_id = ? AND product_id = ?
           AND conversation_type = \'business_inquiry\'
           AND inquiry_context = \'product_inquiry\'
           AND created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
         ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute([$customerId, $sellerId, $businessId, $productId]);
    $existingId = (int) ($stmt->fetchColumn() ?: 0);
    if ($existingId > 0) {
        return fetch_message_row($existingId);
    }

    restore_conversation_for_user($customerId, 'business_inquiry', $businessId, $sellerId);
    $id = insert_message(
        $customerId,
        $sellerId,
        $businessId,
        $productId,
        'Inquiring about: ' . (string) $product['product_name'],
        'business_inquiry',
        false,
        null,
        null,
        'product_inquiry'
    );
    return fetch_message_row($id);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        api_require_login();
        $uid = current_user_id();
        $role = current_user_role();
        $action = (string) ($_GET['action'] ?? '');
        if ($action === 'receivers') {
            messages_json(['success' => true, 'message' => '', 'data' => ['receivers' => message_receiver_options($uid, (string) $role)]]);
        }
        if ($action === 'quick_replies') {
            if ($role !== 'local_user') {
                messages_json(['success' => true, 'message' => '', 'data' => ['quick_replies' => []]]);
            }
            $businessId = isset($_GET['business_id']) ? (int) $_GET['business_id'] : 0;
            if ($businessId < 1) {
                messages_json(['success' => false, 'message' => 'business_id required'], 400);
            }
            $productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
            $stmt = db()->prepare(
                "SELECT b.*, u.id AS owner_user_id
                 FROM businesses b
                 JOIN users u ON u.id = b.user_id
                 WHERE b.id = ? AND b.status = 'approved'
                 LIMIT 1"
            );
            $stmt->execute([$businessId]);
            $business = $stmt->fetch();
            if (!$business || (int) $business['owner_user_id'] === $uid) {
                messages_json(['success' => true, 'message' => '', 'data' => ['quick_replies' => []]]);
            }
            $product = null;
            if ($productId > 0) {
                $product = valid_product_for_business($productId, $businessId);
            }
            messages_json([
                'success' => true,
                'message' => '',
                'data' => ['quick_replies' => business_quick_replies($business, $product)],
            ]);
        }
        $businessId = isset($_GET['business_id']) ? (int) $_GET['business_id'] : 0;
        $receiverId = isset($_GET['receiver_id']) ? (int) $_GET['receiver_id'] : 0;
        $conversationType = (string) ($_GET['conversation_type'] ?? '');
        $tab = (string) ($_GET['tab'] ?? '');

        if ($conversationType === 'admin_support') {
            $adminId = first_admin_user_id();
            if (!$adminId) {
                messages_json(['success' => false, 'message' => 'No admin available'], 503);
            }
            if ($role === 'admin') {
                if ($receiverId < 1) {
                    messages_json(['success' => false, 'message' => 'receiver_id required'], 400);
                }
                $peerId = $receiverId;
                $peerCheck = db()->prepare("SELECT id FROM users WHERE id = ? AND role IN ('local_user','seller') LIMIT 1");
                $peerCheck->execute([$peerId]);
                if (!$peerCheck->fetch()) {
                    messages_json(['success' => false, 'message' => 'Invalid peer'], 400);
                }
            } else {
                $peerId = $adminId;
            }
            $deletedAfter = message_deleted_after($uid, 'admin_support', null, $peerId);
            $stmt = db()->prepare(
                'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
                 FROM messages m
                 JOIN users us ON us.id = m.sender_id
                 JOIN users ur ON ur.id = m.receiver_id
                 WHERE m.conversation_type = \'admin_support\'
                   AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                   AND m.created_at > ?
                 ORDER BY m.created_at ASC'
            );
            $stmt->execute([$uid, $peerId, $peerId, $uid, $deletedAfter]);
            $rows = $stmt->fetchAll();
            db()->prepare(
                'UPDATE messages SET is_read = 1 WHERE conversation_type = \'admin_support\' AND receiver_id = ? AND sender_id = ? AND is_read = 0'
            )->execute([$uid, $peerId]);
            messages_json(['success' => true, 'message' => '', 'data' => enrich_message_rows($rows)]);
        }

        if ($role === 'admin' && $tab !== '') {
            $filterRole = match ($tab) {
                'users' => 'local_user',
                'sellers' => 'seller',
                default => null,
            };
            $baseSql = "SELECT u.id AS peer_id, u.full_name, u.role,
                    (SELECT message_content FROM messages m2
                     WHERE m2.conversation_type = 'admin_support'
                       AND ((m2.sender_id = u.id AND m2.receiver_id = ?) OR (m2.sender_id = ? AND m2.receiver_id = u.id))
                     ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
                    (SELECT MAX(created_at) FROM messages m3
                     WHERE m3.conversation_type = 'admin_support'
                       AND ((m3.sender_id = u.id AND m3.receiver_id = ?) OR (m3.sender_id = ? AND m3.receiver_id = u.id))) AS last_at,
                    (SELECT COUNT(*) FROM messages m4
                     WHERE m4.conversation_type = 'admin_support' AND m4.sender_id = u.id AND m4.receiver_id = ? AND m4.is_read = 0) AS unread_count
                    FROM users u
                    WHERE u.role != 'admin' AND u.id IN (
                        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END
                        FROM messages WHERE conversation_type = 'admin_support' AND (sender_id = ? OR receiver_id = ?)
                    )";
            if ($filterRole) {
                $stmt = db()->prepare($baseSql . ' AND u.role = ? ORDER BY last_at DESC');
                $stmt->execute([$uid, $uid, $uid, $uid, $uid, $uid, $uid, $uid, $filterRole]);
            } else {
                $stmt = db()->prepare($baseSql . ' ORDER BY last_at DESC');
                $stmt->execute([$uid, $uid, $uid, $uid, $uid, $uid, $uid, $uid]);
            }
            messages_json(['success' => true, 'message' => '', 'data' => ['threads' => $stmt->fetchAll()]]);
        }

        if ($businessId > 0) {
            if ($receiverId > 0) {
                $deletedAfter = message_deleted_after($uid, 'business_inquiry', $businessId, $receiverId);
                $stmt = db()->prepare(
                    'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
                     FROM messages m
                     JOIN users us ON us.id = m.sender_id
                     JOIN users ur ON ur.id = m.receiver_id
                     WHERE m.business_id = ?
                       AND m.conversation_type = \'business_inquiry\'
                       AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                       AND m.created_at > ?
                     ORDER BY m.created_at ASC'
                );
                $stmt->execute([$businessId, $uid, $receiverId, $receiverId, $uid, $deletedAfter]);
            } else {
                $owner = business_owner_id($businessId);
                $deletedAfter = $owner ? message_deleted_after($uid, 'business_inquiry', $businessId, $owner) : '1000-01-01 00:00:00';
                $stmt = db()->prepare(
                    'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
                     FROM messages m
                     JOIN users us ON us.id = m.sender_id
                     JOIN users ur ON ur.id = m.receiver_id
                     WHERE m.business_id = ?
                       AND m.conversation_type = \'business_inquiry\'
                       AND (m.sender_id = ? OR m.receiver_id = ?)
                       AND m.created_at > ?
                     ORDER BY m.created_at ASC'
                );
                $stmt->execute([$businessId, $uid, $uid, $deletedAfter]);
            }
            $rows = $stmt->fetchAll();
            if ($receiverId > 0) {
                db()->prepare(
                    'UPDATE messages SET is_read = 1 WHERE business_id = ? AND conversation_type = \'business_inquiry\' AND receiver_id = ? AND sender_id = ? AND is_read = 0'
                )->execute([$businessId, $uid, $receiverId]);
            } else {
                db()->prepare(
                    'UPDATE messages SET is_read = 1 WHERE business_id = ? AND conversation_type = \'business_inquiry\' AND receiver_id = ? AND is_read = 0'
                )->execute([$businessId, $uid]);
            }
            messages_json(['success' => true, 'message' => '', 'data' => enrich_message_rows($rows)]);
        }

        messages_json(['success' => false, 'message' => 'business_id or conversation required'], 400);
    }

    if ($method === 'POST') {
        api_require_login();
        $input = $_POST;
        if (empty($input) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
        }
        $action = (string) ($input['action'] ?? '');

        if ($action === 'mark_read') {
            $businessId = (int) ($input['business_id'] ?? 0);
            $receiverId = (int) ($input['receiver_id'] ?? 0);
            $conversationType = (string) ($input['conversation_type'] ?? 'business_inquiry');
            $me = current_user_id();
            if ($conversationType === 'admin_support') {
                $peer = $receiverId > 0 ? $receiverId : first_admin_user_id();
                if ($peer) {
                    db()->prepare(
                        'UPDATE messages SET is_read = 1 WHERE conversation_type = \'admin_support\' AND receiver_id = ? AND sender_id = ? AND is_read = 0'
                    )->execute([$me, $peer]);
                }
            } elseif ($businessId > 0) {
                if ($receiverId > 0) {
                    db()->prepare(
                        'UPDATE messages SET is_read = 1 WHERE business_id = ? AND receiver_id = ? AND sender_id = ? AND is_read = 0'
                    )->execute([$businessId, $me, $receiverId]);
                } else {
                    db()->prepare(
                        'UPDATE messages SET is_read = 1 WHERE business_id = ? AND receiver_id = ? AND is_read = 0'
                    )->execute([$businessId, $me]);
                }
            }
            messages_json(['success' => true, 'message' => 'Marked read', 'data' => []]);
        }

        if ($action === 'delete_conversation') {
            if (!verify_csrf($input['csrf_token'] ?? null)) {
                messages_json(['success' => false, 'message' => 'Invalid CSRF'], 403);
            }
            $me = current_user_id();
            $role = current_user_role();
            $conversationType = (string) ($input['conversation_type'] ?? 'business_inquiry');
            $businessId = isset($input['business_id']) && $input['business_id'] !== '' ? (int) $input['business_id'] : null;
            $peerId = (int) ($input['receiver_id'] ?? 0);

            if ($conversationType === 'admin_support') {
                if ($role === 'admin') {
                    if ($peerId < 1) {
                        messages_json(['success' => false, 'message' => 'receiver_id required'], 422);
                    }
                } else {
                    $peerId = (int) first_admin_user_id();
                }
                hide_conversation_for_user($me, 'admin_support', null, $peerId);
                messages_json(['success' => true, 'message' => 'Conversation deleted.', 'data' => []]);
            }

            if ($businessId === null || $businessId < 1) {
                messages_json(['success' => false, 'message' => 'business_id required'], 422);
            }
            if ($peerId < 1) {
                $owner = business_owner_id($businessId);
                if (!$owner) {
                    messages_json(['success' => false, 'message' => 'Business not found'], 404);
                }
                $peerId = $owner;
            }
            hide_conversation_for_user($me, 'business_inquiry', $businessId, $peerId);
            messages_json(['success' => true, 'message' => 'Conversation deleted.', 'data' => []]);
        }

        if ($action === 'start_product_inquiry') {
            if (!verify_csrf($input['csrf_token'] ?? null)) {
                messages_json(['success' => false, 'message' => 'Invalid CSRF'], 403);
            }
            $me = current_user_id();
            $role = current_user_role();
            if ($role !== 'local_user') {
                messages_json(['success' => false, 'message' => 'Only customers can start product inquiries.'], 403);
            }
            $businessId = (int) ($input['business_id'] ?? 0);
            $productId = (int) ($input['product_id'] ?? 0);
            $owner = business_owner_id($businessId);
            if (!$owner || $owner === $me) {
                messages_json(['success' => false, 'message' => 'Invalid business inquiry.'], 422);
            }
            $message = ensure_product_inquiry_message($me, $owner, $businessId, $productId);
            if (!$message) {
                messages_json(['success' => false, 'message' => 'Product is not available for this seller.'], 422);
            }
            messages_json(['success' => true, 'message' => 'Product inquiry started.', 'data' => ['message' => $message]]);
        }

        if ($action === 'send') {
            if (!verify_csrf($input['csrf_token'] ?? null)) {
                messages_json(['success' => false, 'message' => 'Invalid CSRF'], 403);
            }

            $me = current_user_id();
            $role = current_user_role();
            $conversationType = (string) ($input['conversation_type'] ?? 'business_inquiry');
            if ($conversationType !== 'admin_support') {
                $conversationType = 'business_inquiry';
            }
            $businessId = isset($input['business_id']) && $input['business_id'] !== '' ? (int) $input['business_id'] : null;
            $productId = isset($input['product_id']) && $input['product_id'] !== '' ? (int) $input['product_id'] : null;
            $text = trim((string) ($input['message_content'] ?? ''));
            $receiverId = (int) ($input['receiver_id'] ?? 0);
            $faqType = preg_replace('/[^a-z_]/', '', strtolower((string) ($input['faq_type'] ?? '')));
            if (!in_array($faqType, ['price', 'availability', 'location', 'payment', 'pickup_delivery', 'hours', 'custom'], true)) {
                $faqType = '';
            }

            $attachmentPath = null;
            $attachmentType = null;
            if (!empty($_FILES['attachment']['tmp_name'])) {
                $up = save_message_upload($_FILES['attachment']);
                if ($up) {
                    $attachmentPath = $up['path'];
                    $attachmentType = $up['type'];
                } else {
                    messages_json(['success' => false, 'message' => 'Invalid attachment'], 422);
                }
            }

            if ($text === '' && !$attachmentPath) {
                messages_json(['success' => false, 'message' => 'Please enter a message or choose an image.'], 422);
            }

            if ($conversationType === 'admin_support') {
                $adminId = first_admin_user_id();
                if (!$adminId) {
                    messages_json(['success' => false, 'message' => 'Admin support unavailable'], 503);
                }
                if ($role === 'admin') {
                    if ($receiverId < 1) {
                        messages_json(['success' => false, 'message' => 'receiver_id required'], 422);
                    }
                    $receiver = $receiverId;
                    $peerCheck = db()->prepare("SELECT id FROM users WHERE id = ? AND role IN ('local_user','seller') LIMIT 1");
                    $peerCheck->execute([$receiver]);
                    if (!$peerCheck->fetch()) {
                        messages_json(['success' => false, 'message' => 'Invalid receiver'], 422);
                    }
                } else {
                    if (!in_array($role, ['local_user', 'seller'], true)) {
                        messages_json(['success' => false, 'message' => 'Forbidden'], 403);
                    }
                    $receiver = $adminId;
                }
                if ($receiver === $me) {
                    messages_json(['success' => false, 'message' => 'Invalid receiver'], 422);
                }
                restore_conversation_for_user($me, 'admin_support', null, $receiver);
                $id = insert_message($me, $receiver, null, null, $text, 'admin_support', false, $attachmentPath, $attachmentType, null);
                $row = fetch_message_row($id);
                messages_json(['success' => true, 'message' => 'Sent', 'data' => ['message' => $row]]);
            }

            if ($businessId === null || $businessId < 1) {
                messages_json(['success' => false, 'message' => 'business_id required'], 422);
            }

            $owner = business_owner_id($businessId);
            if (!$owner) {
                messages_json(['success' => false, 'message' => 'Business not found'], 404);
            }

            if ($role === 'local_user' && $owner === $me) {
                messages_json(['success' => false, 'message' => 'Cannot message your own business'], 422);
            }

            if ($role === 'seller' && $owner !== $me) {
                messages_json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            if ($receiverId < 1) {
                if ($role === 'local_user') {
                    $receiverId = $owner;
                } elseif ($role === 'seller' && $owner === $me) {
                    $stmt = db()->prepare(
                        'SELECT sender_id FROM messages WHERE business_id = ? AND sender_id != ? ORDER BY created_at DESC LIMIT 1'
                    );
                    $stmt->execute([$businessId, $me]);
                    $row = $stmt->fetch();
                    if (!$row) {
                        messages_json(['success' => false, 'message' => 'No customer thread found'], 422);
                    }
                    $receiverId = (int) $row['sender_id'];
                } else {
                    messages_json(['success' => false, 'message' => 'receiver_id required'], 422);
                }
            }

            if ($receiverId === $me) {
                messages_json(['success' => false, 'message' => 'Invalid receiver'], 422);
            }
            if ($productId !== null && !valid_product_for_business($productId, $businessId)) {
                messages_json(['success' => false, 'message' => 'Product is not available for this seller.'], 422);
            }

            restore_conversation_for_user($me, 'business_inquiry', $businessId, $receiverId);
            $id = insert_message($me, $receiverId, $businessId, $productId, $text, 'business_inquiry', false, $attachmentPath, $attachmentType, null);
            $row = fetch_message_row($id);
            $autoReplySent = false;
            $extra = [];
            $inquiryText = $text !== '' ? $text : ($attachmentPath ? '[Image inquiry]' : '');
            if (
                $role === 'local_user'
                && $conversationType === 'business_inquiry'
                && $businessId > 0
                && $owner > 0
                && $receiverId === $owner
            ) {
                try {
                    $autoId = insert_business_auto_reply(
                        $businessId,
                        $me,
                        $owner,
                        $inquiryText,
                        $productId,
                        $faqType !== '' ? $faqType : null
                    );
                    if ($autoId) {
                        $auto = fetch_message_row($autoId);
                        if ($auto) {
                            $autoReplySent = true;
                            $extra['auto_reply'] = $auto;
                        }
                    }
                } catch (Throwable $autoEx) {
                    error_log('auto-reply failed: ' . $autoEx->getMessage());
                }
            }
            messages_json([
                'success' => true,
                'message' => 'Message sent.',
                'auto_reply_sent' => $autoReplySent,
                'data' => array_merge(['message' => $row], $extra),
            ]);
        }

        messages_json(['success' => false, 'message' => 'Unknown action'], 400);
    }

    messages_json(['success' => false, 'message' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    error_log('messages.php: ' . $e->getMessage());
    $msg = APP_DEBUG ? $e->getMessage() : 'Server error';
    messages_json(['success' => false, 'message' => $msg], 500);
}
