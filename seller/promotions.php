<?php

declare(strict_types=1);

$pageTitle = 'Promotions';
$activeSeller = 'pro';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$stmt = db()->prepare('SELECT * FROM businesses WHERE user_id=? LIMIT 1');
$stmt->execute([$uid]);
$b = $stmt->fetch();

if ($b && $_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    db()->prepare('UPDATE businesses SET promotional_note=?, updated_at=NOW() WHERE id=?')->execute([trim($_POST['promotional_note'] ?? ''), (int) $b['id']]);
    set_flash('success', 'Promotion updated');
    redirect(SELLER_URL . 'promotions.php');
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Promotions</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<?php if ($b): ?>
<form method="post"><?= csrf_field() ?>
<label class="form-label">Promotional text / special offers</label>
<textarea class="form-control mb-2" name="promotional_note" rows="4"><?= e((string) ($b['promotional_note'] ?? '')) ?></textarea>
<button class="btn btn-lk-orange" type="submit">Save</button>
</form>
<?php else: ?><p>No business profile.</p><?php endif; ?>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
