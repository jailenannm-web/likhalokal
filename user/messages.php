<?php

declare(strict_types=1);

$pageTitle = 'My messages';
$activeUser = 'msg';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$view = (string) ($_GET['view'] ?? 'sellers');
if (!in_array($view, ['sellers', 'admin'], true)) {
    $view = 'sellers';
}

$adminId = first_admin_user_id();
$conversations = user_message_conversations($uid);
$activeBusinessId = (int) ($_GET['business_id'] ?? 0);
if ($view === 'sellers' && $activeBusinessId < 1 && !empty($conversations)) {
    $activeBusinessId = (int) $conversations[0]['business_id'];
}

$activeBusiness = null;
if ($view === 'sellers' && $activeBusinessId > 0) {
    $bst = db()->prepare('SELECT b.*, u.id AS owner_user_id FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1');
    $bst->execute([$activeBusinessId]);
    $activeBusiness = $bst->fetch();
}

$apiMessagesUrl = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'messages.php';
$csrf = csrf_token();
$baseUrl = USER_DASH_URL . 'messages.php';

require __DIR__ . '/partials/layout-start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 fw-bold mb-0 text-dark">Messages</h1>
    <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-sm btn-lk-orange"><i class="bi bi-bag me-1"></i> Find sellers</a>
</div>

<div class="lk-msg-tabs">
    <a class="lk-msg-tab <?= $view === 'sellers' ? 'active' : '' ?>" href="<?= e($baseUrl) ?>?view=sellers">Seller Messages</a>
    <a class="lk-msg-tab <?= $view === 'admin' ? 'active' : '' ?>" href="<?= e($baseUrl) ?>?view=admin">Admin Support</a>
</div>

<?php if ($view === 'admin'): ?>
<div class="lk-chat-shell">
    <aside class="lk-chat-list">
        <div class="lk-chat-list-header">Admin Support</div>
        <div class="lk-chat-list-scroll">
            <a class="lk-msg-row active text-decoration-none text-dark" href="<?= e($baseUrl) ?>?view=admin">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="lk-chat-avatar support" style="width:36px;height:36px;font-size:0.85rem;"><i class="bi bi-headset"></i></div>
                    <div>
                        <strong class="d-block">Tourism Admin</strong>
                        <span class="badge bg-primary lk-role-badge">Official Support</span>
                    </div>
                </div>
                <p class="small text-muted mb-0">Contact the tourism office for help</p>
            </a>
        </div>
    </aside>
    <section class="lk-chat-thread">
        <?php if (!$adminId): ?>
            <div class="p-5 text-center text-muted">Admin support is not available right now.</div>
        <?php else: ?>
            <div class="lk-chat-thread-header">
                <div class="lk-chat-avatar support"><i class="bi bi-headset"></i></div>
                <div>
                    <div class="fw-bold fs-5">Tourism Admin</div>
                    <span class="badge bg-primary lk-role-badge">Official Support</span>
                    <span class="small text-success ms-2">Responsive</span>
                </div>
            </div>
            <div id="lkUserChatMessages" class="lk-chat-messages"></div>
            <div class="p-3 border-top bg-white">
                <div id="lkUserChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkUserChatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0" title="Attach image">
                        <i class="bi bi-image"></i>
                        <input type="file" id="lkUserChatAttachment" name="attachment" accept="image/*" class="d-none">
                    </label>
                    <input type="text" id="lkUserChatInput" class="form-control rounded-pill" placeholder="Type your message to admin…" autocomplete="off">
                    <button type="submit" class="btn btn-lk-orange rounded-pill px-4 lk-chat-send-btn"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if ($adminId): ?>
<script>
window.LK_USER_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "admin_support",
  receiverId: <?= (int) $adminId ?>,
  me: <?= (int) $uid ?>,
  csrf: <?= json_encode($csrf) ?>,
  formId: "lkUserChatForm",
  inputId: "lkUserChatInput",
  listId: "lkUserChatMessages",
  fileId: "lkUserChatAttachment",
  previewId: "lkUserChatAttachmentPreview",
  errorId: "lkUserChatError"
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=4"></script>
<?php endif; ?>

<?php else: ?>
<?php if (empty($conversations)): ?>
<div class="lk-panel p-5 text-center">
    <i class="bi bi-chat-heart fs-1 text-warning mb-3 d-block"></i>
    <h2 class="h5">No seller conversations yet</h2>
    <p class="text-muted">Browse the marketplace and send an inquiry to a local business.</p>
    <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-lk-orange">Browse Marketplace</a>
</div>
<?php else: ?>
<div class="lk-chat-shell">
    <aside class="lk-chat-list">
        <div class="lk-chat-list-header">Seller Inbox</div>
        <div class="lk-chat-list-scroll">
            <?php foreach ($conversations as $c): ?>
            <a class="lk-msg-row <?= $activeBusinessId === (int) $c['business_id'] ? 'active' : '' ?> text-decoration-none text-dark"
               href="<?= e($baseUrl) ?>?view=sellers&business_id=<?= (int) $c['business_id'] ?>">
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
    </aside>
    <section class="lk-chat-thread">
        <?php if (!$activeBusiness): ?>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted p-5">
                Select a conversation to view messages.
            </div>
        <?php else: ?>
            <div class="lk-chat-thread-header">
                <div class="lk-chat-avatar"><?= e(strtoupper(substr((string) $activeBusiness['business_name'], 0, 1))) ?></div>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5"><?= e($activeBusiness['business_name']) ?></div>
                    <a href="<?= e(vendor_profile_url((int) $activeBusinessId, current_request_return_url())) ?>" class="btn btn-sm btn-outline-secondary mt-1">View shop</a>
                </div>
            </div>
            <div id="lkUserChatMessages" class="lk-chat-messages"></div>
            <div class="p-3 border-top bg-white">
                <div id="lkUserChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkUserChatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0" title="Attach image">
                        <i class="bi bi-image"></i>
                        <input type="file" id="lkUserChatAttachment" name="attachment" accept="image/*" class="d-none">
                    </label>
                    <input type="text" id="lkUserChatInput" class="form-control rounded-pill" placeholder="Type your message…" autocomplete="off">
                    <button type="submit" class="btn btn-lk-orange rounded-pill px-4 lk-chat-send-btn"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if ($activeBusiness): ?>
<script>
window.LK_USER_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "business_inquiry",
  businessId: <?= (int) $activeBusinessId ?>,
  receiverId: <?= (int) $activeBusiness['owner_user_id'] ?>,
  me: <?= (int) $uid ?>,
  csrf: <?= json_encode($csrf) ?>,
  formId: "lkUserChatForm",
  inputId: "lkUserChatInput",
  listId: "lkUserChatMessages",
  fileId: "lkUserChatAttachment",
  previewId: "lkUserChatAttachmentPreview",
  errorId: "lkUserChatError"
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=4"></script>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
