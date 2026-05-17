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

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Promotions</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Share special offers and seasonal promos on your public shop profile.</p>
</div>

<?php if (!$b): ?>
<div class="lk-panel"><div class="lk-empty-state"><i class="bi bi-megaphone"></i><p class="mb-0">Create a business profile first.</p></div></div>
<?php else: ?>
<div class="lk-panel">
    <div class="lk-panel-header"><h2><i class="bi bi-megaphone me-2 text-warning"></i>Promotional note</h2></div>
    <div class="lk-panel-body">
        <form method="post">
            <?= csrf_field() ?>
            <label class="form-label">Promotional text / special offers</label>
            <textarea class="form-control mb-3" name="promotional_note" rows="5" placeholder="e.g. 10% off this week, free delivery in Vinzons..."><?= e((string) ($b['promotional_note'] ?? '')) ?></textarea>
            <button class="btn btn-lk-orange" type="submit"><i class="bi bi-save me-1"></i> Save promotion</button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
