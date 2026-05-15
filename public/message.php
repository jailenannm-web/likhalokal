<?php

declare(strict_types=1);

$pageTitle = 'Chat';
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

require_once BASE_PATH . '/middleware/csrf.php';
$csrf = csrf_token();
$receiverJs = ($role === 'local_user') ? $ownerId : null;

// Product Context
$productContext = null;
if ($productId > 0) {
    $pstmt = db()->prepare('SELECT product_name, image, price FROM products WHERE id = ? AND business_id = ? LIMIT 1');
    $pstmt->execute([$productId, $businessId]);
    $productContext = $pstmt->fetch();
}

require BASE_PATH . '/includes/header.php';
?>

<!-- Custom Topbar for Chat -->
<div style="background: var(--lk-orange); height: 60px; display: flex; align-items: center; padding: 0 1rem; color: white;">
    <a href="<?= e(BASE_URL) ?>products.php" class="text-white text-decoration-none me-3"><i class="fa-solid fa-arrow-left fs-4"></i></a>
    <span class="prototype-title text-white m-0" style="font-size: 1.5rem; letter-spacing: 1px;">CHAT</span>
</div>

<div class="py-5" style="background: #fafafa; min-height: calc(100vh - 60px); font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center">
        
        <!-- Chat Shell -->
        <div class="bg-white border shadow-sm w-100 position-relative" style="max-width: 800px; border-radius: 12px; height: 75vh; display: flex; flex-direction: column;">
            
            <!-- Chat Header -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between position-relative bg-white" style="border-radius: 12px 12px 0 0; z-index: 10; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <div class="d-flex align-items-center gap-3">
                    <?php $logo = $b['logo'] ? asset_url($b['logo']) : 'https://ui-avatars.com/api/?name=' . urlencode($b['business_name']); ?>
                    <div class="rounded-circle bg-navy d-flex align-items-center justify-content-center overflow-hidden" style="width: 45px; height: 45px; background: var(--lk-navy);">
                        <?php if($b['logo']): ?>
                            <img src="<?= e($logo) ?>" class="w-100 h-100 object-fit-cover" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-user text-white"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="fw-bold prototype-title" style="font-size: 1.2rem; color: var(--lk-navy); letter-spacing: 0.5px;"><?= e($b['business_name']) ?></div>
                        <div class="small fw-bold text-muted" style="font-size: 0.75rem;"><i class="fa-solid fa-circle text-success me-1"></i> Active Now</div>
                    </div>
                </div>
                <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= $businessId ?>" class="text-dark fs-4"><i class="fa-regular fa-circle-info"></i></a>
            </div>

            <!-- Date Separator -->
            <div class="text-center py-2 text-muted fw-bold small" style="background: #fdfdfd;">Today <?= date('g:i A') ?></div>

            <!-- Messages Area -->
            <div id="chatMessages" class="chat-messages-area p-4 flex-grow-1 overflow-auto" style="background: #fdfdfd;"></div>
            
            <!-- Input Area -->
            <div class="p-4 bg-white border-top" style="border-radius: 0 0 12px 12px;">
                <form id="chatForm" class="d-flex align-items-center border rounded-pill px-3 py-2">
                    <input type="text" id="chatInput" class="form-control border-0 shadow-none bg-transparent" placeholder="Type something..." autocomplete="off">
                    <button class="btn text-navy fs-4 p-0 shadow-none border-0" type="submit" style="color: var(--lk-navy);"><i class="fa-regular fa-paper-plane"></i></button>
                </form>
            </div>
            
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

<?php if($productContext): ?>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatInput');
    input.value = `Regarding <?= addslashes($productContext['product_name']) ?> (₱<?= number_format((float)$productContext['price'], 2) ?>): `;
});
<?php endif; ?>
</script>
<?php
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/chat.js"></script>';
require BASE_PATH . '/includes/footer.php';
