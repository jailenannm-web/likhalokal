<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$method = $_SERVER['REQUEST_METHOD'];
$hasProductType = db_column_exists('products', 'product_type');
$validCategories = ['local_delicacy', 'handicraft', 'fresh_produce', 'service', 'tour_package', 'food', 'other'];
$validTypes = ['product', 'service', 'tour_package', 'accommodation', 'food', 'other'];
$validAvailability = ['available', 'unavailable'];

if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $businessId = isset($_GET['business_id']) ? (int) $_GET['business_id'] : null;
    $category = $_GET['category'] ?? '';
    $sort = $_GET['sort'] ?? 'latest';
    $q = trim((string) ($_GET['q'] ?? ''));

    if ($id) {
        $stmt = db()->prepare(
            'SELECT p.*, b.business_name, b.status AS business_status FROM products p JOIN businesses b ON b.id = p.business_id WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || ($row['business_status'] !== 'approved' && (!is_logged_in() || current_user_role() !== 'admin'))) {
            if (!is_logged_in() || !seller_owns_product(current_user_id(), $id)) {
                json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
            }
        }
        json_response(['success' => true, 'message' => '', 'data' => $row]);
    }

    $sql = 'SELECT p.*, b.business_name, b.status AS business_status FROM products p JOIN businesses b ON b.id = p.business_id WHERE b.status = \'approved\'';
    $params = [];
    if ($businessId) {
        $sql .= ' AND p.business_id = ?';
        $params[] = $businessId;
    }
    if ($category !== '') {
        $sql .= ' AND p.category = ?';
        $params[] = $category;
    }
    if ($q !== '') {
        $sql .= ' AND (p.product_name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    if ($sort === 'price_asc') {
        $sql .= ' ORDER BY p.price ASC';
    } elseif ($sort === 'price_desc') {
        $sql .= ' ORDER BY p.price DESC';
    } else {
        $sql .= ' ORDER BY p.created_at DESC';
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    json_response(['success' => true, 'message' => '', 'data' => $stmt->fetchAll()]);
}

function seller_owns_product(int $userId, int $productId): bool
{
    $stmt = db()->prepare(
        'SELECT 1 FROM products p JOIN businesses b ON b.id = p.business_id WHERE p.id = ? AND b.user_id = ? LIMIT 1'
    );
    $stmt->execute([$productId, $userId]);
    return (bool) $stmt->fetch();
}

function seller_product_by_api_id(int $productId): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function seller_business_id(int $userId): ?int
{
    $stmt = db()->prepare(
        "SELECT id FROM businesses WHERE user_id = ? AND status = 'approved' ORDER BY id ASC LIMIT 1"
    );
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

if ($method === 'POST') {
    $input = array_merge($_POST, json_decode(file_get_contents('php://input') ?: '[]', true) ?: []);
    $action = $input['action'] ?? '';

    if ($action === 'create') {
        api_require_roles(['seller']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $bid = seller_business_id(current_user_id());
        if (!$bid) {
            json_response(['success' => false, 'message' => 'You need an approved business first.', 'data' => []], 403);
        }
        $name = trim((string) ($input['product_name'] ?? ''));
        $category = in_array(($input['category'] ?? ''), $validCategories, true) ? (string) $input['category'] : 'other';
        $type = in_array(($input['product_type'] ?? ''), $validTypes, true) ? (string) $input['product_type'] : 'product';
        $availability = in_array(($input['availability'] ?? ''), $validAvailability, true) ? (string) $input['availability'] : 'available';
        if ($name === '' || (float) ($input['price'] ?? 0) < 0) {
            json_response(['success' => false, 'message' => 'Valid name and non-negative price are required.', 'data' => []], 422);
        }
        $columns = 'business_id, product_name, category, description, price, image, availability, is_featured';
        $marks = '?,?,?,?,?,?,?,?';
        $params = [
            $bid,
            $name,
            $category,
            trim((string) ($input['description'] ?? '')),
            (float) ($input['price'] ?? 0),
            trim((string) ($input['image'] ?? '')),
            $availability,
            !empty($input['is_featured']) ? 1 : 0,
        ];
        if ($hasProductType) {
            $columns .= ', product_type';
            $marks .= ',?';
            $params[] = $type;
        }
        $stmt = db()->prepare(
            'INSERT INTO products (' . $columns . ', created_at, updated_at)
             VALUES (' . $marks . ',NOW(),NOW())'
        );
        $stmt->execute($params);
        json_response(['success' => true, 'message' => 'Product created', 'data' => ['id' => (int) db()->lastInsertId()]]);
    }

    if ($action === 'update') {
        api_require_roles(['seller']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $pid = (int) ($input['id'] ?? 0);
        if (!seller_owns_product(current_user_id(), $pid)) {
            json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
        }
        $current = seller_product_by_api_id($pid);
        $name = trim((string) ($input['product_name'] ?? ''));
        $category = in_array(($input['category'] ?? ''), $validCategories, true) ? (string) $input['category'] : 'other';
        $type = in_array(($input['product_type'] ?? ''), $validTypes, true) ? (string) $input['product_type'] : 'product';
        $availability = in_array(($input['availability'] ?? ''), $validAvailability, true) ? (string) $input['availability'] : 'available';
        $image = array_key_exists('image', $input) && trim((string) $input['image']) !== ''
            ? trim((string) $input['image'])
            : (string) ($current['image'] ?? '');
        if ($name === '' || (float) ($input['price'] ?? 0) < 0) {
            json_response(['success' => false, 'message' => 'Valid name and non-negative price are required.', 'data' => []], 422);
        }
        $sql = 'UPDATE products SET product_name=?, category=?, description=?, price=?, image=?, availability=?, is_featured=?';
        $params = [
            $name,
            $category,
            trim((string) ($input['description'] ?? '')),
            (float) ($input['price'] ?? 0),
            $image,
            $availability,
            !empty($input['is_featured']) ? 1 : 0,
        ];
        if ($hasProductType) {
            $sql .= ', product_type=?';
            $params[] = $type;
        }
        $sql .= ', updated_at=NOW() WHERE id=?';
        $params[] = $pid;
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        json_response(['success' => true, 'message' => 'Updated', 'data' => ['id' => $pid]]);
    }

    if ($action === 'delete') {
        api_require_roles(['seller']);
        if (!verify_csrf($input['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid CSRF', 'data' => []], 403);
        }
        $pid = (int) ($input['id'] ?? 0);
        if (!seller_owns_product(current_user_id(), $pid)) {
            json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
        }
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$pid]);
        json_response(['success' => true, 'message' => 'Deleted', 'data' => []]);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed', 'data' => []], 405);
