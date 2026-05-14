<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

api_require_login();

$role = $_GET['role'] ?? current_user_role();

if ($role === 'admin' && current_user_role() !== 'admin') {
    json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
}
if ($role === 'seller' && current_user_role() !== 'seller') {
    json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
}

if ($role === 'admin') {
    $counts = [
        'users' => (int) db()->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'],
        'sellers' => (int) db()->query("SELECT COUNT(*) AS c FROM users WHERE role='seller'")->fetch()['c'],
        'businesses_approved' => (int) db()->query("SELECT COUNT(*) AS c FROM businesses WHERE status='approved'")->fetch()['c'],
        'businesses_pending' => (int) db()->query("SELECT COUNT(*) AS c FROM businesses WHERE status='pending'")->fetch()['c'],
        'products' => (int) db()->query('SELECT COUNT(*) AS c FROM products')->fetch()['c'],
        'attractions' => (int) db()->query('SELECT COUNT(*) AS c FROM tourist_attractions')->fetch()['c'],
        'events' => (int) db()->query('SELECT COUNT(*) AS c FROM events')->fetch()['c'],
        'reviews' => (int) db()->query('SELECT COUNT(*) AS c FROM reviews')->fetch()['c'],
        'messages' => (int) db()->query('SELECT COUNT(*) AS c FROM messages')->fetch()['c'],
        'inquiries' => (int) db()->query('SELECT COUNT(*) AS c FROM inquiries')->fetch()['c'],
    ];
    $logs = db()->query('SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 15')->fetchAll();
    json_response(['success' => true, 'message' => '', 'data' => ['counts' => $counts, 'activity' => $logs]]);
}

if ($role === 'seller') {
    $uid = current_user_id();
    $stmt = db()->prepare(
        "SELECT * FROM businesses WHERE user_id = ? ORDER BY CASE status WHEN 'approved' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END, id ASC LIMIT 1"
    );
    $stmt->execute([$uid]);
    $biz = $stmt->fetch();
    if (!$biz) {
        json_response(['success' => true, 'message' => '', 'data' => ['business' => null, 'stats' => []]]);
    }
    $bid = (int) $biz['id'];
    $pstmt = db()->prepare('SELECT COUNT(*) AS c FROM products WHERE business_id = ?');
    $pstmt->execute([$bid]);
    $stats['products'] = (int) $pstmt->fetch()['c'];
    $mstmt = db()->prepare(
        'SELECT COUNT(*) AS c FROM messages m WHERE m.business_id = ? AND (m.sender_id = ? OR m.receiver_id = ?)'
    );
    $mstmt->execute([$bid, $uid, $uid]);
    $stats['messages'] = (int) $mstmt->fetch()['c'];
    $rstmt = db()->prepare("SELECT COUNT(*) AS c FROM reviews WHERE business_id = ? AND status = 'approved'");
    $rstmt->execute([$bid]);
    $stats['reviews'] = (int) $rstmt->fetch()['c'];
    $stats['avg_rating'] = business_avg_rating($bid);
    $inq = db()->prepare('SELECT * FROM inquiries WHERE business_id = ? ORDER BY created_at DESC LIMIT 5');
    $inq->execute([$bid]);
    json_response(['success' => true, 'message' => '', 'data' => ['business' => $biz, 'stats' => $stats, 'inquiries' => $inq->fetchAll()]]);
}

json_response(['success' => false, 'message' => 'Unsupported role', 'data' => []], 400);
