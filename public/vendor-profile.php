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
    'fresh_produce' => 'FRESH PRODUCE',
    'souvenir' => 'SOUVENIRS & GIFTS',
    'apparel' => 'LOCAL APPAREL',
    'services' => 'TOURISM SERVICES',
    'other' => 'OTHER PRODUCTS'
];

foreach (array_keys($groupedProducts) as $k) {
    if (!isset($categoryTitles[$k])) {
        $categoryTitles[$k] = strtoupper(str_replace('_', ' ', $k));
    }
}

$rstmt = db()->prepare(
    "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.business_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20"
);
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll();
$avg = business_avg_rating($id);

// --- MOCK DATA FOR DEMONSTRATION IF EMPTY ---
if (empty($groupedProducts)) {
    $groupedProducts = [
        'local_delicacy' => [
            ['id'=>1, 'business_id'=>$id, 'product_name'=>'Premium Pili Nut Brittle', 'description'=>'Sweet, crunchy, and locally harvested pili nuts mixed with rich caramel.', 'image'=>''],
            ['id'=>2, 'business_id'=>$id, 'product_name'=>'Cassava Cake Special', 'description'=>'Authentic baked cassava cake with a creamy coconut topping.', 'image'=>'']
        ],
        'souvenir' => [
            ['id'=>3, 'business_id'=>$id, 'product_name'=>'Handwoven Abaca Tote', 'description'=>'A beautiful, sturdy handwoven tote bag perfect for beach trips and daily use.', 'image'=>''],
            ['id'=>4, 'business_id'=>$id, 'product_name'=>'Vinzons Magnet Set', 'description'=>'Set of 3 carved wooden refrigerator magnets featuring local landmarks.', 'image'=>'']
        ]
    ];
}
if (empty($reviews)) {
    $reviews = [
        ['reviewer_name'=>'Maria Santos', 'rating'=>5, 'comment'=>'Absolutely loved the local products! The seller was incredibly kind and helpful.'],
        ['reviewer_name'=>'Juan Dela Cruz', 'rating'=>4, 'comment'=>'Great quality handicrafts. Will definitely order again next time we visit.']
    ];
    $avg = 4.5;
}
// --------------------------------------------

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
.vendor-card {
    transition: all 0.3s ease;
}
.vendor-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(27,67,50,0.1) !important;
}
.category-stamp {
    display: inline-block;
    padding: 0.8rem 3rem;
    background: #fff;
    border: 2px dashed #1b4332;
    border-radius: 60px;
    box-shadow: 0 6px 18px rgba(27,67,50,0.12);
    position: relative;
}
.category-stamp::before {
    content: '';
    position: absolute; top: -6px; left: -6px; right: -6px; bottom: -6px;
    border: 2px solid rgba(27,67,50,0.25); border-radius: 65px; pointer-events: none;
}
</style>

<div class="floating-bg-icons">
    <i class="fa-solid fa-store float-icon" style="top: 15%; left: 8%; animation-duration: 15s; font-size: 3.5rem; color: rgba(27,67,50,0.04);"></i>
    <i class="fa-solid fa-basket-shopping float-icon" style="top: 40%; right: 7%; animation-duration: 20s; font-size: 4.5rem; color: rgba(243,146,0,0.04);"></i>
    <i class="fa-solid fa-sun float-icon" style="top: 75%; left: 6%; animation-duration: 18s; font-size: 4rem; color: rgba(27,67,50,0.04);"></i>
    <i class="fa-solid fa-comments float-icon" style="top: 25%; right: 12%; animation-duration: 25s; font-size: 6rem; color: rgba(243,146,0,0.04);"></i>
</div>

<!-- Custom Topbar for View Shop -->
<div style="background: rgba(27,67,50,0.95); backdrop-filter: blur(10px); height: 60px; display: flex; align-items: center; padding: 0 1rem; color: white; border-bottom: 2px solid #f39200;">
    <a href="<?= e(BASE_URL) ?>products.php" class="text-white text-decoration-none me-3" style="transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
        <i class="fa-solid fa-arrow-left fs-4"></i>
    </a>
    <span class="m-0 fw-bold" style="font-family: 'Montserrat', sans-serif; font-size: 1.2rem; letter-spacing: 1px;">LOCAL BUSINESS PROFILE</span>
</div>

