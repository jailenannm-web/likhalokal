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
$apiReceiversUrl = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'message-receivers.php';
$csrf = csrf_token();
$baseUrl = USER_DASH_URL . 'messages.php';
$hidePublicFooter = true;

require __DIR__ . '/partials/layout-start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 fw-bold mb-0 text-dark">Messages</h1>
    <button type="button" class="btn btn-sm btn-lk-orange" data-bs-toggle="modal" data-bs-target="#lkNewMessageModal">
        <i class="bi bi-pencil-square me-1"></i> New Message
    </button>
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
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5">Tourism Admin</div>
                    <span class="badge bg-primary lk-role-badge">Official Support</span>
                    <span class="small text-success ms-2">Responsive</span>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle lk-chat-delete-btn"
                        title="Delete conversation"
                        data-delete-conversation
                        data-conversation-type="admin_support"
                        data-receiver-id="<?= (int) $adminId ?>"
                        data-redirect="<?= e($baseUrl) ?>?view=sellers">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div id="lkUserChatMessages" class="lk-chat-messages"></div>
            <div class="lk-chat-composer">
                <div id="lkUserChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkUserChatForm" class="lk-chat-form" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0 lk-chat-action-btn" title="Attach image">
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
<?php endif; ?>

<?php else: ?>
<div class="lk-chat-shell">
    <aside class="lk-chat-list">
        <div class="lk-chat-list-header">Seller Inbox</div>
        <div class="lk-chat-list-scroll">
            <?php foreach ($conversations as $c): ?>
            <div class="lk-msg-row <?= $activeBusinessId === (int) $c['business_id'] ? 'active' : '' ?>">
                <a class="lk-msg-row-main d-block text-decoration-none text-dark pe-4"
                   href="<?= e($baseUrl) ?>?view=sellers&business_id=<?= (int) $c['business_id'] ?>">
                <div class="d-flex justify-content-between align-items-start pe-3">
                    <strong><?= e($c['business_name'] ?? 'Business') ?></strong>
                    <?php if ((int) ($c['unread_count'] ?? 0) > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= (int) $c['unread_count'] ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($c['inquiry_product_name'])): ?>
                    <p class="small fw-semibold mb-0 mt-1" style="color:#1b4332;">Inquiring about: <?= e(str_limit((string) $c['inquiry_product_name'], 42)) ?></p>
                <?php endif; ?>
                <p class="small text-muted mb-0 mt-1"><?= e(str_limit($c['last_message'] ?? '', 55)) ?></p>
                <span class="small text-muted"><?= e(format_datetime_short($c['last_at'] ?? '')) ?></span>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger lk-msg-row-delete"
                        title="Delete conversation"
                        data-delete-conversation
                        data-conversation-type="business_inquiry"
                        data-business-id="<?= (int) $c['business_id'] ?>"
                        data-receiver-id="<?= (int) ($c['owner_user_id'] ?? 0) ?>"
                        data-redirect="<?= e($baseUrl) ?>?view=sellers">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <?php endforeach; ?>
            <?php if (empty($conversations)): ?>
                <p class="text-muted small text-center py-4 px-3 mb-0">No seller conversations yet.</p>
            <?php endif; ?>
        </div>
    </aside>
    <section class="lk-chat-thread">
        <?php if (!$activeBusiness): ?>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted p-5">
                <?= empty($conversations) ? 'Create a new message to start talking with a seller.' : 'Select a conversation to view messages.' ?>
            </div>
        <?php else: ?>
            <div class="lk-chat-thread-header">
                <div class="lk-chat-avatar"><?= e(strtoupper(substr((string) $activeBusiness['business_name'], 0, 1))) ?></div>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5"><?= e($activeBusiness['business_name']) ?></div>
                    <?php
                    $activeInquiryProduct = null;
                    foreach ($conversations as $conversationRow) {
                        if ((int) $conversationRow['business_id'] === (int) $activeBusinessId) {
                            $activeInquiryProduct = $conversationRow['inquiry_product_name'] ?? null;
                            break;
                        }
                    }
                    ?>
                    <?php if (!empty($activeInquiryProduct)): ?>
                        <div class="small fw-semibold" style="color:#f39200;">Inquiring about: <?= e((string) $activeInquiryProduct) ?></div>
                    <?php endif; ?>
                    <a href="<?= e(vendor_profile_url((int) $activeBusinessId, current_request_return_url())) ?>" class="btn btn-sm btn-outline-secondary mt-1">View shop</a>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle lk-chat-delete-btn"
                        title="Delete conversation"
                        data-delete-conversation
                        data-conversation-type="business_inquiry"
                        data-business-id="<?= (int) $activeBusinessId ?>"
                        data-receiver-id="<?= (int) $activeBusiness['owner_user_id'] ?>"
                        data-redirect="<?= e($baseUrl) ?>?view=sellers">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div id="lkUserChatMessages" class="lk-chat-messages"></div>
            <div class="lk-chat-composer">
                <div id="lkUserChatQuickReplies" class="lk-quick-replies d-none"></div>
                <div id="lkUserChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkUserChatForm" class="lk-chat-form" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0 lk-chat-action-btn" title="Attach image">
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
  quickRepliesId: "lkUserChatQuickReplies",
  errorId: "lkUserChatError"
};
</script>
<?php endif; ?>
<?php endif; ?>

