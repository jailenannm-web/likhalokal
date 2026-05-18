<?php

declare(strict_types=1);

$pageTitle = 'Chat';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';
require_login();

$businessId = (int) ($_GET['business_id'] ?? 0);
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$customerId = isset($_GET['customer_id']) ? (int) $_GET['customer_id'] : 0;
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

if (!in_array($role, ['local_user', 'seller', 'admin'], true)) {
    set_flash('error', 'Please login to use messaging.');
    redirect(BASE_URL . 'index.php');
}

require_once BASE_PATH . '/middleware/csrf.php';
$csrf = csrf_token();
$receiverJs = null;
if ($role === 'local_user') {
    $receiverJs = $ownerId;
} elseif ($role === 'seller') {
    $receiverJs = $ownerId === $me ? ($customerId > 0 ? $customerId : null) : $ownerId;
}

// Product Context
$productContext = null;
if ($productId > 0) {
    $pstmt = db()->prepare('SELECT product_name, image, price FROM products WHERE id = ? AND business_id = ? LIMIT 1');
    $pstmt->execute([$productId, $businessId]);
    $productContext = $pstmt->fetch();
}

$requestedReturn = isset($_GET['return']) ? (string) $_GET['return'] : null;
$defaultBack = ($role === 'local_user')
    ? USER_DASH_URL . 'messages.php?business_id=' . $businessId
    : SELLER_URL . 'messages.php';
$backUrl = resolve_return_url($requestedReturn, $defaultBack);
$shopInfoReturn = $defaultBack;
$bodyClass = trim(($bodyClass ?? '') . ' message-page lk-internal-workspace');
$isDashboardLayout = true;

require BASE_PATH . '/includes/header.php';
?>

