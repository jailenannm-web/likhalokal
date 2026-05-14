<?php

declare(strict_types=1);

$pageTitle = 'Seller dashboard';
$activeSeller = 'dash';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$stmt = db()->prepare(
    "SELECT * FROM businesses WHERE user_id = ? ORDER BY CASE status WHEN 'approved' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END, id ASC LIMIT 1"
);
$stmt->execute([$uid]);
$biz = $stmt->fetch();
$stats = ['products' => 0, 'messages' => 0, 'reviews' => 0, 'avg' => 0];
if ($biz) {
    $bid = (int) $biz['id'];
    $p = db()->prepare('SELECT COUNT(*) c FROM products WHERE business_id=?');
    $p->execute([$bid]);
    $stats['products'] = (int) $p->fetch()['c'];
    $m = db()->prepare('SELECT COUNT(*) c FROM messages WHERE business_id=?');
    $m->execute([$bid]);
    $stats['messages'] = (int) $m->fetch()['c'];
    $r = db()->prepare("SELECT COUNT(*) c FROM reviews WHERE business_id=? AND status='approved'");
    $r->execute([$bid]);
    $stats['reviews'] = (int) $r->fetch()['c'];
    $stats['avg'] = business_avg_rating($bid);
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Seller dashboard</h1>
<?php if (!$biz): ?>
    <div class="alert alert-warning">You have no business profile yet. <a href="<?= e(SELLER_URL) ?>business-profile.php">Create one</a>.</div>
<?php else: ?>
    <p>Status: <strong><?= e($biz['status']) ?></strong>
        <?php if ($biz['status'] === 'rejected' && $biz['rejection_reason']): ?>
            — <?= e($biz['rejection_reason']) ?>
        <?php endif; ?>
    </p>
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card card-lk p-3">Products<div class="fs-3 fw-bold"><?= (int) $stats['products'] ?></div></div></div>
        <div class="col-md-3"><div class="card card-lk p-3">Messages<div class="fs-3 fw-bold"><?= (int) $stats['messages'] ?></div></div></div>
        <div class="col-md-3"><div class="card card-lk p-3">Reviews<div class="fs-3 fw-bold"><?= (int) $stats['reviews'] ?></div></div></div>
        <div class="col-md-3"><div class="card card-lk p-3">Avg rating<div class="fs-3 fw-bold"><?= e((string) $stats['avg']) ?></div></div></div>
    </div>
    <?php if ($biz['status'] === 'approved'): ?>
        <a class="btn btn-lk-orange" href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $biz['id'] ?>">Open customer chat</a>
    <?php endif; ?>
<?php endif; ?>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
