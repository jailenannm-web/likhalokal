<?php

declare(strict_types=1);

$pageTitle = 'Messages';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';
require_login();

$businessId = (int) ($_GET['business_id'] ?? 0);
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
if ($businessId < 1) {
    http_response_code(400);
    echo 'Invalid conversation.';
    exit;
}

$stmt = db()->prepare('SELECT b.*, u.id AS owner_user_id, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1');
$stmt->execute([$businessId]);
$b = $stmt->fetch();
if (!$b) {
    http_response_code(404);
    exit('Business not found');
}

$me = current_user_id();
$role = current_user_role();
$ownerId = (int) $b['owner_user_id'];

if (!in_array($role, ['local_user', 'seller'], true)) {
    set_flash('error', 'Please use a local or seller account for messaging.');
    redirect(BASE_URL . 'index.php');
}
if ($role === 'local_user' && $ownerId === $me) {
    http_response_code(403);
    exit('Cannot message your own business.');
}
if ($role === 'seller' && (int) $b['user_id'] !== $me) {
    http_response_code(403);
    exit('You can only open chats for your own business.');
}

require_once BASE_PATH . '/middleware/csrf.php';
$csrf = csrf_token();
$receiverJs = ($role === 'local_user') ? $ownerId : null;

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="bg-light py-4">
    <div class="container">
        <div class="chat-shell">
            <div class="chat-header p-3 d-flex align-items-center justify-content-between bg-white">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary" style="width:40px;height:40px;"></div>
                    <div>
                        <div class="fw-bold"><?= e($b['business_name']) ?></div>
                        <div class="small text-success"><span class="badge bg-success rounded-pill">&nbsp;</span> Active now</div>
                    </div>
                </div>
                <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= $businessId ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-info-circle"></i></a>
            </div>
            <div id="chatMessages" class="chat-messages-area p-3"></div>
            <form id="chatForm" class="p-3 bg-white border-top d-flex gap-2">
                <input type="text" id="chatInput" class="form-control" placeholder="Type something..." autocomplete="off">
                <button class="btn btn-lk-orange" type="submit"><i class="bi bi-send"></i></button>
            </form>
        </div>
    </div>
</div>
<script>
window.LK_CHAT = {
  businessId: <?= (int) $businessId ?>,
  productId: <?= $productId ?: 'null' ?>,
  receiverId: <?= $receiverJs !== null ? (int) $receiverJs : 'null' ?>,
  me: <?= (int) $me ?>,
  csrf: <?= json_encode($csrf) ?>
};
</script>
<?php
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/chat.js"></script>';
require BASE_PATH . '/includes/footer.php';
