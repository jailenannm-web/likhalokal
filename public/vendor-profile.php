<?php

declare(strict_types=1);

$pageTitle = 'View Shop';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT b.*, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1');
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b || $b['status'] !== 'approved') {
    http_response_code(404);
    echo 'Business not found.';
    exit;
}

$pstmt = db()->prepare('SELECT * FROM products WHERE business_id = ? ORDER BY category, is_featured DESC, product_name ASC');
$pstmt->execute([$id]);
$products = $pstmt->fetchAll();

// Group products for this shop
$groupedProducts = [];
foreach ($products as $p) {
    $groupedProducts[$p['category']][] = $p;
}

$categoryTitles = [
    'local_delicacy' => 'LOCAL DELICACIES',
    'handicraft' => 'HANDICRAFTS',
    'fresh_produce' => 'FRESH PRODUCE'
];

$rstmt = db()->prepare(
    "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.business_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20"
);
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll();
$avg = business_avg_rating($id);

require_once BASE_PATH . '/middleware/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    require_once BASE_PATH . '/middleware/auth.php';
    require_login();
    if (current_user_role() !== 'local_user') {
        set_flash('error', 'Only local users may submit reviews.');
        redirect(BASE_URL . 'vendor-profile.php?id=' . $id);
    }
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token.');
        redirect(BASE_URL . 'vendor-profile.php?id=' . $id);
    }
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    if ($rating >= 1 && $rating <= 5) {
        $ins = db()->prepare(
            'INSERT INTO reviews (user_id, business_id, attraction_id, rating, comment, status, created_at, updated_at) VALUES (?,?,NULL,?,?,\'pending\',NOW(),NOW())'
        );
        $ins->execute([current_user_id(), $id, $rating, $comment]);
        set_flash('success', 'Thank you! Your review is pending moderation.');
    }
    redirect(BASE_URL . 'vendor-profile.php?id=' . $id);
}

require BASE_PATH . '/includes/header.php';

if ($m = flash('success')) {
    echo '<div class="container mt-3"><div class="alert alert-success shadow-sm fw-bold"><i class="fa-solid fa-circle-check me-2"></i>' . e($m) . '</div></div>';
}
if ($m = flash('error')) {
    echo '<div class="container mt-3"><div class="alert alert-danger shadow-sm fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>' . e($m) . '</div></div>';
}

