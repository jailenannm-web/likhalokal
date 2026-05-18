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
    $stmt = db()->prepare(
        'INSERT INTO messages (sender_id, receiver_id, business_id, product_id, message_content, is_read, is_auto_reply, attachment_path, attachment_type, inquiry_context, conversation_type, created_at)
         VALUES (?,?,?,?,?,0,?,?,?,?,?,NOW())'
    );
    $stmt->execute([
        $senderId,
        $receiverId,
        $businessId,
        $productId,
        $text,
        $isAutoReply ? 1 : 0,
        $attachmentPath,
        $attachmentType,
        $inquiryContext,
        $conversationType,
    ]);
    return (int) db()->lastInsertId();
}

function maybe_auto_reply(int $businessId, int $customerId, int $sellerId, string $userMessage, ?int $productId): ?array
{
    $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? LIMIT 1');
    $stmt->execute([$businessId]);
    $business = $stmt->fetch();
    if (!$business || !(int) ($business['auto_reply_enabled'] ?? 0)) {
        return null;
    }
    if (!should_send_auto_reply($businessId, $sellerId, $customerId)) {
        return null;
    }
    $productName = null;
    if ($productId) {
        $ps = db()->prepare('SELECT product_name FROM products WHERE id = ? LIMIT 1');
        $ps->execute([$productId]);
        $pr = $ps->fetch();
        $productName = $pr['product_name'] ?? null;
    }
    $replyText = build_auto_reply_text($business, $userMessage, $productName);
    $id = insert_message($sellerId, $customerId, $businessId, $productId, $replyText, 'business_inquiry', true, null, null, null);
    return fetch_message_row($id);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        api_require_login();
        $uid = current_user_id();
        $role = current_user_role();
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
            $stmt = db()->prepare(
                'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
                 FROM messages m
                 JOIN users us ON us.id = m.sender_id
                 JOIN users ur ON ur.id = m.receiver_id
                 WHERE m.conversation_type = \'admin_support\'
                   AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                 ORDER BY m.created_at ASC'
            );
            $stmt->execute([$uid, $peerId, $peerId, $uid]);
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
                $stmt = db()->prepare(
                    'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
                     FROM messages m
                     JOIN users us ON us.id = m.sender_id
                     JOIN users ur ON ur.id = m.receiver_id
                     WHERE m.business_id = ?
                       AND m.conversation_type = \'business_inquiry\'
                       AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                     ORDER BY m.created_at ASC'
                );
                $stmt->execute([$businessId, $uid, $receiverId, $receiverId, $uid]);
            } else {
                $stmt = db()->prepare(
                    'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
                     FROM messages m
                     JOIN users us ON us.id = m.sender_id
                     JOIN users ur ON ur.id = m.receiver_id
                     WHERE m.business_id = ?
                       AND m.conversation_type = \'business_inquiry\'
                       AND (m.sender_id = ? OR m.receiver_id = ?)
                     ORDER BY m.created_at ASC'
                );
                $stmt->execute([$businessId, $uid, $uid]);
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

            $id = insert_message($me, $receiverId, $businessId, $productId, $text, 'business_inquiry', false, $attachmentPath, $attachmentType, null);
            $row = fetch_message_row($id);
            $autoReplySent = false;
            $extra = [];
            if ($role === 'local_user' && $conversationType === 'business_inquiry' && $owner === $receiverId) {
                $auto = maybe_auto_reply($businessId, $me, $owner, $text, $productId);
                if ($auto) {
                    $autoReplySent = true;
                    $extra['auto_reply'] = $auto;
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
