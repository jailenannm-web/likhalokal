<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $businessId = isset($_GET['business_id']) ? (int) $_GET['business_id'] : null;
    $attractionId = isset($_GET['attraction_id']) ? (int) $_GET['attraction_id'] : null;
    $status = $_GET['status'] ?? '';

    if ($businessId) {
        $stmt = db()->prepare(
            "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.business_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC"
        );
        $stmt->execute([$businessId]);
        json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
    }
    if ($attractionId) {
        $stmt = db()->prepare(
            "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.attraction_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC"
        );
        $stmt->execute([$attractionId]);
        json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
    }

    if (is_logged_in() && current_user_role() === 'admin') {
        $sql = 'SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE 1=1';
        $params = [];
        if ($status !== '') {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY r.created_at DESC LIMIT 200';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
    }

    json_response(['success' => false, 'message' => 'Specify business_id or attraction_id', 'data' => []], 400);
}

if ($method === 'POST') {
    $input = array_merge($_POST, json_decode(file_get_contents('php://input') ?: '[]', true) ?: []);
    $action = $input['action'] ?? '';

    if ($action === 'create') {
        api_require_roles(['local_user']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $bid = isset($input['business_id']) ? (int) $input['business_id'] : null;
        $aid = isset($input['attraction_id']) ? (int) $input['attraction_id'] : null;
        if (($bid && $aid) || (!$bid && !$aid)) {
            json_response(['success' => false, 'message' => 'Provide exactly one of business_id or attraction_id', 'data' => []], 422);
        }
        $rating = (int) ($input['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            json_response(['success' => false, 'message' => 'Rating 1-5 required', 'data' => []], 422);
        }
        $comment = trim((string) ($input['comment'] ?? ''));
        $stmt = db()->prepare(
            'INSERT INTO reviews (user_id, business_id, attraction_id, rating, comment, status, created_at, updated_at) VALUES (?,?,?,?,?,\'pending\',NOW(),NOW())'
        );
        $stmt->execute([current_user_id(), $bid, $aid, $rating, $comment]);
        json_response(['success' => true, 'message' => 'Review submitted for moderation', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }

    if (in_array($action, ['approve', 'reject'], true)) {
        api_require_roles(['admin']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $id = (int) ($input['id'] ?? 0);
        $st = $action === 'approve' ? 'approved' : 'rejected';
        db()->prepare('UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$st, $id]);
        json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $id]]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
