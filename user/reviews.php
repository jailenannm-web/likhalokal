<?php

declare(strict_types=1);

$pageTitle = 'My reviews';
$activeUser = 'rev';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$list = db()->prepare('SELECT r.*, b.business_name, a.attraction_name FROM reviews r LEFT JOIN businesses b ON b.id=r.business_id LEFT JOIN tourist_attractions a ON a.id=r.attraction_id WHERE r.user_id=? ORDER BY r.created_at DESC');
$list->execute([$uid]);
$rows = $list->fetchAll();

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">My reviews</h1>
<table class="table table-sm"><thead><tr><th>Target</th><th>Rating</th><th>Status</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= $r['business_id'] ? e($r['business_name']) : e($r['attraction_name']) ?></td>
<td><?= (int) $r['rating'] ?></td>
<td><?= e($r['status']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
