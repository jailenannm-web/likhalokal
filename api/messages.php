<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

function business_owner_id(int $businessId): ?int
{
    $stmt = db()->prepare('SELECT user_id FROM businesses WHERE id = ? LIMIT 1');
    $stmt->execute([$businessId]);
    $row = $stmt->fetch();
    return $row ? (int) $row['user_id'] : null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    api_require_login();
    $businessId = isset($_GET['business_id']) ? (int) $_GET['business_id'] : null;
    $receiverId = isset($_GET['receiver_id']) ? (int) $_GET['receiver_id'] : null;

    if ($businessId) {
        $uid = current_user_id();
        $stmt = db()->prepare(
            'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
             FROM messages m
             JOIN users us ON us.id = m.sender_id
             JOIN users ur ON ur.id = m.receiver_id
             WHERE m.business_id = ? AND (m.sender_id = ? OR m.receiver_id = ?)
             ORDER BY m.created_at ASC'
        );
        $stmt->execute([$businessId, $uid, $uid]);
        json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
    }

    if ($receiverId) {
        $uid = current_user_id();
        $stmt = db()->prepare(
            'SELECT m.*, us.full_name AS sender_name, ur.full_name AS receiver_name
             FROM messages m
             JOIN users us ON us.id = m.sender_id
             JOIN users ur ON ur.id = m.receiver_id
             WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.created_at ASC'
        );
        $stmt->execute([$uid, $receiverId, $receiverId, $uid]);
        json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
    }

    json_response(['success' => false, 'message' => 'business_id or receiver_id required', 'data' => []], 400);
}

if ($method === 'POST') {
    api_require_login();
    $input = array_merge($_POST, json_decode(file_get_contents('php://input') ?: '[]', true) ?: []);
    $action = $input['action'] ?? '';

    if ($action === 'send') {
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $businessId = (int) ($input['business_id'] ?? 0);
        $productId = isset($input['product_id']) && $input['product_id'] !== '' ? (int) $input['product_id'] : null;
        $text = trim((string) ($input['message_content'] ?? ''));
        if ($businessId < 1 || $text === '') {
            json_response(['success' => false, 'message' => 'business_id and message required', 'data' => []], 422);
        }
        $owner = business_owner_id($businessId);
        if (!$owner) {
            json_response(['success' => false, 'message' => 'Business not found', 'data' => []], 404);
        }
        $me = current_user_id();
        $role = current_user_role();
        if ($role === 'local_user' && $owner === $me) {
            json_response(['success' => false, 'message' => 'Cannot message your own business', 'data' => []], 422);
        }
        $receiver = (int) ($input['receiver_id'] ?? 0);
        if ($receiver < 1) {
            if ($role === 'local_user') {
                $receiver = $owner;
            } elseif ($role === 'seller' && $owner === $me) {
                $stmt = db()->prepare(
                    'SELECT sender_id FROM messages WHERE business_id = ? AND sender_id != ? ORDER BY created_at DESC LIMIT 1'
                );
                $stmt->execute([$businessId, $me]);
                $row = $stmt->fetch();
                if (!$row) {
                    json_response(['success' => false, 'message' => 'No customer thread found', 'data' => []], 422);
                }
                $receiver = (int) $row['sender_id'];
            } else {
                json_response(['success' => false, 'message' => 'receiver_id required', 'data' => []], 422);
            }
        }
        if ($receiver === $me) {
            json_response(['success' => false, 'message' => 'Invalid receiver', 'data' => []], 422);
        }
        $stmt = db()->prepare(
            'INSERT INTO messages (sender_id, receiver_id, business_id, product_id, message_content, is_read, created_at) VALUES (?,?,?,?,?,0,NOW())'
        );
        $stmt->execute([$me, $receiver, $businessId, $productId, $text]);
        json_response(['success' => true, 'message' => 'Sent', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }

    if ($action === 'mark_read') {
        $businessId = (int) ($input['business_id'] ?? 0);
        $me = current_user_id();
        db()->prepare(
            'UPDATE messages SET is_read = 1 WHERE business_id = ? AND receiver_id = ? AND is_read = 0'
        )->execute([$businessId, $me]);
        json_response(['success' => true, 'message' => 'Marked read', 'data' => []]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
