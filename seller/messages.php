<?php

declare(strict_types=1);

$pageTitle = 'Seller messages';
$activeSeller = 'msg';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$view = (string) ($_GET['view'] ?? 'customers');
if (!in_array($view, ['customers', 'admin'], true)) {
    $view = 'customers';
}

$stmt = db()->prepare("SELECT id, business_name FROM businesses WHERE user_id=? AND status='approved' LIMIT 1");
$stmt->execute([$uid]);
$b = $stmt->fetch();
$bid = $b ? (int) $b['id'] : 0;
$threads = $bid ? seller_message_threads($uid, $bid) : [];
$adminId = first_admin_user_id();

$activeCustomerId = (int) ($_GET['customer_id'] ?? 0);
if ($view === 'customers' && $activeCustomerId < 1 && !empty($threads)) {
    $activeCustomerId = (int) $threads[0]['customer_id'];
}

$activeCustomer = null;
if ($view === 'customers' && $activeCustomerId > 0) {
    $cst = db()->prepare('SELECT id, full_name, last_seen_at FROM users WHERE id = ? LIMIT 1');
    $cst->execute([$activeCustomerId]);
    $activeCustomer = $cst->fetch();
}

$apiMessagesUrl = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'messages.php';
$csrf = csrf_token();
$baseUrl = SELLER_URL . 'messages.php';

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Messages</h1>
    <p class="lk-dash-page-lead text-muted mb-0">View customer inquiries and admin support messages.</p>
</div>

<?php if (!$bid && $view === 'customers'): ?>
<div class="lk-panel">
    <div class="lk-empty-state">
        <i class="bi bi-chat-dots"></i>
        <p class="mb-2 fw-semibold">No approved business</p>
        <p class="small mb-0">Messages are available after your business is approved.</p>
    </div>
</div>
<?php else: ?>

<div class="lk-msg-tabs">
    <a class="lk-msg-tab <?= $view === 'customers' ? 'active' : '' ?>" href="<?= e($baseUrl) ?>?view=customers">Customer Inquiries</a>
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
                <p class="small text-muted mb-0">Contact the tourism office for support</p>
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
                </div>
            </div>
            <div id="lkSellerChatMessages" class="lk-chat-messages"></div>
            <div class="p-3 border-top bg-white">
                <div id="lkSellerChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkSellerChatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0" title="Attach image">
                        <i class="bi bi-image"></i>
                        <input type="file" id="lkSellerChatAttachment" name="attachment" accept="image/*" class="d-none">
                    </label>
                    <input type="text" id="lkSellerChatInput" class="form-control rounded-pill" placeholder="Type your message to admin…" autocomplete="off">
                    <button type="submit" class="btn btn-lk-orange rounded-pill px-4 lk-chat-send-btn"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if ($adminId): ?>
<script>
window.LK_SELLER_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "admin_support",
  receiverId: <?= (int) $adminId ?>,
  me: <?= (int) $uid ?>,
  csrf: <?= json_encode($csrf) ?>,
  formId: "lkSellerChatForm",
  inputId: "lkSellerChatInput",
  listId: "lkSellerChatMessages",
  fileId: "lkSellerChatAttachment",
  previewId: "lkSellerChatAttachmentPreview",
  errorId: "lkSellerChatError"
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=4"></script>
<?php endif; ?>

<?php else: ?>
<div class="lk-chat-shell">
    <aside class="lk-chat-list">
        <div class="lk-chat-list-header"><i class="bi bi-chat-square-text me-1 text-warning"></i> <?= e($b['business_name'] ?? 'Inbox') ?></div>
        <div class="lk-chat-list-scroll">
            <?php if (empty($threads)): ?>
                <p class="text-muted small text-center py-4 px-3 mb-0">No customer messages yet.</p>
            <?php else: ?>
                <?php foreach ($threads as $t): ?>
                <a class="lk-msg-row <?= $activeCustomerId === (int) $t['customer_id'] ? 'active' : '' ?> text-decoration-none text-dark"
                   href="<?= e($baseUrl) ?>?view=customers&customer_id=<?= (int) $t['customer_id'] ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="d-block"><?= e($t['customer_name'] ?? 'Customer') ?></strong>
                        <?php if ((int) ($t['unread_count'] ?? 0) > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= (int) $t['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-secondary lk-role-badge mt-1">Local User</span>
                    <p class="small text-muted mb-0 mt-1"><?= e(str_limit($t['last_message'] ?? '', 55)) ?></p>
                    <span class="small text-muted"><?= e(format_datetime_short($t['last_at'] ?? '')) ?></span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>
    <section class="lk-chat-thread">
        <?php if (!$activeCustomer): ?>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted p-5">
                <?= empty($threads) ? 'No customer messages yet.' : 'Select a customer conversation.' ?>
            </div>
        <?php else:
            $activity = user_activity_status($activeCustomer['last_seen_at'] ?? null);
            ?>
            <div class="lk-chat-thread-header">
                <div class="lk-chat-avatar"><?= e(strtoupper(substr((string) $activeCustomer['full_name'], 0, 1))) ?></div>
                <div>
                    <div class="fw-bold fs-5"><?= e($activeCustomer['full_name']) ?></div>
                    <span class="badge bg-secondary lk-role-badge">Local User / Customer Inquiry</span>
                    <span class="small text-muted ms-2"><?= e($activity) ?></span>
                </div>
            </div>
            <div id="lkSellerChatMessages" class="lk-chat-messages"></div>
            <div class="p-3 border-top bg-white">
                <div id="lkSellerChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkSellerChatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0" title="Attach image">
                        <i class="bi bi-image"></i>
                        <input type="file" id="lkSellerChatAttachment" name="attachment" accept="image/*" class="d-none">
                    </label>
                    <input type="text" id="lkSellerChatInput" class="form-control rounded-pill" placeholder="Type your reply…" autocomplete="off">
                    <button type="submit" class="btn btn-lk-orange rounded-pill px-4 lk-chat-send-btn"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if ($activeCustomer && $bid): ?>
<script>
window.LK_SELLER_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "business_inquiry",
  businessId: <?= (int) $bid ?>,
  receiverId: <?= (int) $activeCustomer['id'] ?>,
  me: <?= (int) $uid ?>,
  csrf: <?= json_encode($csrf) ?>,
  formId: "lkSellerChatForm",
  inputId: "lkSellerChatInput",
  listId: "lkSellerChatMessages",
  fileId: "lkSellerChatAttachment",
  previewId: "lkSellerChatAttachmentPreview",
  errorId: "lkSellerChatError"
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=4"></script>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
