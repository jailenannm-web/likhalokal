<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$hasBusinessCategory = db_column_exists('businesses', 'business_category');
$hasBusinessBranch = db_column_exists('businesses', 'branch');

if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    if ($id) {
        $stmt = db()->prepare(
            'SELECT b.*, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
        }
        if ($row['status'] !== 'approved' && (!is_logged_in() || current_user_role() !== 'admin')) {
            if (!is_logged_in() || (int) $row['user_id'] !== current_user_id()) {
                json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
            }
        }
        $row['avg_rating'] = business_avg_rating($id);
        json_response(['success' => true, 'message' => '', 'data' => $row]);
    }

    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $q = trim((string) ($_GET['q'] ?? ''));
    $sql = 'SELECT b.*, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE 1=1';
    $params = [];
    if (!is_logged_in() || current_user_role() !== 'admin') {
        $sql .= " AND b.status = 'approved'";
    } elseif ($status !== '') {
        $sql .= ' AND b.status = ?';
        $params[] = $status;
    }
    if ($type !== '') {
        $sql .= ' AND b.business_type = ?';
        $params[] = $type;
    }
    if ($q !== '') {
        $sql .= ' AND (b.business_name LIKE ? OR b.barangay LIKE ? OR b.address LIKE ? OR COALESCE(b.business_category, \'\') LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY b.business_name ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['avg_rating'] = business_avg_rating((int) $r['id']);
    }
    json_response(['success' => true, 'message' => '', 'data' => $rows]);
}

if ($method === 'POST') {
    $input = array_merge($_POST, json_decode(file_get_contents('php://input') ?: '[]', true) ?: []);
    $action = $input['action'] ?? $action;

    if ($action === 'create') {
        api_require_roles(['seller', 'local_user']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $stmt = db()->prepare('SELECT id FROM businesses WHERE user_id = ? AND status IN (\'pending\',\'approved\') LIMIT 1');
        $stmt->execute([current_user_id()]);
        if ($stmt->fetch()) {
            json_response(['success' => false, 'message' => 'You already have a business profile or pending application.', 'data' => []], 422);
        }
        $columns = 'user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, status, created_at, updated_at';
        $marks = '?,?,?,?,?,?,?,?,?,?,?,?,' . "'pending',NOW(),NOW()";
        $params = [
            current_user_id(),
            trim((string) ($input['business_name'] ?? '')),
            (string) ($input['business_type'] ?? 'pasalubong'),
            trim((string) ($input['description'] ?? '')),
            trim((string) ($input['contact_number'] ?? '')),
            trim((string) ($input['email'] ?? '')),
            trim((string) ($input['address'] ?? '')),
            trim((string) ($input['barangay'] ?? '')),
            $input['latitude'] !== '' && $input['latitude'] !== null ? (float) $input['latitude'] : null,
            $input['longitude'] !== '' && $input['longitude'] !== null ? (float) $input['longitude'] : null,
            trim((string) ($input['operating_hours'] ?? '')),
            (string) ($input['accepted_payments'] ?? '[]'),
        ];
        if ($hasBusinessCategory) {
            $columns .= ', business_category';
            $marks .= ',?';
            $params[] = trim((string) ($input['business_category'] ?? ''));
        }
        if ($hasBusinessBranch) {
            $columns .= ', branch';
            $marks .= ',?';
            $params[] = trim((string) ($input['branch'] ?? '')) ?: null;
        }
        db()->prepare('INSERT INTO businesses (' . $columns . ') VALUES (' . $marks . ')')->execute($params);
        $bid = (int) db()->lastInsertId();
        log_activity(current_user_id(), 'business_create', 'Business application #' . $bid, $_SERVER['REMOTE_ADDR'] ?? null);
        json_response(['success' => true, 'message' => 'Application submitted', 'data' => ['id' => $bid]]);
    }

    if ($action === 'update') {
        api_require_roles(['seller', 'admin']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $bid = (int) ($input['id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? LIMIT 1');
        $stmt->execute([$bid]);
        $biz = $stmt->fetch();
        if (!$biz) {
            json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
        }
        if (current_user_role() === 'seller' && (int) $biz['user_id'] !== current_user_id()) {
            json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
        }
        $updates = 'business_name=?, business_type=?, description=?, contact_number=?, email=?, address=?, barangay=?, latitude=?, longitude=?, operating_hours=?, accepted_payments=?, promotional_note=?, updated_at=NOW()';
        $params = [
            trim((string) ($input['business_name'] ?? $biz['business_name'])),
            (string) ($input['business_type'] ?? $biz['business_type']),
            trim((string) ($input['description'] ?? $biz['description'])),
            trim((string) ($input['contact_number'] ?? $biz['contact_number'])),
            trim((string) ($input['email'] ?? $biz['email'])),
            trim((string) ($input['address'] ?? $biz['address'])),
            trim((string) ($input['barangay'] ?? $biz['barangay'])),
            $input['latitude'] !== '' && $input['latitude'] !== null ? (float) $input['latitude'] : $biz['latitude'],
            $input['longitude'] !== '' && $input['longitude'] !== null ? (float) $input['longitude'] : $biz['longitude'],
            trim((string) ($input['operating_hours'] ?? $biz['operating_hours'])),
            (string) ($input['accepted_payments'] ?? $biz['accepted_payments']),
            trim((string) ($input['promotional_note'] ?? $biz['promotional_note'] ?? '')),
        ];
        if ($hasBusinessCategory) {
            $updates .= ', business_category=?';
            $params[] = trim((string) ($input['business_category'] ?? $biz['business_category'] ?? ''));
        }
        if ($hasBusinessBranch) {
            $updates .= ', branch=?';
            $params[] = trim((string) ($input['branch'] ?? $biz['branch'] ?? '')) ?: null;
        }
        $params[] = $bid;
        db()->prepare('UPDATE businesses SET ' . $updates . ' WHERE id=?')->execute($params);
        json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $bid]]);
    }

    if (in_array($action, ['approve', 'reject', 'suspend'], true)) {
        api_require_roles(['admin']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $bid = (int) ($input['id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? LIMIT 1');
        $stmt->execute([$bid]);
        $biz = $stmt->fetch();
        if (!$biz) {
            json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
        }
        if ($action === 'approve') {
            $stmt = db()->prepare(
                "UPDATE businesses SET status='approved', approved_by=?, approved_at=NOW(), rejection_reason=NULL, updated_at=NOW() WHERE id=?"
            );
            $stmt->execute([current_user_id(), $bid]);
            db()->prepare("UPDATE users SET role='seller', updated_at=NOW() WHERE id=? AND role='local_user'")
                ->execute([(int) $biz['user_id']]);
            notify_user((int) $biz['user_id'], 'Business approved', 'Your business "' . $biz['business_name'] . '" is now live on LikhaLokal.', 'success');
        } elseif ($action === 'reject') {
            $reason = trim((string) ($input['rejection_reason'] ?? ''));
            $stmt = db()->prepare("UPDATE businesses SET status='rejected', rejection_reason=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$reason, $bid]);
            notify_user((int) $biz['user_id'], 'Business application update', 'Your application needs attention: ' . $reason, 'warning');
        } else {
            $stmt = db()->prepare("UPDATE businesses SET status='suspended', updated_at=NOW() WHERE id=?");
            $stmt->execute([$bid]);
        }
        log_activity(current_user_id(), 'business_' . $action, 'Business #' . $bid, $_SERVER['REMOTE_ADDR'] ?? null);
        json_response(['success' => true, 'message' => 'OK', 'data' => ['id' => $bid]]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