<!-- Tourism Boutique Styles -->
<style>
body {
    background: linear-gradient(135deg, #fff3e0 0%, #e8f5e9 40%, #ffffff 100%);
    background-attachment: fixed;
}
.floating-bg-icons {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: -1; overflow: hidden;
}
.float-icon {
    position: absolute; animation-name: floatAround; animation-timing-function: ease-in-out; animation-iteration-count: infinite; animation-direction: alternate;
}
@keyframes floatAround {
    0% { transform: translateY(0) rotate(0deg); }
    100% { transform: translateY(-40px) rotate(15deg); }
}
/* Custom Scrollbar for Chat */
.chat-messages-area::-webkit-scrollbar { width: 6px; }
.chat-messages-area::-webkit-scrollbar-track { background: transparent; }
.chat-messages-area::-webkit-scrollbar-thumb { background: rgba(27,67,50,0.2); border-radius: 10px; }

/* Chat Bubbles */
.chat-bubble-out { background: linear-gradient(135deg, #1b4332, #2d6a4f); color: white; border-radius: 16px 16px 0 16px; }
.chat-bubble-in { background: white; color: #1b4332; border-radius: 16px 16px 16px 0; border: 1px solid rgba(27,67,50,0.1); }
</style>

<div class="floating-bg-icons">
    <i class="fa-solid fa-leaf float-icon" style="top: 10%; left: 5%; animation-duration: 18s; font-size: 3rem; color: rgba(27,67,50,0.05);"></i>
    <i class="fa-regular fa-comments float-icon" style="top: 50%; right: 5%; animation-duration: 22s; font-size: 5rem; color: rgba(243,146,0,0.05);"></i>
    <i class="fa-solid fa-store float-icon" style="top: 80%; left: 10%; animation-duration: 15s; font-size: 4rem; color: rgba(27,67,50,0.05);"></i>
</div>

<div class="vendor-profile-subnav message-subnav">
    <div class="container">
        <a href="<?= e($backUrl) ?>" aria-label="Go back"><i class="fa-solid fa-arrow-left fs-5"></i> Back</a>
        <span class="fw-bold text-uppercase small" style="letter-spacing: 1px; font-family: 'Montserrat', sans-serif;">Inquiry Hub</span>
    </div>
</div>

<div class="py-4 py-lg-5" style="min-height: calc(100vh - 52px); font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center h-100">
        
        <!-- Chat Shell -->
        <div class="bg-white shadow-lg w-100 position-relative" style="max-width: 800px; border-radius: 20px; height: 75vh; display: flex; flex-direction: column; border: 2px solid rgba(27,67,50,0.1); overflow: hidden;">
            
            <!-- Chat Header -->
            <div class="p-3 d-flex align-items-center justify-content-between position-relative bg-light" style="border-bottom: 2px solid rgba(27,67,50,0.1); z-index: 10;">
                <div class="d-flex align-items-center gap-3">
                    <?php $logo = $b['logo'] ? asset_url($b['logo']) : 'https://ui-avatars.com/api/?name=' . urlencode($b['business_name']); ?>
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; border: 2px solid #1b4332; background: white;">
                        <?php if($b['logo']): ?>
                            <img src="<?= e($logo) ?>" class="w-100 h-100 rounded-circle object-fit-cover" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-store" style="color: #1b4332;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 1.2rem; color: #1b4332; font-family: 'Montserrat', sans-serif;"><?= e($b['business_name']) ?></div>
                        <div class="small fw-bold" style="font-size: 0.8rem; color: #f39200;"><i class="fa-solid fa-circle text-success me-1" style="font-size: 0.6rem;"></i> Responsive Seller</div>
                    </div>
                </div>
                <a href="<?= e(vendor_profile_url($businessId, $shopInfoReturn)) ?>" class="btn rounded-pill shadow-sm fw-bold px-3 py-1 text-white" style="background: #1b4332; font-size: 0.85rem;">
                    <i class="fa-regular fa-circle-info me-1"></i> Shop Info
                </a>
            </div>

            <!-- Date Separator -->
            <div class="text-center py-2 text-muted fw-bold small" style="background: #fdfdfd; border-bottom: 1px dashed rgba(0,0,0,0.05); font-family: 'Montserrat', sans-serif; font-size: 0.75rem;">
                Today <?= date('g:i A') ?>
            </div>
            
            <?php if($productContext): ?>
                <div class="bg-white p-3 border-bottom d-flex align-items-center justify-content-center shadow-sm" style="z-index: 5;">
                    <div class="badge rounded-pill fw-bold text-dark px-4 py-2 border shadow-sm" style="background: #fdf9f1; font-family: 'Montserrat', sans-serif; font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-info me-2 text-warning"></i> Inquiring about: <span style="color: #1b4332;"><?= e($productContext['product_name']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Messages Area -->
            <div id="chatMessages" class="chat-messages-area p-4 flex-grow-1 overflow-auto" style="background: #fafafa;"></div>
            
            <!-- Input Area -->
            <div class="p-3 bg-white" style="border-top: 2px solid rgba(27,67,50,0.1);">
                <div id="chatAttachmentPreview" class="px-2 small"></div>
                <form id="chatForm" class="d-flex align-items-center bg-light border rounded-pill px-3 py-2 shadow-sm" style="border-color: rgba(27,67,50,0.2) !important;" enctype="multipart/form-data">
                    <label class="btn btn-link text-secondary p-0 me-2 mb-0" title="Attach image"><i class="fa-solid fa-image"></i><input type="file" id="chatAttachment" name="attachment" accept="image/*" class="d-none"></label>
                    <input type="text" id="chatInput" class="form-control border-0 shadow-none bg-transparent" placeholder="Type your inquiry here..." autocomplete="off" style="font-family: 'Poppins', sans-serif;">
                    <button class="btn fs-4 p-0 shadow-none border-0 ms-2" type="submit" style="color: #f39200;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>

<script>
window.LK_CHAT = {
  apiUrl: <?= json_encode(preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'messages.php') ?>,
  appBase: <?= json_encode(app_root_url()) ?>,
  assetBase: <?= json_encode(ASSET_URL) ?>,
  conversationType: "business_inquiry",
  businessId: <?= (int) $businessId ?>,
  productId: <?= $productId ?: 'null' ?>,
  customerId: <?= $customerId ?: 'null' ?>,
  receiverId: <?= $receiverJs !== null ? (int) $receiverJs : 'null' ?>,
  me: <?= (int) $me ?>,
  csrf: <?= json_encode($csrf) ?>,
  fileId: "chatAttachment",
  previewId: "chatAttachmentPreview"
};

<?php if($productContext): ?>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatInput');
    input.value = `Hello! I would like to inquire about the ${<?= json_encode($productContext['product_name']) ?>}. Is this still available?`;
});
<?php endif; ?>
</script>
<?php
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/lk-chat.js?v=3"></script>';
require BASE_PATH . '/includes/footer.php';
