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

require __DIR__ . '/partials/layout-start.php';
?>
<div class="lk-dash-inner-head"><h1 class="lk-dash-page-title mb-1">Engagement reports</h1><p class="lk-dash-page-lead text-muted mb-0">Top-reviewed businesses and attractions from live review data.</p></div>
<div class="row g-4">
<div class="col-md-6"><div class="lk-panel h-100"><div class="lk-panel-header"><h2>Most reviewed businesses</h2></div>
<?php if (empty($topBiz)): ?><div class="lk-empty-state"><p class="mb-0">No business reviews yet.</p></div>
<?php else: foreach ($topBiz as $r): ?><div class="lk-msg-row d-flex justify-content-between"><span><?= e($r['business_name']) ?></span><span class="badge bg-warning text-dark"><?= (int) $r['c'] ?></span></div><?php endforeach; endif; ?>
</div></div>
<div class="col-md-6"><div class="lk-panel h-100"><div class="lk-panel-header"><h2>Most reviewed attractions</h2></div>
<?php if (empty($topAtt)): ?><div class="lk-empty-state"><p class="mb-0">No attraction reviews yet.</p></div>
<?php else: foreach ($topAtt as $r): ?><div class="lk-msg-row d-flex justify-content-between"><span><?= e($r['attraction_name']) ?></span><span class="badge bg-warning text-dark"><?= (int) $r['c'] ?></span></div><?php endforeach; endif; ?>
</div></div>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
