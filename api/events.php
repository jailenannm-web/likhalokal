<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare("SELECT * FROM events WHERE status = 'published' AND event_date >= CURDATE() - INTERVAL 1 DAY ORDER BY event_date ASC");
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
            'INSERT INTO events (admin_id, title, description, event_date, event_time, location, image, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            current_user_id(),
            trim((string) ($input['title'] ?? '')),
            trim((string) ($input['description'] ?? '')),
            (string) ($input['event_date'] ?? date('Y-m-d')),
            $input['event_time'] ?: null,
            trim((string) ($input['location'] ?? '')),
            trim((string) ($input['image'] ?? '')),
            (string) ($input['status'] ?? 'published'),
        ]);
        json_response(['success' => true, 'message' => 'Created', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }
    if ($action === 'update') {
        $id = (int) ($input['id'] ?? 0);
        $stmt = db()->prepare(
            'UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, image=?, status=?, updated_at=NOW() WHERE id=?'
        );
        $stmt->execute([
            trim((string) ($input['title'] ?? '')),
            trim((string) ($input['description'] ?? '')),
            (string) ($input['event_date'] ?? ''),
            $input['event_time'] ?: null,
            trim((string) ($input['location'] ?? '')),
            trim((string) ($input['image'] ?? '')),
            (string) ($input['status'] ?? 'published'),
            $id,
        ]);
        json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $id]]);
    }
    if ($action === 'delete') {
        $id = (int) ($input['id'] ?? 0);
        db()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
        json_response(['success' => true, 'message' => 'Deleted', 'data' => []]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
