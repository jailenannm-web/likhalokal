<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($type === 'business' && $id > 0) {
    $stmt = db()->prepare('SELECT id, business_name, address, barangay, latitude, longitude FROM businesses WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
    }
    $lat = $row['latitude'];
    $lng = $row['longitude'];
    $parts = array_filter([$row['address'] ?? '', $row['barangay'] ?? '', 'Vinzons, Camarines Norte']);
    $address = implode(', ', $parts);
    json_response([
        'success' => true,
        'message' => '',
        'data' => [
            'name' => $row['business_name'],
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $address,
            'directions_url' => ($lat && $lng)
                ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode((string) $lat . ',' . (string) $lng)
                : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address),
        ],
    ]);
}

if ($type === 'attraction' && $id > 0) {
    $stmt = db()->prepare(
        'SELECT id, attraction_name, address, latitude, longitude FROM tourist_attractions WHERE id = ? AND status = \'published\' LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['success' => false, 'message' => 'Not found', 'data' => []], 404);
    }
    $lat = $row['latitude'];
    $lng = $row['longitude'];
    $address = (string) ($row['address'] ?? '');
    json_response([
        'success' => true,
        'message' => '',
        'data' => [
            'name' => $row['attraction_name'],
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $address,
            'directions_url' => ($lat && $lng)
                ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode((string) $lat . ',' . (string) $lng)
                : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address ?: $row['attraction_name']),
        ],
    ]);
}

json_response(['success' => false, 'message' => 'Invalid type or id', 'data' => []], 400);