<div class="modal fade" id="lkNewMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="lkNewMessageForm">
            <div class="modal-header">
                <h2 class="modal-title h5">New Message</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="lkNewMessageError" class="alert alert-danger py-2 d-none"></div>
                <label class="form-label">Send to</label>
                <div class="lk-recipient-search mb-3" data-recipient-search>
                    <div class="lk-recipient-selected d-none" id="lkNewMessageSelected">
                        <div>
                            <strong data-selected-label></strong>
                            <span data-selected-meta></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-recipient-clear aria-label="Clear selected receiver">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                    <div class="position-relative">
                        <input type="search" class="form-control" id="lkNewMessageReceiverSearch" placeholder="Search by name, business, or role" autocomplete="off">
                        <div class="lk-recipient-results" id="lkNewMessageReceiverResults"></div>
                    </div>
                    <input type="hidden" id="lkNewMessageConversationType">
                    <input type="hidden" id="lkNewMessageReceiverId">
                    <input type="hidden" id="lkNewMessageReceiverRole">
                    <input type="hidden" id="lkNewMessageBusinessId">
                    <input type="hidden" id="lkNewMessageRedirect">
                </div>
                <label class="form-label">Message</label>
                <textarea class="form-control" id="lkNewMessageText" rows="4" required placeholder="Type your first message"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-lk-orange" id="lkNewMessageSubmit" disabled><i class="bi bi-send-fill me-1"></i> Send</button>
            </div>
        </form>
    </div>
</div>

<script>
window.LK_USER_CHAT = window.LK_USER_CHAT || {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "",
  me: <?= (int) $uid ?>,
  csrf: <?= json_encode($csrf) ?>,
  receiverSearchUrl: <?= json_encode($apiReceiversUrl) ?>,
  newMessage: {
    formId: "lkNewMessageForm",
    searchId: "lkNewMessageReceiverSearch",
    resultsId: "lkNewMessageReceiverResults",
    selectedId: "lkNewMessageSelected",
    conversationTypeId: "lkNewMessageConversationType",
    receiverIdId: "lkNewMessageReceiverId",
    roleId: "lkNewMessageReceiverRole",
    businessIdId: "lkNewMessageBusinessId",
    redirectId: "lkNewMessageRedirect",
    inputId: "lkNewMessageText",
    submitId: "lkNewMessageSubmit",
    errorId: "lkNewMessageError",
    defaultRedirect: <?= json_encode($baseUrl) ?>
  }
};
window.LK_USER_CHAT.receiverSearchUrl = <?= json_encode($apiReceiversUrl) ?>;
window.LK_USER_CHAT.newMessage = window.LK_USER_CHAT.newMessage || {
  formId: "lkNewMessageForm",
  searchId: "lkNewMessageReceiverSearch",
  resultsId: "lkNewMessageReceiverResults",
  selectedId: "lkNewMessageSelected",
  conversationTypeId: "lkNewMessageConversationType",
  receiverIdId: "lkNewMessageReceiverId",
  roleId: "lkNewMessageReceiverRole",
  businessIdId: "lkNewMessageBusinessId",
  redirectId: "lkNewMessageRedirect",
  inputId: "lkNewMessageText",
  submitId: "lkNewMessageSubmit",
  errorId: "lkNewMessageError",
  defaultRedirect: <?= json_encode($baseUrl) ?>
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=8"></script>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
