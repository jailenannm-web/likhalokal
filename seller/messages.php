<?php

declare(strict_types=1);

$pageTitle = 'Seller messages';
$activeSeller = 'msg';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$stmt = db()->prepare("SELECT id, business_name FROM businesses WHERE user_id=? AND status='approved' LIMIT 1");
$stmt->execute([$uid]);
$b = $stmt->fetch();
$bid = $b ? (int) $b['id'] : 0;

$threads = $bid ? seller_message_threads($uid, $bid) : [];

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Messages</h1>
    <p class="lk-dash-page-lead text-muted mb-0">View customer inquiries and reply from the chat workspace.</p>
</div>

<?php if (!$bid): ?>
<div class="lk-panel">
    <div class="lk-empty-state">
        <i class="bi bi-chat-dots"></i>
        <p class="mb-2 fw-semibold">No approved business</p>
        <p class="small mb-0">Messages are available after your business is approved.</p>
    </div>
</div>
<?php else: ?>
<div class="lk-panel mb-3">
    <div class="lk-panel-header">
        <h2><i class="bi bi-chat-square-text me-2 text-warning"></i><?= e($b['business_name']) ?></h2>
        <a class="btn btn-sm btn-lk-orange" href="<?= e(BASE_URL) ?>message.php?business_id=<?= $bid ?>&return=<?= rawurlencode(current_request_return_url()) ?>">
            <i class="bi bi-box-arrow-up-right me-1"></i> Open chat workspace
        </a>
    </div>
</div>
<div class="lk-panel">
    <div class="lk-panel-header"><h2>Conversations</h2></div>
    <?php if (empty($threads)): ?>
        <div class="lk-empty-state">
            <i class="bi bi-inbox"></i>
            <p class="mb-0">No customer messages yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($threads as $t): ?>
        <a class="lk-msg-row d-flex justify-content-between align-items-start gap-2 text-decoration-none text-dark" href="<?= e(BASE_URL) ?>message.php?business_id=<?= $bid ?>&customer_id=<?= (int) $t['customer_id'] ?>&return=<?= rawurlencode(current_request_return_url()) ?>">
            <div>
                <strong class="d-block"><?= e($t['customer_name'] ?? 'Customer') ?></strong>
                <span class="small text-muted"><?= e(str_limit($t['last_message'] ?? '', 70)) ?></span>
            </div>
            <div class="text-end flex-shrink-0">
                <?php if ((int) ($t['unread_count'] ?? 0) > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= (int) $t['unread_count'] ?></span>
                <?php endif; ?>
                <span class="small text-muted d-block"><?= e(format_datetime_short($t['last_at'] ?? '')) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
