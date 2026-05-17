<?php

declare(strict_types=1);

$pageTitle = 'My reviews';
$activeUser = 'rev';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$list = db()->prepare(
    'SELECT r.*, b.business_name, a.attraction_name
     FROM reviews r
     LEFT JOIN businesses b ON b.id = r.business_id
     LEFT JOIN tourist_attractions a ON a.id = r.attraction_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC'
);
$list->execute([$uid]);
$rows = $list->fetchAll();

require __DIR__ . '/partials/layout-start.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h4 fw-bold mb-0">My reviews</h1>
    <a href="<?= e(BASE_URL) ?>local-business.php" class="btn btn-sm btn-lk-orange"><i class="bi bi-pencil me-1"></i> Write a review</a>
</div>

<?php if (empty($rows)): ?>
<div class="lk-panel p-5 text-center">
    <i class="bi bi-star fs-1 text-warning mb-3 d-block"></i>
    <h2 class="h5">No reviews yet</h2>
    <p class="text-muted mb-3">Visit a business profile and share your experience after your visit or purchase.</p>
    <a href="<?= e(BASE_URL) ?>local-business.php" class="btn btn-lk-orange">Explore businesses</a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($rows as $r):
        $target = $r['business_name'] ?? $r['attraction_name'] ?? 'Review';
        $link = $r['business_id']
            ? vendor_profile_url((int) $r['business_id'], current_request_return_url())
            : ($r['attraction_id'] ? BASE_URL . 'attraction-detail.php?id=' . (int) $r['attraction_id'] : '#');
    ?>
    <div class="col-md-6 col-xl-4">
        <article class="lk-review-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h2 class="h6 mb-0">
                    <?php if ($link !== '#'): ?>
                        <a href="<?= e($link) ?>" class="text-decoration-none text-dark"><?= e($target) ?></a>
                    <?php else: ?>
                        <?= e($target) ?>
                    <?php endif; ?>
                </h2>
                <span class="badge bg-<?= review_status_badge_class($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span>
            </div>
            <div class="mb-2"><?= render_star_rating((int) $r['rating']) ?></div>
            <p class="small text-secondary mb-3"><?= e($r['comment'] ?? '') ?></p>
            <div class="small text-muted d-flex justify-content-between">
                <span><i class="bi bi-clock me-1"></i><?= e(format_datetime_short($r['created_at'] ?? '')) ?></span>
                <?php if ($r['status'] === 'pending'): ?>
                    <span class="text-warning fw-semibold">Awaiting moderation</span>
                <?php endif; ?>
            </div>
        </article>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
