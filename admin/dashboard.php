<?php

declare(strict_types=1);

$pageTitle = 'Admin Dashboard';
$activeAdmin = 'dash';
require_once __DIR__ . '/_init.php';

$counts = [
    'users' => (int) db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'],
    'local_users' => (int) db()->query("SELECT COUNT(*) c FROM users WHERE role='local_user'")->fetch()['c'],
    'sellers' => (int) db()->query("SELECT COUNT(*) c FROM users WHERE role='seller'")->fetch()['c'],
    'businesses_approved' => (int) db()->query("SELECT COUNT(*) c FROM businesses WHERE status='approved'")->fetch()['c'],
    'businesses_pending' => (int) db()->query("SELECT COUNT(*) c FROM businesses WHERE status='pending'")->fetch()['c'],
    'products' => (int) db()->query('SELECT COUNT(*) c FROM products')->fetch()['c'],
    'attractions' => (int) db()->query('SELECT COUNT(*) c FROM tourist_attractions')->fetch()['c'],
    'events' => (int) db()->query('SELECT COUNT(*) c FROM events')->fetch()['c'],
    'announcements' => (int) db()->query('SELECT COUNT(*) c FROM announcements')->fetch()['c'],
    'reviews' => (int) db()->query('SELECT COUNT(*) c FROM reviews')->fetch()['c'],
    'reviews_pending' => (int) db()->query("SELECT COUNT(*) c FROM reviews WHERE status='pending'")->fetch()['c'],
    'messages' => (int) db()->query('SELECT COUNT(*) c FROM messages')->fetch()['c'],
];

$recentActivity = db()->query(
    'SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT 8'
)->fetchAll();

if (empty($recentActivity)) {
    $recentActivity = [];
    $bizNew = db()->query(
        "SELECT b.business_name AS label, b.created_at, 'New business' AS kind FROM businesses b ORDER BY b.created_at DESC LIMIT 3"
    )->fetchAll();
    foreach ($bizNew as $row) {
        $recentActivity[] = ['action' => $row['kind'], 'description' => $row['label'], 'full_name' => 'System', 'created_at' => $row['created_at']];
    }
    $revNew = db()->query(
        "SELECT CONCAT('Review ', r.rating, '★') AS action, b.business_name AS description, u.full_name, r.created_at
         FROM reviews r LEFT JOIN businesses b ON b.id = r.business_id LEFT JOIN users u ON u.id = r.user_id
         ORDER BY r.created_at DESC LIMIT 3"
    )->fetchAll();
    foreach ($revNew as $row) {
        $recentActivity[] = $row;
    }
}

$pendingBusinesses = db()->query(
    "SELECT b.*, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.status='pending' ORDER BY b.created_at DESC LIMIT 5"
)->fetchAll();

$pendingReviews = db()->query(
    "SELECT r.*, u.full_name AS reviewer_name, b.business_name FROM reviews r
     JOIN users u ON u.id = r.user_id LEFT JOIN businesses b ON b.id = r.business_id
     WHERE r.status='pending' ORDER BY r.created_at DESC LIMIT 5"
)->fetchAll();

$recentMessages = db()->query(
    'SELECT m.*, u.full_name AS sender_name, b.business_name FROM messages m
     JOIN users u ON u.id = m.sender_id LEFT JOIN businesses b ON b.id = m.business_id
     ORDER BY m.created_at DESC LIMIT 5'
)->fetchAll();

