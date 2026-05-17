<?php

declare(strict_types=1);

$pageTitle = 'Seller dashboard';
$activeSeller = 'dash';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$userStmt = db()->prepare('SELECT full_name, profile_image FROM users WHERE id = ?');
$userStmt->execute([$uid]);
$sellerUser = $userStmt->fetch() ?: [];

$stmt = db()->prepare(
    "SELECT * FROM businesses WHERE user_id = ? ORDER BY CASE status WHEN 'approved' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END, id ASC LIMIT 1"
);
$stmt->execute([$uid]);
$biz = $stmt->fetch();

$stats = ['products' => 0, 'messages' => 0, 'reviews' => 0, 'avg' => 0, 'unread' => 0];
$recentThreads = [];
$recentReviews = [];

if ($biz) {
    $bid = (int) $biz['id'];
    $p = db()->prepare('SELECT COUNT(*) c FROM products WHERE business_id=?');
    $p->execute([$bid]);
    $stats['products'] = (int) $p->fetch()['c'];

    $m = db()->prepare('SELECT COUNT(*) c FROM messages WHERE business_id=?');
    $m->execute([$bid]);
    $stats['messages'] = (int) $m->fetch()['c'];

    $u = db()->prepare('SELECT COUNT(*) c FROM messages WHERE business_id=? AND receiver_id=? AND is_read=0');
    $u->execute([$bid, $uid]);
    $stats['unread'] = (int) $u->fetch()['c'];

    $r = db()->prepare("SELECT COUNT(*) c FROM reviews WHERE business_id=? AND status='approved'");
    $r->execute([$bid]);
    $stats['reviews'] = (int) $r->fetch()['c'];
    $stats['avg'] = business_avg_rating($bid);

    $recentThreads = seller_message_threads($uid, $bid);
    $recentThreads = array_slice($recentThreads, 0, 5);

    $revStmt = db()->prepare(
        "SELECT r.*, u.full_name AS reviewer_name FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.business_id = ? ORDER BY r.created_at DESC LIMIT 5"
    );
    $revStmt->execute([$bid]);
    $recentReviews = $revStmt->fetchAll();
}

$sellerName = $sellerUser['full_name'] ?? $_SESSION['user_name'] ?? 'Seller';
$logoUrl = !empty($biz['logo'])
    ? media_url($biz['logo'])
    : profile_avatar_url($sellerName, $sellerUser['profile_image'] ?? null);
$publicShopUrl = $biz && $biz['status'] === 'approved'
    ? vendor_profile_url((int) $biz['id'], current_request_return_url())
    : null;

require __DIR__ . '/partials/layout-start.php';
?>

<?php if ($m = flash('success')): ?><div class="alert alert-success shadow-sm"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('error')): ?><div class="alert alert-danger shadow-sm"><?= e($m) ?></div><?php endif; ?>

<div class="lk-dash-hero mb-4">
    <div class="row align-items-center g-4 position-relative" style="z-index: 1;">
        <div class="col-md-auto text-center text-md-start">
            <div class="logo-ring mx-auto mx-md-0">
                <img src="<?= e($logoUrl) ?>" alt="Business">
            </div>
        </div>
        <div class="col">
            <p class="text-warning small text-uppercase fw-bold mb-1" style="letter-spacing: 0.08em;">Seller workspace</p>
            <h1 class="h3 fw-bold mb-1"><?= e($sellerName) ?></h1>
            <?php if ($biz): ?>
                <p class="mb-2 opacity-90"><?= e($biz['business_name']) ?></p>
                <span class="badge bg-<?= business_status_badge_class((string) $biz['status']) ?> text-uppercase"><?= e($biz['status']) ?></span>
                <?php if ($biz['status'] === 'rejected' && !empty($biz['rejection_reason'])): ?>
                    <p class="small mt-2 mb-0 opacity-75"><?= e($biz['rejection_reason']) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="mb-0 opacity-75">Set up your business profile to start selling on LikhaLokal.</p>
            <?php endif; ?>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <?php if ($publicShopUrl): ?>
                    <a href="<?= e($publicShopUrl) ?>" class="btn btn-sm btn-lk-orange" target="_blank" rel="noopener">View website</a>
                <?php endif; ?>
                <a href="<?= e(SELLER_URL) ?>business-profile.php" class="btn btn-sm btn-lk-outline-white">Edit business profile</a>
                <a href="<?= e(SELLER_URL) ?>products.php" class="btn btn-sm btn-outline-light">Add product / service</a>
                <a href="<?= e(SELLER_URL) ?>messages.php" class="btn btn-sm btn-outline-light">Open messages</a>
            </div>
        </div>
    </div>
</div>

