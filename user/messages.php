<?php

declare(strict_types=1);

$pageTitle = 'My messages';
$activeUser = 'msg';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$conversations = user_message_conversations($uid);
$activeBusinessId = (int) ($_GET['business_id'] ?? 0);
if ($activeBusinessId < 1 && !empty($conversations)) {
    $activeBusinessId = (int) $conversations[0]['business_id'];
}

$activeBusiness = null;
if ($activeBusinessId > 0) {
    $bst = db()->prepare('SELECT b.*, u.id AS owner_user_id FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1');
    $bst->execute([$activeBusinessId]);
    $activeBusiness = $bst->fetch();
}

$apiMessagesUrl = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'messages.php';
$csrf = csrf_token();

require __DIR__ . '/partials/layout-start.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h4 fw-bold mb-0 text-dark">Messages</h1>
    <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-sm btn-lk-orange"><i class="bi bi-bag me-1"></i> Find sellers</a>
</div>

<?php if (empty($conversations)): ?>
<div class="lk-panel p-5 text-center">
    <i class="bi bi-chat-heart fs-1 text-warning mb-3 d-block"></i>
    <h2 class="h5">No conversations yet</h2>
    <p class="text-muted">Browse the marketplace and send an inquiry to a local business.</p>
    <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-lk-orange">Browse Marketplace</a>
</div>
<?php else: ?>
<div class="lk-inbox-layout">
    <div class="lk-panel">
        <div class="lk-panel-header"><h2>Inbox</h2></div>
        <div style="max-height: 480px; overflow-y: auto;">
            <?php foreach ($conversations as $c): ?>
            <a class="lk-msg-row <?= $activeBusinessId === (int) $c['business_id'] ? 'active' : '' ?>"
               href="<?= e(USER_DASH_URL) ?>messages.php?business_id=<?= (int) $c['business_id'] ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <strong><?= e($c['business_name'] ?? 'Business') ?></strong>
                    <?php if ((int) ($c['unread_count'] ?? 0) > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= (int) $c['unread_count'] ?></span>
                    <?php endif; ?>
                </div>
                <p class="small text-muted mb-0 mt-1"><?= e(str_limit($c['last_message'] ?? '', 55)) ?></p>
                <span class="small text-muted"><?= e(format_datetime_short($c['last_at'] ?? '')) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="lk-panel lk-chat-thread">
        <?php if (!$activeBusiness): ?>
            <div class="p-5 text-center text-muted flex-grow-1 d-flex align-items-center justify-content-center">
                Select a conversation to view messages.
            </div>
        <?php else: ?>
            <div class="lk-panel-header">
                <h2><?= e($activeBusiness['business_name']) ?></h2>
                <a href="<?= e(vendor_profile_url((int) $activeBusinessId, current_request_return_url())) ?>" class="btn btn-sm btn-outline-secondary">View shop</a>
            </div>
            <div id="lkUserChatMessages" class="lk-chat-messages"></div>
            <div class="p-3 border-top bg-white">
                <form id="lkUserChatForm" class="d-flex gap-2">
                    <input type="text" id="lkUserChatInput" class="form-control rounded-pill" placeholder="Type your message…" autocomplete="off" required>
                    <button type="submit" class="btn btn-lk-orange rounded-pill px-4"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($activeBusiness): ?>
<script>
window.LK_USER_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  businessId: <?= (int) $activeBusinessId ?>,
  receiverId: <?= (int) $activeBusiness['owner_user_id'] ?>,
  me: <?= (int) $uid ?>,
  csrf: <?= json_encode($csrf) ?>
};
</script>
<script src="<?= e(ASSET_URL) ?>js/user-chat.js?v=1"></script>
<?php endif; ?>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
