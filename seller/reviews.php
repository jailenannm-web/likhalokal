<?php

declare(strict_types=1);

$pageTitle = 'Seller reviews';
$activeSeller = 'rev';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$stmt = db()->prepare('SELECT id FROM businesses WHERE user_id=? LIMIT 1');
$stmt->execute([$uid]);
$b = $stmt->fetch();
$bid = $b ? (int) $b['id'] : 0;
$list = [];
if ($bid) {
    $r = db()->prepare(
        "SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.business_id=? ORDER BY r.created_at DESC"
    );
    $r->execute([$bid]);
    $list = $r->fetchAll();
}

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Reviews</h1>
    <p class="lk-dash-page-lead text-muted mb-0">See what customers are saying about your business.</p>
</div>

<div class="lk-panel">
    <?php if (!$bid): ?>
        <div class="lk-empty-state"><i class="bi bi-star"></i><p class="mb-0">No business profile found.</p></div>
    <?php elseif (empty($list)): ?>
        <div class="lk-empty-state"><i class="bi bi-star"></i><p class="mb-0">No reviews yet.</p></div>
    <?php else: ?>
        <?php foreach ($list as $x): ?>
        <article class="lk-msg-row">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <strong><?= e($x['full_name']) ?></strong>
                <span class="badge bg-<?= review_status_badge_class((string) $x['status']) ?>"><?= e(ucfirst((string) $x['status'])) ?></span>
            </div>
            <div class="mb-1"><?= render_star_rating((int) $x['rating']) ?></div>
            <p class="small text-muted mb-0"><?= e($x['comment'] ?? '') ?></p>
            <span class="small text-muted"><?= e(format_datetime_short($x['created_at'] ?? '')) ?></span>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
