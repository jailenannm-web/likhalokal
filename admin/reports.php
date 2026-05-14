<?php

declare(strict_types=1);

$pageTitle = 'Reports';
$activeAdmin = 'rep';
require_once __DIR__ . '/_init.php';

$topBiz = db()->query(
    "SELECT b.business_name, COUNT(r.id) AS c FROM reviews r JOIN businesses b ON b.id = r.business_id WHERE r.status='approved' GROUP BY b.id ORDER BY c DESC LIMIT 8"
)->fetchAll();

$topAtt = db()->query(
    "SELECT a.attraction_name, COUNT(r.id) AS c FROM reviews r JOIN tourist_attractions a ON a.id = r.attraction_id WHERE r.status='approved' GROUP BY a.id ORDER BY c DESC LIMIT 8"
)->fetchAll();

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Engagement reports</h1>
<div class="row g-4">
<div class="col-md-6"><h2 class="h6">Most reviewed businesses</h2><ul class="list-group"><?php foreach ($topBiz as $r): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($r['business_name']) ?></span><span class="badge bg-secondary"><?= (int) $r['c'] ?></span></li><?php endforeach; ?></ul></div>
<div class="col-md-6"><h2 class="h6">Most reviewed attractions</h2><ul class="list-group"><?php foreach ($topAtt as $r): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($r['attraction_name']) ?></span><span class="badge bg-secondary"><?= (int) $r['c'] ?></span></li><?php endforeach; ?></ul></div>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
