<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare("SELECT * FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 20");
    $stmt->execute();
    json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    api_require_roles(['admin']);
    $input = array_merge($_POST, json_decode(file_get_contents('php://input') ?: '[]', true) ?: []);
    if (!verify_csrf($input['csrf_token'] ?? null)) {
        json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
    }
    $action = $input['action'] ?? '';
    if ($action === 'create') {
        $stmt = db()->prepare(
            'INSERT INTO announcements (admin_id, title, content, image, status, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            current_user_id(),
            trim((string) ($input['title'] ?? '')),
            trim((string) ($input['content'] ?? '')),
            trim((string) ($input['image'] ?? '')),
            (string) ($input['status'] ?? 'published'),
        ]);
        json_response(['success' => true, 'message' => 'Created', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }
    if ($action === 'update') {
        $id = (int) ($input['id'] ?? 0);
        $stmt = db()->prepare(
            'UPDATE announcements SET title=?, content=?, image=?, status=?, updated_at=NOW() WHERE id=?'
        );
        $stmt->execute([
            trim((string) ($input['title'] ?? '')),
            trim((string) ($input['content'] ?? '')),
            trim((string) ($input['image'] ?? '')),
            (string) ($input['status'] ?? 'published'),
            $id,
        ]);
        json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $id]]);
    }
    if ($action === 'delete') {
        $id = (int) ($input['id'] ?? 0);
        db()->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
        json_response(['success' => true, 'message' => 'Deleted', 'data' => []]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