<!-- Immersive Header Area -->
<div class="position-relative" style="height: 350px; background: url('<?= e($cover) ?>') center/cover no-repeat; border-bottom: 6px solid #f39200;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(27,67,50,0.85));"></div>
    
    <div class="container position-relative h-100 d-flex align-items-center justify-content-center">
        <!-- Floating Profile Box -->
        <div class="d-flex flex-column flex-md-row align-items-center gap-4 p-4 shadow-lg w-100 position-relative" style="max-width: 900px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; border: 2px solid rgba(27,67,50,0.1); transform: translateY(60px);">
            
            <!-- Absolute Badge -->
            <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill px-4 py-2 shadow-sm" style="background: #f39200; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; letter-spacing: 2px;">
                VERIFIED LOCAL SELLER
            </div>

            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 130px; height: 130px; border: 4px solid #1b4332;">
                <?php if($b['logo']): ?>
                    <img src="<?= e($logo) ?>" class="w-100 h-100 rounded-circle object-fit-cover" alt="">
                <?php else: ?>
                    <i class="fa-solid fa-store text-dark fs-1"></i>
                <?php endif; ?>
            </div>
            
            <div class="flex-grow-1 text-center text-md-start">
                <h2 class="fw-bold mb-1" style="font-family: Impact, sans-serif; letter-spacing: 2px; font-size: 2.5rem; color: #1b4332;"><?= e($b['business_name']) ?></h2>
                <div class="mb-3" style="font-family: 'Dancing Script', cursive; font-size: 1.5rem; color: #f39200;">Owner: <?= e($b['owner_name'] ?? 'Local Resident') ?></div>
                
                <div class="row g-2 text-start">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center p-2 rounded shadow-sm bg-light" style="border-left: 4px solid #1b4332;">
                            <i class="fa-solid fa-location-dot fs-5 me-3" style="color: #f39200; width: 20px; text-align: center;"></i>
                            <div>
                                <div class="small text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: bold;">Business Address</div>
                                <div class="fw-bold text-dark" style="font-family: 'Montserrat', sans-serif; font-size: 0.95rem;"><?= e($b['address'] ?? 'Vinzons, Camarines Norte') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center p-2 rounded shadow-sm bg-light" style="border-left: 4px solid #1b4332;">
                            <i class="fa-solid fa-phone fs-5 me-3" style="color: #f39200; width: 20px; text-align: center;"></i>
                            <div>
                                <div class="small text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: bold;">Contact Information</div>
                                <div class="fw-bold text-dark" style="font-family: 'Montserrat', sans-serif; font-size: 0.95rem;"><?= e($b['contact_number'] ?? 'Not Provided') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3 mt-md-0">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 fw-bold bg-light rounded-pill px-3 py-1 border shadow-sm">
                    <i class="fa-solid fa-star text-warning"></i> <span class="text-dark" style="font-size: 1.1rem;"><?= e((string) $avg) ?> Rating</span>
                </div>
                <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= $id ?>" class="btn fw-bold px-4 py-2 rounded-pill shadow" style="background: #1b4332; color: white; border: 2px solid #1b4332; transition: all 0.3s;" onmouseover="this.style.background='white'; this.style.color='#1b4332';" onmouseout="this.style.background='#1b4332'; this.style.color='white';">
                    <i class="fa-regular fa-comment-dots me-2"></i> Message Seller
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 mt-5">
    
    <!-- Catalog Section Divider -->
    <div class="d-flex align-items-center justify-content-center mb-5 mt-4">
        <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.4);"></div>
        <div class="mx-4 text-center d-flex align-items-center flex-column position-relative">
            <span class="text-dark" style="font-family: 'Dancing Script', cursive; font-size: 2.2rem; color: #1b4332 !important; margin-bottom: -15px;">Shop</span>
            <span style="font-family: Impact, sans-serif; font-size: 3rem; color: #f39200; letter-spacing: 3px;">CATALOG</span>
        </div>
        <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.4);"></div>
    </div>

    <?php if (empty($groupedProducts)): ?>
        <div class="alert alert-secondary text-center fw-bold py-4 rounded-4 border-2 border-dashed shadow-sm" style="border-color: rgba(27,67,50,0.3) !important;">
            <i class="fa-solid fa-box-open fs-1 text-muted mb-3"></i><br>
            This seller hasn't listed any products yet. Check back soon!
        </div>
    <?php else: ?>
        <?php foreach ($groupedProducts as $cat => $products): ?>
            <div class="mb-5 pb-4">
                    
                    <div class="mb-4 d-flex align-items-center">
                        <h3 class="m-0 fw-bold px-4 py-2 rounded-pill shadow-sm bg-white" style="font-family: 'Montserrat', sans-serif; font-size: 1.2rem; color: #1b4332; border-left: 5px solid #f39200;">
                            <?= $categoryTitles[$cat] ?>
                        </h3>
                        <div class="ms-3 flex-grow-1" style="height: 2px; background: linear-gradient(to right, rgba(27,67,50,0.5), transparent);"></div>
                    </div>
                    
                    <div class="row g-4">
                        <?php foreach ($groupedProducts[$cat] as $p): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card h-100 overflow-hidden shadow-sm vendor-card bg-white" style="border-radius: 16px; border: none;">
                                    <?php $pi = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                                    <div class="ratio ratio-1x1 position-relative">
                                        <img src="<?= e($pi) ?>" class="object-fit-cover w-100 h-100" alt="">
                                    </div>
                                    <div class="p-3 d-flex flex-column h-100">
                                        <h6 class="fw-bold mb-1" style="font-family: 'Montserrat', sans-serif; font-size: 1rem; color: #1b4332;"><?= e($p['product_name']) ?></h6>
                                        <p class="small text-muted mb-3" style="font-size: 0.8rem; line-height: 1.4; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= e((string) $p['description']) ?>
                                        </p>
                                        <div class="mt-auto">
                                            <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $p['business_id'] ?>&product_id=<?= (int) $p['id'] ?>" class="btn w-100 text-white shadow-sm" style="background: rgba(27,67,50,0.9); font-size: 0.85rem; font-weight: 600; border-radius: 8px;">
                                                <i class="fa-solid fa-comment-dots me-1 text-warning"></i> Inquire Item
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
    <?php endif; ?>

    <!-- Reviews Section -->
    <div class="mt-5 p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden" style="background: white; border: 1px solid rgba(27,67,50,0.1);">
        <div class="position-absolute top-0 end-0 p-3 opacity-25">
            <i class="fa-solid fa-quote-right" style="font-size: 8rem; color: #f39200;"></i>
        </div>
        
        <div class="text-center mb-5 position-relative z-1">
            <h3 class="fw-bold m-0" style="font-family: Impact, sans-serif; color: #1b4332; font-size: 2.5rem; letter-spacing: 1px;">CUSTOMER REVIEWS</h3>
            <div class="small fw-bold text-muted mb-4" style="font-family: 'Montserrat', sans-serif; letter-spacing: 2px;">WHAT LOCALS ARE SAYING</div>
            
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-pill px-5 py-3 shadow-sm border">
                <span class="fw-bold text-dark me-3" style="font-size: 3rem; font-family: Impact, sans-serif; line-height: 1;"><?= number_format((float)$avg, 1) ?></span>
                <div class="text-start">
                    <div class="fs-4 mb-1" style="color: #f39200;">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="<?= $i <= round((float)$avg) ? 'fa-solid fa-star' : 'fa-regular fa-star' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="small fw-bold text-muted" style="font-family: 'Montserrat', sans-serif;">Based on <?= count($reviews) ?> Reviews</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($reviews)): ?>
            <div class="row g-4 position-relative z-1">
                <?php foreach ($reviews as $r): ?>
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded-4 h-100 shadow-sm border-0 position-relative" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; border: 2px solid #e8f5e9;">
                                        <i class="fa-solid fa-user text-muted"></i>
                                    </div>
                                    <span class="fw-bold text-dark" style="font-family: 'Montserrat', sans-serif; font-size: 1rem;"><?= e($r['reviewer_name']) ?></span>
                                </div>
                                <div style="color: #f39200; font-size: 0.9rem;">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?= $i <= (int)$r['rating'] ? 'fa-solid fa-star' : 'fa-regular fa-star' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.6; font-style: italic;">
                                "<?= e($r['comment'] ?? '') ?>"
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fa-regular fa-comment-dots fs-1 text-muted mb-3 opacity-50"></i>
                <p class="text-muted fw-bold m-0" style="font-family: 'Montserrat', sans-serif;">No reviews yet. Be the first to share your experience!</p>
            </div>
        <?php endif; ?>

        <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
            <div class="mt-5 text-center position-relative z-1">
                <button type="button" class="btn text-white fw-bold px-5 py-3 rounded-pill shadow" style="background: #1b4332; font-family: 'Montserrat', sans-serif; transition: background 0.3s;" onmouseover="this.style.background='#f39200';" onmouseout="this.style.background='#1b4332';" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="fa-solid fa-pen-nib me-2"></i> Rate Your Experience
                </button>
            </div>

            <!-- Review Modal -->
            <div class="modal fade" id="reviewModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="post" class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="review_submit" value="1">
                        <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                            <h4 class="modal-title fw-bold m-0" style="font-family: 'Montserrat', sans-serif; color: #1b4332;">Leave a Review</h4>
                            <button type="button" class="btn-close position-absolute end-0 me-4" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 py-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted small text-uppercase">Your Rating</label>
                                <select name="rating" class="form-select form-select-lg shadow-sm bg-light border-0" required style="cursor: pointer;">
                                    <option value="5">⭐⭐⭐⭐⭐ Outstanding (5)</option>
                                    <option value="4">⭐⭐⭐⭐ Great (4)</option>
                                    <option value="3">⭐⭐⭐ Good (3)</option>
                                    <option value="2">⭐⭐ Fair (2)</option>
                                    <option value="1">⭐ Poor (1)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Your Comment</label>
                                <textarea name="comment" class="form-control shadow-sm bg-light border-0" rows="4" placeholder="Tell everyone what you loved about this local business..." required style="resize: none;"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-center">
                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn px-4 rounded-pill fw-bold shadow-sm" style="background: #f39200; color: white;">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/includes/footer.php'; ?>
