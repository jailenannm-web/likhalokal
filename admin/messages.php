<?php

declare(strict_types=1);

$pageTitle = 'Messages';
$activeAdmin = 'msg';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$adminId = current_user_id();
$tab = (string) ($_GET['tab'] ?? 'users');
if (!in_array($tab, ['users', 'sellers', 'all'], true)) {
    $tab = 'users';
}

$filterRole = match ($tab) {
    'users' => 'local_user',
    'sellers' => 'seller',
    default => null,
};

$threads = admin_support_threads($adminId, $filterRole);
$activePeerId = (int) ($_GET['peer_id'] ?? 0);
if ($activePeerId < 1 && !empty($threads)) {
    $activePeerId = (int) $threads[0]['peer_id'];
}

$activePeer = null;
if ($activePeerId > 0) {
    $pst = db()->prepare('SELECT id, full_name, role, last_seen_at, profile_image FROM users WHERE id = ? AND role != \'admin\' LIMIT 1');
    $pst->execute([$activePeerId]);
    $activePeer = $pst->fetch();
}

$apiMessagesUrl = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'messages.php';
$apiReceiversUrl = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'message-receivers.php';
$csrf = csrf_token();
$hidePublicFooter = true;

$tabBase = ADMIN_URL . 'messages.php';
function admin_msg_tab_url(string $base, string $tab, int $peerId = 0): string
{
    $url = $base . '?tab=' . rawurlencode($tab);
    if ($peerId > 0) {
        $url .= '&peer_id=' . $peerId;
    }
    return $url;
}

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="lk-dash-page-title mb-1">Messages</h1>
        <p class="lk-dash-page-lead text-muted mb-0">Read and reply to support conversations from local users and sellers.</p>
    </div>
    <button type="button" class="btn btn-lk-orange" data-bs-toggle="modal" data-bs-target="#lkNewMessageModal">
        <i class="bi bi-pencil-square me-1"></i> New Message
    </button>
</div>

<div class="lk-msg-tabs">
    <a class="lk-msg-tab <?= $tab === 'users' ? 'active' : '' ?>" href="<?= e(admin_msg_tab_url($tabBase, 'users')) ?>">User Support</a>
    <a class="lk-msg-tab <?= $tab === 'sellers' ? 'active' : '' ?>" href="<?= e(admin_msg_tab_url($tabBase, 'sellers')) ?>">Seller Support</a>
    <a class="lk-msg-tab <?= $tab === 'all' ? 'active' : '' ?>" href="<?= e(admin_msg_tab_url($tabBase, 'all')) ?>">All Support</a>
</div>

<div class="lk-chat-shell">
    <aside class="lk-chat-list">
        <div class="lk-chat-list-header">Support Inbox</div>
        <div class="lk-chat-list-scroll">
            <?php if (empty($threads)): ?>
                <p class="text-muted small text-center py-4 px-3 mb-0">No support messages yet.</p>
            <?php else: ?>
                <?php foreach ($threads as $t):
                    $pid = (int) $t['peer_id'];
                    $activity = user_activity_status($t['last_seen_at'] ?? null);
                    ?>
                <div class="lk-msg-row <?= $activePeerId === $pid ? 'active' : '' ?>">
                    <a class="lk-msg-row-main d-block text-decoration-none text-dark pe-4"
                       href="<?= e(admin_msg_tab_url($tabBase, $tab, $pid)) ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2 pe-3">
                        <strong class="d-block"><?= e($t['full_name'] ?? 'User') ?></strong>
                        <?php if ((int) ($t['unread_count'] ?? 0) > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= (int) $t['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-secondary lk-role-badge mt-1"><?= e(role_display_label((string) ($t['role'] ?? ''))) ?></span>
                    <p class="small text-muted mb-0 mt-1"><?= e(str_limit((string) ($t['last_message'] ?? ''), 50)) ?></p>
                    <span class="small text-muted d-block"><?= e(format_datetime_short($t['last_at'] ?? '')) ?></span>
                    <span class="small text-success"><?= e($activity) ?></span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger lk-msg-row-delete"
                            title="Delete conversation"
                            data-delete-conversation
                            data-conversation-type="admin_support"
                            data-receiver-id="<?= $pid ?>"
                            data-redirect="<?= e(admin_msg_tab_url($tabBase, $tab)) ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <section class="lk-chat-thread">
        <?php if (!$activePeer): ?>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted p-5">
                <div class="text-center">
                    <i class="bi bi-chat-left-text fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-0"><?= empty($threads) ? 'No support messages yet.' : 'Select a conversation to view messages.' ?></p>
                </div>
            </div>
        <?php else:
            $initials = strtoupper(substr((string) $activePeer['full_name'], 0, 1));
            $activity = user_activity_status($activePeer['last_seen_at'] ?? null);
            ?>
            <div class="lk-chat-thread-header">
                <div class="lk-chat-avatar"><?= e($initials) ?></div>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5"><?= e($activePeer['full_name']) ?></div>
                    <span class="badge bg-info text-dark lk-role-badge"><?= e(role_display_label((string) $activePeer['role'])) ?></span>
                    <span class="small text-muted ms-2"><?= e($activity) ?></span>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle lk-chat-delete-btn"
                        title="Delete conversation"
                        data-delete-conversation
                        data-conversation-type="admin_support"
                        data-receiver-id="<?= (int) $activePeer['id'] ?>"
                        data-redirect="<?= e(admin_msg_tab_url($tabBase, $tab)) ?>">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div id="lkAdminChatMessages" class="lk-chat-messages"></div>
            <div class="lk-chat-composer">
                <div id="lkAdminChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkAdminChatForm" class="lk-chat-form" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0 lk-chat-action-btn" title="Attach image">
                        <i class="bi bi-image"></i>
                        <input type="file" id="lkAdminChatAttachment" name="attachment" accept="image/*" class="d-none">
                    </label>
                    <input type="text" id="lkAdminChatInput" class="form-control rounded-pill" placeholder="Type your reply…" autocomplete="off">
                    <button type="submit" class="btn btn-lk-orange rounded-pill px-4 lk-chat-send-btn"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>

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
window.LK_ADMIN_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: <?= json_encode($activePeer ? 'admin_support' : '') ?>,
  receiverId: <?= (int) ($activePeer['id'] ?? 0) ?>,
  me: <?= (int) $adminId ?>,
  csrf: <?= json_encode($csrf) ?>,
  receiverSearchUrl: <?= json_encode($apiReceiversUrl) ?>,
  formId: "lkAdminChatForm",
  inputId: "lkAdminChatInput",
  listId: "lkAdminChatMessages",
  fileId: "lkAdminChatAttachment",
  previewId: "lkAdminChatAttachmentPreview",
  errorId: "lkAdminChatError",
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
    defaultRedirect: <?= json_encode($tabBase) ?>
  }
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=6"></script>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
