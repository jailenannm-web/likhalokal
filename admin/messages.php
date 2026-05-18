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
$csrf = csrf_token();

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
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Messages</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Read and reply to support conversations from local users and sellers.</p>
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
                <a class="lk-msg-row <?= $activePeerId === $pid ? 'active' : '' ?>"
                   href="<?= e(admin_msg_tab_url($tabBase, $tab, $pid)) ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
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
            </div>
            <div id="lkAdminChatMessages" class="lk-chat-messages"></div>
            <div class="p-3 border-top bg-white">
                <div id="lkAdminChatAttachmentPreview" class="small mb-2"></div>
                <form id="lkAdminChatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                    <label class="btn btn-outline-secondary btn-sm rounded-circle mb-0" title="Attach image">
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

<?php if ($activePeer): ?>
<script>
window.LK_ADMIN_CHAT = {
  apiUrl: <?= json_encode($apiMessagesUrl) ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "admin_support",
  receiverId: <?= (int) $activePeer['id'] ?>,
  me: <?= (int) $adminId ?>,
  csrf: <?= json_encode($csrf) ?>,
  formId: "lkAdminChatForm",
  inputId: "lkAdminChatInput",
  listId: "lkAdminChatMessages",
  fileId: "lkAdminChatAttachment",
  previewId: "lkAdminChatAttachmentPreview",
  errorId: "lkAdminChatError"
};
</script>
<script src="<?= e(ASSET_URL) ?>js/lk-chat.js?v=4"></script>
<?php endif; ?>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
