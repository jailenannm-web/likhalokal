<?php

declare(strict_types=1);

$pageTitle = 'Moderate reviews';
$activeAdmin = 'rev';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $id = (int) ($_POST['review_id'] ?? 0);
    $st = $_POST['review_status'] ?? '';
    if ($id && in_array($st, ['approved', 'rejected'], true)) {
        db()->prepare('UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$st, $id]);
    }
    redirect(ADMIN_URL . 'reviews.php');
}

$list = db()->query(
    "SELECT r.*, u.full_name AS reviewer FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.status = 'pending' ORDER BY r.created_at DESC"
)->fetchAll();

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Moderate reviews</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Approve or reject community reviews before they appear publicly.</p>
</div>
<div class="lk-panel">
<div class="lk-dash-table-wrap">
<table class="table table-hover align-middle mb-0">
<thead><tr><th>User</th><th>Target</th><th>Rating</th><th>Comment</th><th></th></tr></thead>
<tbody>
<?php foreach ($list as $r): ?>
<tr>
<td><?= e($r['reviewer']) ?></td>
<td><?= $r['business_id'] ? 'Business #' . (int) $r['business_id'] : 'Attraction #' . (int) $r['attraction_id'] ?></td>
<td><?= render_star_rating((int) $r['rating']) ?></td>
<td><?= e(str_limit((string) $r['comment'], 80)) ?></td>
<td>
<form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="review_status" value="approved"><button class="btn btn-sm btn-success" type="submit">Approve</button></form>
<form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="review_status" value="rejected"><button class="btn btn-sm btn-outline-danger" type="submit">Reject</button></form>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-muted py-4">No pending reviews.</td></tr><?php endif; ?>
</tbody></table>
</div>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