<?php if (!$biz): ?>
<div class="lk-panel p-4 text-center">
    <i class="bi bi-shop fs-1 text-warning d-block mb-3"></i>
    <h2 class="h5">No business profile yet</h2>
    <p class="text-muted mb-3">Create your listing so local customers can find you on LikhaLokal.</p>
    <a href="<?= e(SELLER_URL) ?>business-profile.php" class="btn btn-lk-orange">Create business profile</a>
</div>
<?php else: ?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(243,146,0,0.15); color: var(--lk-orange);"><i class="bi bi-box-seam-fill"></i></div>
            <div class="text-muted small">Products / services</div>
            <div class="stat-value"><?= (int) $stats['products'] ?></div>
            <a href="<?= e(SELLER_URL) ?>products.php" class="small fw-semibold text-decoration-none">Manage →</a>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(2,62,138,0.12); color: var(--lk-navy);"><i class="bi bi-chat-dots-fill"></i></div>
            <div class="text-muted small">Messages</div>
            <div class="stat-value"><?= (int) $stats['messages'] ?></div>
            <?php if ($stats['unread'] > 0): ?><span class="badge bg-danger"><?= (int) $stats['unread'] ?> unread</span><?php endif; ?>
            <a href="<?= e(SELLER_URL) ?>messages.php" class="small fw-semibold text-decoration-none d-block">Open inbox →</a>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(27,67,50,0.12); color: #1b4332;"><i class="bi bi-star-fill"></i></div>
            <div class="text-muted small">Reviews</div>
            <div class="stat-value"><?= (int) $stats['reviews'] ?></div>
            <a href="<?= e(SELLER_URL) ?>reviews.php" class="small fw-semibold text-decoration-none">View all →</a>
        </div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="lk-stat-card">
            <div class="stat-icon" style="background: rgba(0,31,63,0.08); color: var(--lk-navy);"><i class="bi bi-graph-up"></i></div>
            <div class="text-muted small">Average rating</div>
            <div class="stat-value"><?= e((string) $stats['avg']) ?></div>
            <span class="small text-muted">Business status: <?= e($biz['status']) ?></span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="lk-panel h-100">
            <div class="lk-panel-header">
                <h2><i class="bi bi-chat-dots me-2 text-warning"></i>Recent messages</h2>
                <a href="<?= e(SELLER_URL) ?>messages.php" class="btn btn-sm btn-lk-orange">View all</a>
            </div>
            <?php if (empty($recentThreads)): ?>
                <div class="p-4 text-center text-muted">No customer messages yet.</div>
            <?php else: ?>
                <?php foreach ($recentThreads as $t): ?>
                <a class="lk-msg-row d-flex justify-content-between align-items-start gap-2" href="<?= e(SELLER_URL) ?>messages.php">
                    <div>
                        <strong class="d-block"><?= e($t['customer_name'] ?? 'Customer') ?></strong>
                        <span class="small text-muted"><?= e(str_limit($t['last_message'] ?? '', 70)) ?></span>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <?php if ((int) ($t['unread_count'] ?? 0) > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= (int) $t['unread_count'] ?></span>
                        <?php endif; ?>
                        <div class="small text-muted"><?= e(format_datetime_short($t['last_at'] ?? '')) ?></div>
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
                <a href="<?= e(SELLER_URL) ?>reviews.php" class="btn btn-sm btn-lk-orange">View all</a>
            </div>
            <?php if (empty($recentReviews)): ?>
                <div class="p-4 text-center text-muted">No reviews yet.</div>
            <?php else: ?>
                <?php foreach ($recentReviews as $r): ?>
                <div class="lk-msg-row">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong><?= e($r['reviewer_name'] ?? 'Customer') ?></strong>
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

<div class="lk-panel">
    <div class="lk-panel-header"><h2><i class="bi bi-lightning-charge me-2"></i>Quick actions</h2></div>
    <div class="p-3 row g-2">
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(SELLER_URL) ?>business-profile.php"><i class="bi bi-shop"></i> Edit business profile</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(SELLER_URL) ?>products.php"><i class="bi bi-plus-circle"></i> Add product / service</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(SELLER_URL) ?>products.php"><i class="bi bi-box-seam"></i> Manage products</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(SELLER_URL) ?>messages.php"><i class="bi bi-chat-dots"></i> View messages</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(SELLER_URL) ?>reviews.php"><i class="bi bi-star"></i> View reviews</a></div>
        <div class="col-md-6 col-lg-4"><a class="lk-quick-link" href="<?= e(SELLER_URL) ?>promotions.php"><i class="bi bi-megaphone"></i> Update promotions</a></div>
    </div>
</div>

<?php endif; ?>

<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
