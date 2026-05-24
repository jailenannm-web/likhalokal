<?php



declare(strict_types=1);



$pageTitle = 'View Shop';

$activeNav = 'business';

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

    'service' => 'TOURISM SERVICES',

    'tour_package' => 'TOUR PACKAGES',

    'food' => 'FOOD',

    'accommodation' => 'ACCOMMODATIONS',

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



$canReview = is_logged_in() && current_user_role() === 'local_user';

$canMessage = is_logged_in()

    && (

        current_user_role() === 'local_user'

        || (current_user_role() === 'seller' && current_user_id() !== (int) $b['user_id'])

    );



require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {

    require_once BASE_PATH . '/middleware/auth.php';

    require_login();

    if (current_user_role() !== 'local_user') {

        set_flash('error', 'Only local users may submit reviews.');

        $returnQs = isset($_GET['return']) ? '&return=' . rawurlencode((string) $_GET['return']) : '';

        redirect(BASE_URL . 'vendor-profile.php?id=' . $id . $returnQs);

    }

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {

        set_flash('error', 'Invalid token.');

        $returnQs = isset($_GET['return']) ? '&return=' . rawurlencode((string) $_GET['return']) : '';

        redirect(BASE_URL . 'vendor-profile.php?id=' . $id . $returnQs);

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

    $returnQs = isset($_GET['return']) ? '&return=' . rawurlencode((string) $_GET['return']) : '';

    redirect(BASE_URL . 'vendor-profile.php?id=' . $id . $returnQs);

}



$requestedReturn = isset($_GET['return']) ? (string) $_GET['return'] : null;

$isInternalProfile = is_internal_return_context($requestedReturn);

$backUrl = resolve_return_url($requestedReturn, BASE_URL . 'local-business.php');

$bodyClass = trim(($bodyClass ?? '') . ' vendor-profile-page' . ($isInternalProfile ? ' lk-internal-workspace' : ' vendor-profile-has-nav'));

$showPublicNavbar = !$isInternalProfile;
$isDashboardLayout = !$showPublicNavbar;



require BASE_PATH . '/includes/header.php';



if ($showPublicNavbar) {
    require BASE_PATH . '/includes/navbar.php';
}
$flashSuccess = flash('success');
$flashError = flash('error');



$cover = media_url($b['cover_image'] ?? null, asset_url('images/localbg.png'));

$logo = media_url($b['logo'] ?? null, 'https://ui-avatars.com/api/?name=' . urlencode($b['business_name']));

$businessType = business_type_label((string) ($b['business_type'] ?? ''));

$businessCategory = trim((string) ($b['business_category'] ?? ''));

if ($businessCategory === '') {

    $businessCategory = $businessType;

}

$payments = json_decode((string) ($b['accepted_payments'] ?? '[]'), true);

if (!is_array($payments)) {

    $payments = [];

}

$display = static fn($value, string $placeholder): string => trim((string) $value) !== '' ? trim((string) $value) : $placeholder;

$hasCoords = isset($b['latitude'], $b['longitude'])

    && $b['latitude'] !== null

    && $b['longitude'] !== null

    && $b['latitude'] !== ''

    && $b['longitude'] !== ''

    && is_numeric($b['latitude'])

    && is_numeric($b['longitude']);

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

body {
    padding-top: 0 !important; /* Enable full-bleed cover behind transparent navbar */
}

/* Premium Spacious Vendor Info Grid Override */
.vendor-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    background: #ffffff;
    border-radius: 16px;
    padding: 25px;
    border: 1px solid rgba(0,0,0,0.05);
}
.vendor-info-card {
    display: flex;
    align-items: center;
    gap: 15px;
    background: none;
    border-left: none;
    box-shadow: none;
    padding: 0;
    min-height: auto;
}
.vendor-info-card i {
    width: 40px;
    height: 40px;
    background: rgba(243, 146, 0, 0.1);
    color: var(--lk-orange);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    padding-top: 0;
}
.vendor-info-card span {
    font-size: 0.72rem;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.vendor-info-card strong {
    font-size: 0.92rem;
    color: var(--lk-navy);
    font-weight: 600;
}
</style>



<div class="floating-bg-icons">

    <i class="fa-solid fa-store float-icon" style="top: 15%; left: 8%; animation-duration: 15s; font-size: 3.5rem; color: rgba(27,67,50,0.04);"></i>

    <i class="fa-solid fa-basket-shopping float-icon" style="top: 40%; right: 7%; animation-duration: 20s; font-size: 4.5rem; color: rgba(243,146,0,0.04);"></i>

    <i class="fa-solid fa-sun float-icon" style="top: 75%; left: 6%; animation-duration: 18s; font-size: 4rem; color: rgba(27,67,50,0.04);"></i>

    <i class="fa-solid fa-comments float-icon" style="top: 25%; right: 12%; animation-duration: 25s; font-size: 6rem; color: rgba(243,146,0,0.04);"></i>

</div>



<?php $msgReturn = rawurlencode(current_request_return_url() ?: $backUrl); ?>


<?php if ($flashSuccess || $flashError): ?>
<div class="container vendor-profile-flash py-2">
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success shadow-sm fw-bold mb-0 py-2"><i class="fa-solid fa-circle-check me-2"></i><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger shadow-sm fw-bold mb-0 py-2"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($flashError) ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="vendor-profile-subnav">
    <div class="container">
        <a href="<?= e($backUrl) ?>" aria-label="Go back"><i class="fa-solid fa-arrow-left fs-5"></i> Back</a>
        <span class="fw-bold text-uppercase small" style="letter-spacing: 1px; font-family: 'Montserrat', sans-serif;">Vendor Profile</span>
    </div>
</div>

<div class="vendor-profile-cover" style="background-image: url('<?= e($cover) ?>');"></div>

<div class="container vendor-profile-shell">

    <article class="vendor-profile-card w-100">

        <span class="vendor-profile-badge badge rounded-pill px-4 py-2 shadow-sm" style="background: #f39200; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; letter-spacing: 2px;">VERIFIED LOCAL SELLER</span>

        <div class="vendor-profile-grid">

            <aside class="vendor-profile-media text-center">

                <div class="vendor-logo-frame mx-auto">

                    <?php if (!empty($b['logo'])): ?>

                        <img src="<?= e($logo) ?>" alt="<?= e($b['business_name']) ?> logo">

                    <?php else: ?>

                        <i class="fa-solid fa-store text-dark fs-1"></i>

                    <?php endif; ?>

                </div>

                <div class="vendor-rating-pill mt-3">

                    <i class="fa-solid fa-star text-warning"></i>

                    <span><?= e((string) $avg) ?> Rating</span>

                </div>

                <?php if ($canMessage): ?>

                    <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= $id ?>&return=<?= e($msgReturn) ?>" class="btn fw-bold px-4 py-2 rounded-pill shadow mt-3" style="background: #1b4332; color: white;">

                        <i class="fa-regular fa-comment-dots me-2"></i> Message Seller

                    </a>

                <?php else: ?>

                    <button type="button" class="btn fw-bold px-4 py-2 rounded-pill shadow mt-3" style="background: #1b4332; color: white;" data-require-auth>

                        <i class="fa-regular fa-comment-dots me-2"></i> Message Seller

                    </button>

                <?php endif; ?>

            </aside>



            <div class="vendor-profile-main">

                <h2 class="vendor-profile-name"><?= e($b['business_name']) ?></h2>

                <div class="vendor-profile-owner">Owner: <?= e($display($b['owner_name'] ?? '', 'Owner not provided')) ?></div>

                <p class="vendor-profile-description"><?= nl2br(e($display($b['description'] ?? '', 'Business description not provided.'))) ?></p>



                <div class="vendor-info-grid">

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-store"></i>

                        <div><span>Business Type</span><strong><?= e($businessType) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-layer-group"></i>

                        <div><span>Business Category</span><strong><?= e($businessCategory) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-phone"></i>

                        <div><span>Contact Number</span><strong><?= e($display($b['contact_number'] ?? '', 'Contact number not provided')) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-envelope"></i>

                        <div><span>Email</span><strong><?= e($display($b['email'] ?? '', 'Email not provided')) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-location-dot"></i>

                        <div><span>Address</span><strong><?= e($display($b['address'] ?? '', 'Address not provided')) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-map-pin"></i>

                        <div><span>Barangay</span><strong><?= e($display($b['barangay'] ?? '', 'Barangay not provided')) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-clock"></i>

                        <div><span>Operating Hours</span><strong><?= e($display($b['operating_hours'] ?? '', 'Operating hours not provided')) ?></strong></div>

                    </div>

                    <div class="vendor-info-card">

                        <i class="fa-solid fa-wallet"></i>

                        <div>

                            <span>Accepted Payments</span>

                            <strong><?= !empty($payments) ? e(implode(', ', array_map('strval', $payments))) : 'Accepted payments not provided' ?></strong>

                        </div>

                    </div>

                    <div class="vendor-info-card vendor-info-card-wide">

                        <i class="fa-solid fa-crosshairs"></i>

                        <div>

                            <span>Map Coordinates</span>

                            <strong><?= $hasCoords ? e($b['latitude'] . ', ' . $b['longitude']) : 'Location unavailable' ?></strong>

                        </div>

                    </div>

                </div>



                <div class="vendor-map-wrap w-100 mt-3">

                    <?php if ($hasCoords): ?>

                        <div class="lk-map-box shadow-sm" id="vendorMap" data-lat="<?= e((string) $b['latitude']) ?>" data-lng="<?= e((string) $b['longitude']) ?>" data-title="<?= e($b['business_name']) ?>" data-address="<?= e($display($b['address'] ?? '', 'Vinzons, Camarines Norte')) ?>"></div>

                    <?php else: ?>

                        <div class="lk-map-box vendor-map-placeholder shadow-sm">

                            <i class="fa-solid fa-map-location-dot"></i>

                            <strong>Location unavailable</strong>

                            <span>Latitude and longitude have not been provided yet.</span>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </article>

</div>



<div class="container vendor-catalog-section py-4">

    

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

                                    <?php 
                                    $pi = media_url($p['image'], 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'); 
                                    $inquireUrl = BASE_URL . 'message.php?business_id=' . (int)$p['business_id'] . '&product_id=' . (int)$p['id'] . '&return=' . e($msgReturn);
                                    $shopProfileUrl = vendor_profile_url((int)$p['business_id'], $msgReturn);
                                    $displayPrice = (float)$p['price'] > 0 ? 'PHP ' . number_format((float)$p['price'], 2) : 'Contact Vendor for Price';
                                    ?>

                                    <a href="#" class="ratio ratio-1x1 position-relative d-block"
                                        data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                        data-name="<?= e($p['product_name']) ?>"
                                        data-image="<?= e($pi) ?>"
                                        data-price="<?= e($displayPrice) ?>"
                                        data-category="<?= e(product_category_label($p['category'] ?? 'other')) ?>"
                                        data-type="<?= e(product_type_label($p['product_type'] ?? null)) ?>"
                                        data-availability="<?= e($p['availability'] ?? 'available') ?>"
                                        data-description="<?= e($p['description']) ?>"
                                        data-shop-name="<?= e($b['business_name']) ?>"
                                        data-shop-url="<?= e($shopProfileUrl) ?>"
                                        data-shop-contact="<?= e($b['contact_number'] ?? 'Contact not provided') ?>"
                                        data-shop-address="<?= e($b['address'] ?? 'Vinzons') ?>"
                                        data-inquire-url="<?= e($inquireUrl) ?>">

                                        <img src="<?= e($pi) ?>" class="object-fit-cover w-100 h-100" alt="">

                                    </a>

                                    <div class="p-3 d-flex flex-column h-100">

                                        <div class="d-flex flex-wrap gap-1 mb-2">

                                            <span class="badge bg-light text-dark border"><?= e(product_category_label($p['category'] ?? 'other')) ?></span>

                                            <span class="badge bg-<?= ($p['availability'] ?? '') === 'available' ? 'success' : 'secondary' ?>"><?= e(ucfirst((string) ($p['availability'] ?? 'available'))) ?></span>

                                        </div>

                                        <h6 class="fw-bold mb-1" style="font-family: 'Montserrat', sans-serif; font-size: 1rem; color: #1b4332;">
                                            <a href="#" class="text-decoration-none" style="color: #1b4332; transition: color 0.2s;" onmouseover="this.style.color='#f39200'" onmouseout="this.style.color='#1b4332'"
                                                data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                                data-name="<?= e($p['product_name']) ?>"
                                                data-image="<?= e($pi) ?>"
                                                data-price="<?= e($displayPrice) ?>"
                                                data-category="<?= e(product_category_label($p['category'] ?? 'other')) ?>"
                                                data-type="<?= e(product_type_label($p['product_type'] ?? null)) ?>"
                                                data-availability="<?= e($p['availability'] ?? 'available') ?>"
                                                data-description="<?= e($p['description']) ?>"
                                                data-shop-name="<?= e($b['business_name']) ?>"
                                                data-shop-url="<?= e($shopProfileUrl) ?>"
                                                data-shop-contact="<?= e($b['contact_number'] ?? 'Contact not provided') ?>"
                                                data-shop-address="<?= e($b['address'] ?? 'Vinzons') ?>"
                                                data-inquire-url="<?= e($inquireUrl) ?>"><?= e($p['product_name']) ?></a>
                                        </h6>

                                        <div class="fw-bold mb-2" style="color:#f39200;"><?= e($displayPrice) ?></div>

                                        <p class="small text-muted mb-3" style="font-size: 0.8rem; line-height: 1.4; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">

                                            <?= e((string) $p['description']) ?>

                                        </p>

                                        <div class="mt-auto">

                                            <button type="button" class="btn w-100 text-white shadow-sm" style="background: rgba(27,67,50,0.9); font-size: 0.85rem; font-weight: 600; border-radius: 8px;"
                                                data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                                data-name="<?= e($p['product_name']) ?>"
                                                data-image="<?= e($pi) ?>"
                                                data-price="<?= e($displayPrice) ?>"
                                                data-category="<?= e(product_category_label($p['category'] ?? 'other')) ?>"
                                                data-type="<?= e(product_type_label($p['product_type'] ?? null)) ?>"
                                                data-availability="<?= e($p['availability'] ?? 'available') ?>"
                                                data-description="<?= e($p['description']) ?>"
                                                data-shop-name="<?= e($b['business_name']) ?>"
                                                data-shop-url="<?= e($shopProfileUrl) ?>"
                                                data-shop-contact="<?= e($b['contact_number'] ?? 'Contact not provided') ?>"
                                                data-shop-address="<?= e($b['address'] ?? 'Vinzons') ?>"
                                                data-inquire-url="<?= e($inquireUrl) ?>">

                                                <i class="fa-solid fa-circle-info me-1 text-warning"></i> View Details

                                            </button>

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



        <?php if ($canReview): ?>

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

        <?php elseif (!is_logged_in()): ?>

            <div class="mt-5 text-center position-relative z-1">

                <button type="button" class="btn text-white fw-bold px-5 py-3 rounded-pill shadow" style="background: #1b4332;" data-require-auth>

                    <i class="fa-solid fa-pen-nib me-2"></i> Submit Review

                </button>

            </div>

        <?php endif; ?>

    </div>

</div>



<!-- Product Details Modal -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: none; box-shadow: 0 20px 50px rgba(0, 31, 63, 0.15);">
      <div class="modal-header text-white" style="background: #001F3F; border-bottom: none; padding: 22px 28px;">
        <h5 class="modal-title fw-bold" id="productDetailModalLabel" style="font-family: 'Montserrat', sans-serif; letter-spacing: 0.5px;">Product Information</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 p-md-5" style="background: linear-gradient(135deg, #fffdf9 0%, #f4faf6 100%);">
        <div class="row g-4 align-items-center">
          <!-- Left: Image Box -->
          <div class="col-md-5">
            <div class="shadow-sm overflow-hidden" style="border-radius: 16px; border: 4px solid #fff; aspect-ratio: 1/1; background-color: #fcfcfc;">
              <img id="modal-product-img" src="" class="w-100 h-100 object-fit-cover" alt="Product Image" style="transition: transform 0.4s ease;">
            </div>
          </div>
          <!-- Right: Information -->
          <div class="col-md-7 text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
              <span id="modal-product-cat" class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill fw-bold" style="font-size:0.68rem; letter-spacing:0.5px;">CATEGORY</span>
              <span id="modal-product-type" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill fw-bold" style="font-size:0.68rem; letter-spacing:0.5px;">TYPE</span>
              <span id="modal-product-avail" class="badge bg-success px-2.5 py-1.5 rounded-pill fw-bold text-white" style="font-size:0.68rem; letter-spacing:0.5px;">AVAILABLE</span>
            </div>
            <h3 id="modal-product-name" class="fw-bold text-dark mb-2" style="font-family: 'Montserrat', sans-serif;">Product Name</h3>
            
            <div class="my-3 py-2 border-top border-bottom border-light">
              <span class="small text-muted d-block font-monospace mb-0.5" style="font-size:0.75rem; font-weight:700;">PROPOSED PRICE</span>
              <span id="modal-product-price" class="h3 fw-bold" style="color: #f39200; font-family: 'Montserrat', sans-serif;">PHP 0.00</span>
            </div>
            
            <div class="mb-4">
              <h6 class="fw-bold text-dark mb-1" style="font-size:0.9rem;"><i class="fa-solid fa-circle-info me-1.5 text-warning"></i> Specifications</h6>
              <p id="modal-product-desc" class="text-secondary small" style="line-height:1.6; text-align:justify; font-family: 'Montserrat', sans-serif; font-size:0.85rem;">Product description goes here.</p>
            </div>
            
            <!-- Seller Details -->
            <div class="p-3 bg-white rounded-3 shadow-sm border border-light mb-4">
              <h6 class="fw-bold mb-2 text-dark" style="font-size:0.85rem;"><i class="fa-solid fa-store me-1.5 text-secondary"></i> Seller Information</h6>
              <div class="row g-2 small text-muted" style="font-size:0.8rem; line-height:1.4;">
                <div class="col-12"><strong>Shop Name:</strong> <a id="modal-shop-link" href="#" class="text-decoration-none fw-bold" style="color: #1b4332;">Shop Name</a></div>
                <div class="col-sm-6"><strong>Contact:</strong> <span id="modal-shop-contact">Contact</span></div>
                <div class="col-sm-6"><strong>Address:</strong> <span id="modal-shop-address">Address</span></div>
              </div>
            </div>

            <div class="d-grid">
              <a id="modal-inquire-btn" href="" class="btn text-white fw-bold py-2.5 rounded-3" style="background: #1b4332; font-size: 0.9rem; transition: background 0.3s;" onmouseover="this.style.background='#f39200';" onmouseout="this.style.background='#1b4332';"><i class="fa-solid fa-comment-dots me-2"></i> Inquire / Message Seller</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById('productDetailModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button/link that triggered the modal
            if (!button) return;
            
            // Extract info from data-* attributes
            const name = button.getAttribute('data-name');
            const image = button.getAttribute('data-image');
            const price = button.getAttribute('data-price');
            const category = button.getAttribute('data-category');
            const type = button.getAttribute('data-type');
            const availability = button.getAttribute('data-availability');
            const description = button.getAttribute('data-description');
            const shopName = button.getAttribute('data-shop-name');
            const shopUrl = button.getAttribute('data-shop-url');
            const shopContact = button.getAttribute('data-shop-contact');
            const shopAddress = button.getAttribute('data-shop-address');
            const inquireUrl = button.getAttribute('data-inquire-url');
            
            // Update modal elements
            modal.querySelector('#modal-product-img').src = image;
            modal.querySelector('#modal-product-name').textContent = name;
            modal.querySelector('#modal-product-price').textContent = price;
            modal.querySelector('#modal-product-cat').textContent = category;
            modal.querySelector('#modal-product-type').textContent = type;
            modal.querySelector('#modal-product-desc').textContent = description || "No specifications provided.";
            
            const availBadge = modal.querySelector('#modal-product-avail');
            availBadge.textContent = availability.toUpperCase();
            if (availability === 'available') {
                availBadge.className = 'badge bg-success px-2.5 py-1.5 rounded-pill fw-bold text-white';
            } else {
                availBadge.className = 'badge bg-secondary px-2.5 py-1.5 rounded-pill fw-bold text-white';
            }
            
            const shopLink = modal.querySelector('#modal-shop-link');
            shopLink.textContent = shopName;
            shopLink.href = shopUrl;
            
            modal.querySelector('#modal-shop-contact').textContent = shopContact;
            modal.querySelector('#modal-shop-address').textContent = shopAddress;
            
            const inquireBtn = modal.querySelector('#modal-inquire-btn');
            if (inquireUrl) {
                inquireBtn.href = inquireUrl;
                inquireBtn.style.display = 'block';
            } else {
                inquireBtn.style.display = 'none';
            }
        });
    }
});
</script>

<?php

if ($hasCoords) {

    $extraScripts = ($extraScripts ?? '')

        . '<script src="' . e(asset_url('js/maps.js')) . '"></script>'

        . '<script>document.addEventListener("DOMContentLoaded",function(){var el=document.getElementById("vendorMap");if(el&&window.likhaInitMap){likhaInitMap(el,el.dataset.lat,el.dataset.lng,el.dataset.title,el.dataset.address);}});</script>';

}

require BASE_PATH . '/includes/footer.php';

?>