$draftEvents = db()->query(
    "SELECT id, title, event_date FROM events WHERE status != 'published' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

$draftAttractions = db()->query(
    "SELECT id, attraction_name FROM tourist_attractions WHERE status != 'published' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

$adminUser = current_user();
$adminName = $adminUser['full_name'] ?? $_SESSION['user_name'] ?? 'Admin';
$adminAvatar = profile_avatar_url($adminName, $adminUser['profile_image'] ?? null);

require __DIR__ . '/partials/layout-start.php';
?>

<div class="lk-dash-hero mb-4">
    <div class="row align-items-center g-4 position-relative" style="z-index: 1;">
        <div class="col-md-auto text-center text-md-start">
            <div class="avatar-ring mx-auto mx-md-0">
                <img src="<?= e($adminAvatar) ?>" alt="Admin">
            </div>
        </div>
        <div class="col">
            <p class="text-warning small text-uppercase fw-bold mb-1" style="letter-spacing: 0.08em;">Administration</p>
            <h1 class="h3 fw-bold mb-2">Welcome, <?= e($adminName) ?>!</h1>
            <p class="mb-0 opacity-75">Manage Vinzons tourism content, local businesses, and community activity.</p>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="<?= e(BASE_URL) ?>index.php" class="btn btn-sm btn-lk-orange">View public website</a>
                <a href="<?= e(ADMIN_URL) ?>business-applications.php" class="btn btn-sm btn-lk-outline-white">Review applications</a>
                <a href="<?= e(ADMIN_URL) ?>reviews.php" class="btn btn-sm btn-outline-light">Moderate reviews</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label' => 'Total users', 'val' => $counts['users'], 'icon' => 'bi-people-fill', 'link' => ADMIN_URL . 'users.php'],
        ['label' => 'Local users', 'val' => $counts['local_users'], 'icon' => 'bi-person-heart', 'link' => ADMIN_URL . 'users.php'],
        ['label' => 'Sellers', 'val' => $counts['sellers'], 'icon' => 'bi-shop', 'link' => ADMIN_URL . 'users.php'],
        ['label' => 'Approved businesses', 'val' => $counts['businesses_approved'], 'icon' => 'bi-building-check', 'link' => ADMIN_URL . 'businesses.php?tab=approved'],
        ['label' => 'Pending businesses', 'val' => $counts['businesses_pending'], 'icon' => 'bi-hourglass-split', 'link' => ADMIN_URL . 'business-applications.php'],
        ['label' => 'Products / services', 'val' => $counts['products'], 'icon' => 'bi-box-seam', 'link' => ADMIN_URL . 'businesses.php'],
        ['label' => 'Attractions', 'val' => $counts['attractions'], 'icon' => 'bi-geo-alt', 'link' => ADMIN_URL . 'attractions.php'],
        ['label' => 'Events', 'val' => $counts['events'], 'icon' => 'bi-calendar-event', 'link' => ADMIN_URL . 'events.php'],
        ['label' => 'Announcements', 'val' => $counts['announcements'], 'icon' => 'bi-megaphone', 'link' => ADMIN_URL . 'announcements.php'],
        ['label' => 'Reviews', 'val' => $counts['reviews'], 'icon' => 'bi-star', 'link' => ADMIN_URL . 'reviews.php'],
        ['label' => 'Pending reviews', 'val' => $counts['reviews_pending'], 'icon' => 'bi-star-half', 'link' => ADMIN_URL . 'reviews.php'],
        ['label' => 'Messages', 'val' => $counts['messages'], 'icon' => 'bi-chat-dots', 'link' => ADMIN_URL . 'messages.php'],
    ];
    foreach ($statCards as $card):
    ?>
    <div class="col-sm-6 col-md-4 col-xl-3">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(243,146,0,0.15); color: var(--lk-orange);"><i class="bi <?= e($card['icon']) ?>"></i></div>
            <div class="text-muted small"><?= e($card['label']) ?></div>
            <div class="stat-value"><?= (int) $card['val'] ?></div>
            <a href="<?= e($card['link']) ?>" class="small fw-semibold text-decoration-none">Manage →</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header"><h2><i class="bi bi-activity me-2 text-warning"></i>Recent activity</h2></div>
            <ul class="list-group list-group-flush">
                <?php if (empty($recentActivity)): ?>
                    <li class="list-group-item text-muted">No recent activity logged.</li>
                <?php else: ?>
                    <?php foreach ($recentActivity as $log): ?>
                    <li class="list-group-item px-3 py-2">
                        <strong><?= e($log['action'] ?? 'Activity') ?></strong>
                        <?= e(str_limit($log['description'] ?? '', 80)) ?>
                        <span class="text-muted small d-block"><?= e($log['full_name'] ?? 'System') ?> · <?= e(format_datetime_short($log['created_at'] ?? '')) ?></span>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header"><h2><i class="bi bi-list-check me-2 text-warning"></i>Pending tasks</h2></div>
            <div class="p-3">
                <p class="mb-2"><strong><?= count($pendingBusinesses) ?></strong> business application(s) awaiting review.
                    <a href="<?= e(ADMIN_URL) ?>business-applications.php">Review</a></p>
                <p class="mb-2"><strong><?= (int) $counts['reviews_pending'] ?></strong> review(s) awaiting moderation.
                    <a href="<?= e(ADMIN_URL) ?>reviews.php">Moderate</a></p>
                <?php if ($draftEvents): ?>
                    <p class="mb-2 small text-muted">Unpublished events: <?= e(implode(', ', array_column($draftEvents, 'title'))) ?></p>
                <?php endif; ?>
                <?php if ($draftAttractions): ?>
                    <p class="mb-0 small text-muted">Unpublished attractions: <?= e(implode(', ', array_column($draftAttractions, 'attraction_name'))) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="lk-panel">
            <div class="lk-panel-header">
                <h2><i class="bi bi-building me-2"></i>Pending businesses</h2>
                <a href="<?= e(ADMIN_URL) ?>business-applications.php" class="btn btn-sm btn-lk-orange">View all</a>
            </div>
            <?php if (empty($pendingBusinesses)): ?>
                <div class="p-4 text-muted text-center">No pending applications.</div>
            <?php else: ?>
                <?php foreach ($pendingBusinesses as $b): ?>
                <div class="lk-msg-row d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= e($b['business_name']) ?></strong>
                        <span class="small text-muted d-block"><?= e($b['owner_name'] ?? '') ?></span>
                    </div>
                    <a href="<?= e(ADMIN_URL) ?>businesses.php?tab=pending" class="btn btn-sm btn-outline-primary">Review</a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="lk-panel">
            <div class="lk-panel-header">
                <h2><i class="bi bi-star me-2"></i>Pending reviews</h2>
                <a href="<?= e(ADMIN_URL) ?>reviews.php" class="btn btn-sm btn-lk-orange">View all</a>
            </div>
            <?php if (empty($pendingReviews)): ?>
                <div class="p-4 text-muted text-center">No pending reviews.</div>
            <?php else: ?>
                <?php foreach ($pendingReviews as $r): ?>
                <div class="lk-msg-row">
                    <strong><?= e($r['reviewer_name']) ?></strong> · <?= e($r['business_name'] ?? 'Attraction') ?>
                    <div class="mt-1"><?= render_star_rating((int) $r['rating']) ?></div>
                    <p class="small text-muted mb-0"><?= e(str_limit($r['comment'] ?? '', 80)) ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="lk-panel mb-4">
    <div class="lk-panel-header">
        <h2><i class="bi bi-chat-dots me-2"></i>Latest messages</h2>
        <a href="<?= e(ADMIN_URL) ?>messages.php" class="btn btn-sm btn-lk-orange">View all</a>
    </div>
    <?php if (empty($recentMessages)): ?>
        <div class="p-4 text-muted text-center">No messages yet.</div>
    <?php else: ?>
        <?php foreach ($recentMessages as $m): ?>
        <div class="lk-msg-row">
            <strong><?= e($m['sender_name']) ?></strong>
            <?php if ($m['business_name']): ?> · <?= e($m['business_name']) ?><?php endif; ?>
            <p class="small text-muted mb-0"><?= e(str_limit($m['message_content'] ?? '', 90)) ?></p>
            <span class="small text-muted"><?= e(format_datetime_short($m['created_at'] ?? '')) ?></span>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="lk-panel">
    <div class="lk-panel-header"><h2><i class="bi bi-lightning-charge me-2"></i>Quick actions</h2></div>
    <div class="p-3 row g-2">
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(BASE_URL) ?>index.php"><i class="bi bi-globe"></i> View public website</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(ADMIN_URL) ?>attractions.php"><i class="bi bi-geo-alt"></i> Add tourist attraction</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(ADMIN_URL) ?>announcements.php"><i class="bi bi-megaphone"></i> Post announcement</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(ADMIN_URL) ?>events.php"><i class="bi bi-calendar-plus"></i> Post event</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(ADMIN_URL) ?>business-applications.php"><i class="bi bi-file-earmark-check"></i> Review applications</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(ADMIN_URL) ?>users.php"><i class="bi bi-people"></i> Manage users</a></div>
    </div>
</div>

<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