$cover = $b['cover_image'] ? asset_url($b['cover_image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=80';
$logo = $b['logo'] ? asset_url($b['logo']) : 'https://ui-avatars.com/api/?name=' . urlencode($b['business_name']);
?>

<!-- Custom Topbar for View Shop -->
<div style="background: var(--lk-orange); height: 60px; display: flex; align-items: center; padding: 0 1rem; color: white;">
    <a href="<?= e(BASE_URL) ?>products.php" class="text-white text-decoration-none me-3"><i class="fa-solid fa-arrow-left fs-4"></i></a>
    <span class="prototype-title text-white m-0" style="font-size: 1.5rem; letter-spacing: 1px;">VIEW SHOP</span>
    <div class="ms-auto">
        <i class="fa-solid fa-magnifying-glass fs-5"></i>
    </div>
</div>

<!-- Immersive Header Area -->
<div class="position-relative" style="height: 300px; background: url('<?= e($cover) ?>') center/cover no-repeat;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.3);"></div>
    
    <div class="container position-relative h-100 d-flex align-items-center justify-content-center">
        <!-- Floating Profile Box -->
        <div class="glass-panel d-flex align-items-center gap-4 p-4 shadow-lg w-100" style="max-width: 800px; background: rgba(255,255,255,0.85); backdrop-filter: blur(5px); border-radius: 12px; transform: translateY(50px);">
            <div class="bg-navy rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 100px; height: 100px; background: var(--lk-navy);">
                <?php if($b['logo']): ?>
                    <img src="<?= e($logo) ?>" class="w-100 h-100 rounded-circle object-fit-cover" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-user text-white fs-1"></i>
                <?php endif; ?>
            </div>
            
            <div class="flex-grow-1 text-dark">
                <h2 class="fw-bold mb-1" style="font-family: Impact, sans-serif; letter-spacing: 1px;"><?= e($b['business_name']) ?></h2>
                <div class="d-flex align-items-center gap-3 mb-1 fw-bold" style="font-size: 0.9rem;">
                    <span class="text-dark"><i class="fa-solid fa-star"></i> <?= e((string) $avg) ?></span>
                </div>
                <div class="small fw-bold text-dark mb-1"><i class="fa-solid fa-location-dot me-1"></i> <?= e($b['address'] ?? 'Vinzons') ?></div>
                <div class="small fw-bold text-dark"><i class="fa-solid fa-phone me-1"></i> <?= e($b['contact_number'] ?? 'N/A') ?></div>
            </div>
            
            <div>
                <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= $id ?>" class="btn shadow-sm fw-bold px-4 rounded-pill text-white" style="background: var(--lk-orange);">
                    <i class="fa-regular fa-comment-dots me-1"></i> Chat Seller
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 mt-4" style="font-family: 'Poppins', sans-serif;">
    
    <?php if (empty($groupedProducts)): ?>
        <div class="alert alert-secondary">This shop hasn't listed any products yet.</div>
    <?php else: ?>
        <?php foreach (['local_delicacy', 'handicraft', 'fresh_produce'] as $cat): ?>
            <?php if (!empty($groupedProducts[$cat])): ?>
                <div class="mb-5">
                    <h3 class="prototype-title mb-4" style="font-size: 1.8rem;"><?= $categoryTitles[$cat] ?></h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem;">
                        <?php foreach ($groupedProducts[$cat] as $p): ?>
                            <div class="card overflow-hidden shadow-sm" style="border-radius: 8px;">
                                <?php $pi = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                                <div class="ratio ratio-4x3">
                                    <img src="<?= e($pi) ?>" class="object-fit-cover" alt="">
                                </div>
                                <div class="p-2 bg-light d-flex flex-column h-100">
                                    <div class="fw-bold" style="font-size: 0.9rem; font-family: 'Montserrat', sans-serif;"><?= e($p['product_name']) ?></div>
                                    <div class="small text-muted mb-2" style="font-size: 0.75rem; line-height: 1.2; flex-grow: 1;">
                                        <?= e(str_limit((string) $p['description'], 50)) ?>
                                    </div>
                                    <div class="d-flex justify-content-end align-items-center mt-auto">
                                        <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $p['business_id'] ?>&product_id=<?= (int) $p['id'] ?>" class="badge rounded-pill text-decoration-none" style="background: var(--lk-orange); font-size: 0.7rem; padding: 0.3rem 0.6rem;">
                                            <i class="fa-solid fa-cart-shopping me-1"></i> Buy Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Reviews Section -->
    <div class="mt-5 p-4 rounded" style="background: #e8dcc4; border: 1px solid #ccc; box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);">
        <h3 class="prototype-title mb-3" style="color: var(--lk-navy); font-size: 1.5rem;">REVIEWS & FEEDBACKS</h3>
        <div class="d-flex align-items-end mb-4">
            <span class="fw-bold text-dark" style="font-size: 3rem; font-family: Impact, sans-serif; line-height: 1;"><?= number_format((float)$avg, 1) ?></span>
            <div class="ms-2 pb-1">
                <div class="text-warning fs-5">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="fa-solid fa-star"></i>
                    <?php endfor; ?>
                </div>
                <div class="small fw-bold text-dark"><?= count($reviews) ?> Reviews</div>
            </div>
        </div>
        
        <?php if (!empty($reviews)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                <?php foreach ($reviews as $r): ?>
                    <div class="bg-white p-3 rounded shadow-sm border text-center">
                        <div class="d-flex justify-content-center mb-2">
                            <span class="badge bg-light text-dark border shadow-sm">
                                <i class="fa-solid fa-user me-1"></i> <?= e($r['reviewer_name']) ?>
                            </span>
                        </div>
                        <div class="text-warning mb-2" style="font-size: 0.8rem;">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?= $i <= (int)$r['rating'] ? 'fa-solid fa-star' : 'fa-regular fa-star' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="small text-muted mb-0 font-italic">"<?= e($r['comment'] ?? '') ?>"</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted fw-bold">No reviews yet.</p>
        <?php endif; ?>

        <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
            <div class="mt-4 text-center">
                <button type="button" class="btn bg-white border border-secondary rounded-pill px-5 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    Rate your experience now!
                </button>
            </div>

            <!-- Review Modal -->
            <div class="modal fade" id="reviewModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="post" class="modal-content">
                        <?= csrf_field() ?>
                        <input type="hidden" name="review_submit" value="1">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title fw-bold text-dark">Leave a Review</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Comment</label>
                                <textarea name="comment" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-lk-orange">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/includes/footer.php'; ?>
