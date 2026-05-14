<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $category = $_GET['category'] ?? '';
    $q = trim((string) ($_GET['q'] ?? ''));

    if ($id) {
        $stmt = db()->prepare('SELECT * FROM tourist_attractions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || ($row['status'] !== 'published' && (!is_logged_in() || current_user_role() !== 'admin'))) {
            json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
        }
        $row['avg_rating'] = attraction_avg_rating($id);
        json_response(['success' => true, 'message' => '', 'data' => $row]);
    }

    $sql = "SELECT * FROM tourist_attractions WHERE status = 'published'";
    $params = [];
    if ($category !== '') {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }
    if ($q !== '') {
        $sql .= ' AND (attraction_name LIKE ? OR description LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY attraction_name ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
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
            'INSERT INTO tourist_attractions (admin_id, attraction_name, category, description, history, travel_guide, entrance_fee, best_time_to_visit, address, latitude, longitude, image, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            current_user_id(),
            trim((string) ($input['attraction_name'] ?? '')),
            (string) ($input['category'] ?? 'other'),
            trim((string) ($input['description'] ?? '')),
            trim((string) ($input['history'] ?? '')),
            trim((string) ($input['travel_guide'] ?? '')),
            trim((string) ($input['entrance_fee'] ?? '')),
            trim((string) ($input['best_time_to_visit'] ?? '')),
            trim((string) ($input['address'] ?? '')),
            $input['latitude'] !== '' && $input['latitude'] !== null ? (float) $input['latitude'] : null,
            $input['longitude'] !== '' && $input['longitude'] !== null ? (float) $input['longitude'] : null,
            trim((string) ($input['image'] ?? '')),
            (string) ($input['status'] ?? 'published'),
        ]);
        json_response(['success' => true, 'message' => 'Created', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }
    if ($action === 'update') {
        $id = (int) ($input['id'] ?? 0);
        $stmt = db()->prepare(
            'UPDATE tourist_attractions SET attraction_name=?, category=?, description=?, history=?, travel_guide=?, entrance_fee=?, best_time_to_visit=?, address=?, latitude=?, longitude=?, image=?, status=?, updated_at=NOW() WHERE id=?'
        );
        $stmt->execute([
            trim((string) ($input['attraction_name'] ?? '')),
            (string) ($input['category'] ?? 'other'),
            trim((string) ($input['description'] ?? '')),
            trim((string) ($input['history'] ?? '')),
            trim((string) ($input['travel_guide'] ?? '')),
            trim((string) ($input['entrance_fee'] ?? '')),
            trim((string) ($input['best_time_to_visit'] ?? '')),
            trim((string) ($input['address'] ?? '')),
            $input['latitude'] !== '' && $input['latitude'] !== null ? (float) $input['latitude'] : null,
            $input['longitude'] !== '' && $input['longitude'] !== null ? (float) $input['longitude'] : null,
            trim((string) ($input['image'] ?? '')),
            (string) ($input['status'] ?? 'published'),
            $id,
        ]);
        json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $id]]);
    }
    if ($action === 'delete') {
        $id = (int) ($input['id'] ?? 0);
        db()->prepare('DELETE FROM tourist_attractions WHERE id = ?')->execute([$id]);
        json_response(['success' => true, 'message' => 'Deleted', 'data' => []]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
