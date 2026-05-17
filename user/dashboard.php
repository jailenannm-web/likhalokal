<?php

declare(strict_types=1);

$pageTitle = 'My dashboard';
$activeUser = 'dash';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$unread = unread_messages_count($uid);

$stmt = db()->prepare('SELECT full_name, email, contact_number, profile_image FROM users WHERE id = ?');
$stmt->execute([$uid]);
$profile = $stmt->fetch() ?: [];

$reviewCountStmt = db()->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$reviewCountStmt->execute([$uid]);
$reviewTotal = (int) $reviewCountStmt->fetchColumn();

$messages = user_message_conversations($uid);
$recentMessages = array_slice($messages, 0, 5);

$recentReviews = db()->prepare(
    'SELECT r.*, b.business_name, ta.attraction_name
     FROM reviews r
     LEFT JOIN businesses b ON b.id = r.business_id
     LEFT JOIN tourist_attractions ta ON ta.id = r.attraction_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC LIMIT 5'
);
$recentReviews->execute([$uid]);
$reviews = $recentReviews->fetchAll();

$announcements = db()->query(
    "SELECT title, content, created_at FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

$events = db()->query(
    "SELECT id, title, event_date, location FROM events WHERE status = 'published' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3"
)->fetchAll();

$completion = profile_completion_percent($profile);
$avatar = profile_avatar_url($profile['full_name'] ?? null, $profile['profile_image'] ?? null);

require __DIR__ . '/partials/layout-start.php';
?>

<div class="lk-user-hero mb-4">
    <div class="row align-items-center g-4 position-relative" style="z-index: 1;">
        <div class="col-md-auto text-center text-md-start">
            <div class="avatar-ring mx-auto mx-md-0">
                <img src="<?= e($avatar) ?>" alt="Profile">
            </div>
        </div>
        <div class="col">
            <p class="text-warning small text-uppercase fw-bold mb-1 mb-md-0" style="letter-spacing: 0.08em;">Welcome back</p>
            <h1 class="h3 fw-bold mb-2"><?= e($profile['full_name'] ?? $_SESSION['user_name'] ?? 'Explorer') ?>!</h1>
            <p class="mb-0 opacity-75">Discover Vinzons culture, support local businesses, and stay connected with sellers.</p>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="<?= e(BASE_URL) ?>tourism.php" class="btn btn-sm btn-lk-orange">Explore Tourism</a>
                <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-sm btn-lk-outline-white">Browse Marketplace</a>
                <a href="<?= e(BASE_URL) ?>local-business.php" class="btn btn-sm btn-outline-light">View Local Businesses</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(243,146,0,0.15); color: var(--lk-orange);"><i class="bi bi-envelope-fill"></i></div>
            <div class="text-muted small">Unread messages</div>
            <div class="stat-value"><?= (int) $unread ?></div>
            <a href="<?= e(USER_DASH_URL) ?>messages.php" class="small fw-semibold text-decoration-none">Open inbox →</a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(2,62,138,0.12); color: var(--lk-navy);"><i class="bi bi-star-fill"></i></div>
            <div class="text-muted small">My reviews</div>
            <div class="stat-value"><?= $reviewTotal ?></div>
            <a href="<?= e(USER_DASH_URL) ?>reviews.php" class="small fw-semibold text-decoration-none">View all →</a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(27,67,50,0.12); color: #1b4332;"><i class="bi bi-chat-left-text-fill"></i></div>
            <div class="text-muted small">Conversations</div>
            <div class="stat-value"><?= count($messages) ?></div>
            <a href="<?= e(USER_DASH_URL) ?>messages.php" class="small fw-semibold text-decoration-none">Message sellers →</a>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(0,31,63,0.08); color: var(--lk-navy);"><i class="bi bi-person-check-fill"></i></div>
            <div class="text-muted small">Profile completion</div>
            <div class="stat-value"><?= $completion ?>%</div>
            <a href="<?= e(USER_DASH_URL) ?>profile.php" class="small fw-semibold text-decoration-none">Update profile →</a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header">
                <h2><i class="bi bi-chat-dots me-2 text-warning"></i>Recent messages</h2>
                <a href="<?= e(USER_DASH_URL) ?>messages.php" class="btn btn-sm btn-lk-orange">View all</a>
            </div>
            <?php if (empty($recentMessages)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-2">No messages yet.</p>
                    <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-sm btn-lk-orange">Browse marketplace</a>
                </div>
            <?php else: ?>
                <?php foreach ($recentMessages as $m): ?>
                <a class="lk-msg-row d-flex justify-content-between align-items-start gap-2" href="<?= e(USER_DASH_URL) ?>messages.php?business_id=<?= (int) $m['business_id'] ?>">
                    <div>
                        <strong class="d-block"><?= e($m['business_name'] ?? 'Business') ?></strong>
                        <span class="small text-muted"><?= e(str_limit($m['last_message'] ?? '', 70)) ?></span>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <?php if ((int) ($m['unread_count'] ?? 0) > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= (int) $m['unread_count'] ?></span>
                        <?php endif; ?>
                        <div class="small text-muted"><?= e(format_datetime_short($m['last_at'] ?? '')) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header">
                <h2><i class="bi bi-star me-2 text-warning"></i>Recent reviews</h2>
                <a href="<?= e(USER_DASH_URL) ?>reviews.php" class="btn btn-sm btn-lk-orange">View all</a>
            </div>
            <?php if (empty($reviews)): ?>
                <div class="p-4 text-center text-muted">
                    <p class="mb-2">You have not submitted any reviews yet.</p>
                    <a href="<?= e(BASE_URL) ?>local-business.php" class="btn btn-sm btn-lk-orange">Find businesses</a>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                <div class="lk-msg-row">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong><?= e($r['business_name'] ?? $r['attraction_name'] ?? 'Review') ?></strong>
                        <span class="badge bg-<?= review_status_badge_class($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span>
                    </div>
                    <div class="mt-1"><?= render_star_rating((int) $r['rating']) ?></div>
                    <p class="small text-muted mb-0 mt-1"><?= e(str_limit($r['comment'] ?? '', 90)) ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($announcements) || !empty($events)): ?>
<div class="row g-4 mb-4">
    <?php if (!empty($announcements)): ?>
    <div class="col-md-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header"><h2><i class="bi bi-megaphone me-2"></i>Announcements</h2></div>
            <div class="p-3">
                <?php foreach ($announcements as $a): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <strong class="d-block text-dark"><?= e($a['title']) ?></strong>
                    <p class="small text-muted mb-0"><?= e(str_limit($a['content'], 120)) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($events)): ?>
    <div class="col-md-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header"><h2><i class="bi bi-calendar-event me-2"></i>Upcoming events</h2></div>
            <div class="p-3">
                <?php foreach ($events as $ev): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <strong class="d-block"><a href="<?= e(BASE_URL) ?>events.php#event-<?= (int) $ev['id'] ?>" class="text-decoration-none"><?= e($ev['title']) ?></a></strong>
                    <p class="small text-muted mb-0"><i class="bi bi-calendar3 me-1"></i><?= e($ev['event_date']) ?> · <?= e($ev['location'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
                <a href="<?= e(BASE_URL) ?>events.php" class="small fw-semibold">See all events →</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="lk-panel">
    <div class="lk-panel-header"><h2><i class="bi bi-compass me-2"></i>Quick links</h2></div>
    <div class="p-3 row g-2">
        <div class="col-md-6 col-lg-3"><a class="lk-quick-link" href="<?= e(BASE_URL) ?>tourism.php"><i class="bi bi-geo-alt-fill"></i> Tourism</a></div>
        <div class="col-md-6 col-lg-3"><a class="lk-quick-link" href="<?= e(BASE_URL) ?>products.php"><i class="bi bi-bag-fill"></i> Marketplace</a></div>
        <div class="col-md-6 col-lg-3"><a class="lk-quick-link" href="<?= e(BASE_URL) ?>local-business.php"><i class="bi bi-shop"></i> Local Business</a></div>
        <div class="col-md-6 col-lg-3"><a class="lk-quick-link" href="<?= e(BASE_URL) ?>tourism.php"><i class="bi bi-map-fill"></i> Map &amp; places</a></div>
    </div>
</div>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
