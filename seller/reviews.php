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
        "SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.business_id=? AND r.status='approved' ORDER BY r.created_at DESC"
    );
    $r->execute([$bid]);
    $list = $r->fetchAll();
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Reviews</h1>
<ul class="list-group"><?php foreach ($list as $x): ?><li class="list-group-item"><strong><?= e($x['full_name']) ?></strong> <?= (int) $x['rating'] ?>★<div class="small"><?= e($x['comment'] ?? '') ?></div></li><?php endforeach; ?></ul>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
