<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$searchReturn = isset($_GET['return']) && is_safe_return_url((string) $_GET['return'])
    ? (string) $_GET['return']
    : current_request_return_url();
if (strlen($q) < 2) {
    json_response(['success' => true, 'message' => '', 'data' => ['groups' => []]]);
}

$like = '%' . $q . '%';
$pdo = db();
$groups = [];

$stmt = $pdo->prepare(
    "SELECT id, business_name AS title, business_type AS meta
     FROM businesses WHERE status = 'approved'
       AND (business_name LIKE ? OR description LIKE ? OR address LIKE ?)
     ORDER BY business_name ASC LIMIT 6"
);
$stmt->execute([$like, $like, $like]);
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $items[] = [
        'label' => $row['title'],
        'meta' => business_type_label($row['meta']),
        'url' => vendor_profile_url((int) $row['id'], $searchReturn),
    ];
}
if ($items) {
    $groups[] = ['name' => 'Businesses', 'items' => $items];
}

$stmt = $pdo->prepare(
    "SELECT p.id, p.product_name AS title, b.business_name AS meta, p.business_id
     FROM products p
     INNER JOIN businesses b ON b.id = p.business_id AND b.status = 'approved'
     WHERE p.availability = 'available'
       AND (p.product_name LIKE ? OR p.description LIKE ?)
     ORDER BY p.product_name ASC LIMIT 6"
);
$stmt->execute([$like, $like]);
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $items[] = [
        'label' => $row['title'],
        'meta' => $row['meta'],
        'url' => vendor_profile_url((int) $row['business_id'], $searchReturn),
    ];
}
if ($items) {
    $groups[] = ['name' => 'Marketplace', 'items' => $items];
}

$stmt = $pdo->prepare(
    "SELECT id, attraction_name AS title, category AS meta
     FROM tourist_attractions WHERE status = 'published'
       AND (attraction_name LIKE ? OR description LIKE ?)
     ORDER BY attraction_name ASC LIMIT 5"
);
$stmt->execute([$like, $like]);
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $items[] = [
        'label' => $row['title'],
        'meta' => ucwords(str_replace('_', ' ', $row['meta'])),
        'url' => BASE_URL . 'attraction-detail.php?id=' . (int) $row['id'],
    ];
}
if ($items) {
    $groups[] = ['name' => 'Attractions', 'items' => $items];
}

$stmt = $pdo->prepare(
    "SELECT id, title, event_date AS meta FROM events
     WHERE status = 'published' AND (title LIKE ? OR description LIKE ?)
     ORDER BY event_date DESC LIMIT 4"
);
$stmt->execute([$like, $like]);
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $items[] = [
        'label' => $row['title'],
        'meta' => (string) $row['meta'],
        'url' => BASE_URL . 'events.php',
    ];
}
if ($items) {
    $groups[] = ['name' => 'Events', 'items' => $items];
}

$stmt = $pdo->prepare(
    "SELECT id, title, created_at AS meta FROM announcements
     WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)
     ORDER BY created_at DESC LIMIT 4"
);
$stmt->execute([$like, $like]);
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $items[] = [
        'label' => $row['title'],
        'meta' => format_datetime_short((string) $row['meta']),
        'url' => BASE_URL . 'index.php',
    ];
}
if ($items) {
    $groups[] = ['name' => 'Announcements', 'items' => $items];
}

$stmt = $pdo->prepare(
    "SELECT id, title, category AS meta FROM cultural_information
     WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)
     ORDER BY title ASC LIMIT 4"
);
$stmt->execute([$like, $like]);
$items = [];
foreach ($stmt->fetchAll() as $row) {
    $items[] = [
        'label' => $row['title'],
        'meta' => ucwords(str_replace('_', ' ', (string) $row['meta'])),
        'url' => BASE_URL . 'cultural-info.php',
    ];
}
if ($items) {
    $groups[] = ['name' => 'Cultural Information', 'items' => $items];
}

json_response(['success' => true, 'message' => '', 'data' => ['groups' => $groups, 'query' => $q]]);
