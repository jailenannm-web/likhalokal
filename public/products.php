<?php

declare(strict_types=1);

$pageTitle = 'Marketplace | LikhaLokal';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$sql = "SELECT p.*, b.business_name, b.id AS business_id, b.logo, b.address, b.contact_number, b.business_type 
        FROM products p 
        JOIN businesses b ON b.id = p.business_id 
        WHERE b.status = 'approved'
        ORDER BY p.category, b.id, p.is_featured DESC, p.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

// Group products by category AND by business
$groupedByCategory = [];
$allShops = [];
$categorySpotlights = [];

foreach ($products as $p) {
    $cat = $p['category'];
    $bId = $p['business_id'];
    
    if (!isset($groupedByCategory[$cat])) {
        $groupedByCategory[$cat] = [];
    }
    
    if (!isset($groupedByCategory[$cat][$bId])) {
        $groupedByCategory[$cat][$bId] = [
            'shop_info' => [
                'id' => $bId,
                'name' => $p['business_name'],
                'logo' => $p['logo']
            ],
            'products' => []
        ];
    }
    
    $groupedByCategory[$cat][$bId]['products'][] = $p;
    
    // Save the very first product of the category as the spotlight banner
    if (!isset($categorySpotlights[$cat])) {
        $categorySpotlights[$cat] = $p;
    }
    
    if (!isset($allShops[$bId])) {
        $allShops[$bId] = [
            'id' => $bId,
            'name' => $p['business_name'],
            'address' => $p['address'] ?? 'Vinzons',
            'contact' => $p['contact_number'] ?? 'N/A',
            'type' => $p['business_type']
        ];
    }
}

$categoryTitles = [
    'local_delicacy' => 'LOCAL DELICACIES',
    'handicraft' => 'HANDICRAFTS',
    'fresh_produce' => 'FRESH PRODUCE'
];

$categorySpotlightTitles = [
    'local_delicacy' => 'VINZONS BEST DELICACY',
    'handicraft' => 'VINZONS BEST HANDICRAFT',
    'fresh_produce' => 'FRESH FROM VINZONS'
];

$categoryIcons = [
    'local_delicacy' => '<i class="fa-solid fa-bowl-rice"></i>',
    'handicraft' => '<i class="fa-solid fa-hands-holding-circle"></i>',
    'fresh_produce' => '<i class="fa-solid fa-seedling"></i>'
];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<style>
/* Tourism & Organic Vibe Enhancements */
body {
    background: linear-gradient(135deg, #fff3e0 0%, #e8f5e9 40%, #ffffff 100%);
    background-attachment: fixed;
}
.floating-bg-icons {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    z-index: -1;
    overflow: hidden;
}
.float-icon {
    position: absolute;
    animation-name: floatAround;
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
    animation-direction: alternate;
}
@keyframes floatAround {
    0% { transform: translateY(0) rotate(0deg); }
    100% { transform: translateY(-30px) rotate(15deg); }
}

.hero-text-animate {
    animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.category-stamp {
    display: inline-block;
    padding: 0.4rem 2rem;
    background: #fff;
    border: 2px dashed #1b4332;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(27,67,50,0.06);
    position: relative;
    transition: transform 0.3s ease;
}
.category-stamp:hover {
    transform: translateY(-2px);
}
.category-stamp::before {
    content: '';
    position: absolute;
    top: -4px; left: -4px; right: -4px; bottom: -4px;
    border: 1px solid rgba(27,67,50,0.2);
    border-radius: 55px;
    pointer-events: none;
}

.spotlight-card {
    transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    background: linear-gradient(135deg, #f4faf6 0%, #e2f2e6 100%) !important;
    border-radius: 16px !important;
    border: 1px solid rgba(27, 67, 50, 0.08);
}
.spotlight-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(27,67,50,0.08) !important;
}
.spotlight-img {
    transition: transform 0.5s ease;
    max-height: 200px;
    object-fit: cover;
    border-radius: 12px;
}
.spotlight-card:hover .spotlight-img {
    transform: scale(1.03);
}

.shop-header-card {
    border-left: 5px solid #1b4332;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.shop-header-card:hover {
    box-shadow: 0 6px 16px rgba(27,67,50,0.08) !important;
    border-left-color: #f39200;
}

.product-card-tourism {
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    border: 1px solid rgba(0,0,0,0.04);
    background: #fff;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.01);
}
.product-card-tourism:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(27, 67, 50, 0.08);
    border-color: rgba(243,146,0,0.2);
}

.scroll-btn-tourism {
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    background: #1b4332;
    color: white;
    border: none;
    border-radius: 50%;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 3px 8px rgba(27,67,50,0.2);
    transition: all 0.2s ease;
}
.scroll-btn-tourism:hover {
    background: #f39200;
    transform: translateY(-50%) scale(1.08);
}

.product-carousel {
    scroll-behavior: smooth;
    padding-bottom: 0.75rem;
    padding-top: 0.25rem;
}
.product-carousel::-webkit-scrollbar {
    height: 4px;
}
.product-carousel::-webkit-scrollbar-track {
    background: rgba(27,67,50,0.02);
    border-radius: 10px;
}
.product-carousel::-webkit-scrollbar-thumb {
    background-color: rgba(27,67,50,0.12);
    border-radius: 10px;
}

.quick-cat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px;
}
.quick-cat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.quick-cat-img {
    transition: transform 0.5s ease;
}
.quick-cat-card:hover .quick-cat-img {
    transform: scale(1.03);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<!-- Floating Background Icons -->
<div class="floating-bg-icons">
    <i class="fa-solid fa-leaf float-icon" style="top: 15%; left: 8%; animation-duration: 15s; font-size: 2.2rem; color: rgba(27,67,50,0.03);"></i>
    <i class="fa-solid fa-basket-shopping float-icon" style="top: 40%; right: 7%; animation-duration: 20s; font-size: 2.8rem; color: rgba(243,146,0,0.03);"></i>
    <i class="fa-solid fa-seedling float-icon" style="top: 75%; left: 6%; animation-duration: 18s; font-size: 2.5rem; color: rgba(27,67,50,0.03);"></i>
    <i class="fa-solid fa-sun float-icon" style="top: 25%; right: 12%; animation-duration: 25s; font-size: 3.5rem; color: rgba(243,146,0,0.02);"></i>
    <i class="fa-solid fa-bowl-rice float-icon" style="top: 85%; right: 10%; animation-duration: 17s; font-size: 2.2rem; color: rgba(27,67,50,0.03);"></i>
</div>

<!-- Enhanced Hero Section -->
<section class="hero position-relative" style="min-height: 60vh; background-image: url('<?= asset_url('images/products-hero2.png') ?>'), url('<?= asset_url('images/products-hero.png') ?>'); background-position: center; background-size: cover, cover; background-repeat: no-repeat, no-repeat;">
    <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5 hero-text-animate">
        <h1 class="display-3 fw-bold text-white mb-2" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4);">
            SUPORTA LOKAL,<br><span style="color: #ffda79;">LIKHA LOKAL</span>
        </h1>
        <p class="text-white" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Mga produktong tunay, gawa ng sariling komunidad.</p>
    </div>
</section>

<div class="container py-5">
    
    <!-- Quick Categories Carousel -->
    <div class="position-relative mb-4">
        <div class="d-flex overflow-auto gap-4 pb-3 product-carousel px-1" id="quick-cat-scroll">
            
            <a href="#cat-local_delicacy" class="text-decoration-none flex-shrink-0" style="width: 420px; max-width: 85vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 160px;">
                    <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Local Delicacies">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-4" style="background: linear-gradient(transparent, rgba(27,67,50,0.9)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-bowl-rice me-2" style="color: #f39200;"></i>Local Delicacies
                    </div>
                </div>
            </a>

            <a href="#cat-handicraft" class="text-decoration-none flex-shrink-0" style="width: 420px; max-width: 85vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 160px;">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Handicrafts">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-4" style="background: linear-gradient(transparent, rgba(27,67,50,0.9)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-hands-holding-circle me-2" style="color: #f39200;"></i>Handicrafts
                    </div>
                </div>
            </a>

            <a href="#cat-fresh_produce" class="text-decoration-none flex-shrink-0" style="width: 420px; max-width: 85vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 160px;">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Fresh Produce">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-4" style="background: linear-gradient(transparent, rgba(27,67,50,0.9)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-seedling me-2" style="color: #f39200;"></i>Fresh Produce
                    </div>
                </div>
            </a>

            <!-- Right Padding Spacer -->
            <div class="flex-shrink-0" style="width: 20px;"></div>
        </div>
    </div>

    <!-- Tourism Slogan Text -->
    <p class="text-center px-md-5 mb-5 mx-auto fw-medium" style="max-width: 900px; font-family: 'Montserrat', sans-serif; font-size: 1.15rem; line-height: 1.8; color: #1b4332; font-style: italic;">
        "From handcrafted souvenirs to fresh harvests and local delicacies, discover products made with skill, tradition, and the flavors of Vinzons—crafted by nature, perfected by the community."
    </p>
    
    <!-- See DEALS Divider -->
    <div class="d-flex align-items-center justify-content-center mb-5 mt-5">
        <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.4);"></div>
        <div class="mx-4 text-center d-flex align-items-center flex-column position-relative">
            <i class="fa-solid fa-sun position-absolute" style="color: rgba(243, 146, 0, 0.15); font-size: 5rem; z-index: -1; top: -15px;"></i>
            <span class="text-dark" style="font-family: 'Dancing Script', cursive; font-size: 2.2rem; color: #1b4332 !important; margin-bottom: -15px;">See</span>
            <span style="font-family: Impact, sans-serif; font-size: 3.5rem; color: #f39200; letter-spacing: 3px; text-shadow: 2px 2px 0px rgba(243,146,0,0.2);">DEALS</span>
        </div>
        <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.4);"></div>
    </div>

    <?php foreach (['local_delicacy', 'handicraft', 'fresh_produce'] as $cat): ?>
        <?php if (!empty($groupedByCategory[$cat])): ?>
            <div id="cat-<?= $cat ?>" class="mb-5 pb-5">
                
                <!-- Category Partition Header -->
                <div class="text-center mb-4" style="max-width: 650px; margin: 0 auto;">
                    <div class="category-divider d-flex align-items-center mb-3">
                        <div style="flex: 1; height: 1px; background: linear-gradient(to right, transparent, #1b4332);"></div>
                        <div class="category-stamp mx-3 mx-md-4">
                            <h2 class="m-0" style="font-family: 'Montserrat', sans-serif; color: #1b4332; font-size: 1.35rem !important; font-weight: 700; letter-spacing: 1px !important;">
                                <span style="color: #f39200;" class="me-2"><?= $categoryIcons[$cat] ?></span> 
                                <?= $categoryTitles[$cat] ?>
                            </h2>
                        </div>
                        <div style="flex: 1; height: 1px; background: linear-gradient(to left, transparent, #1b4332);"></div>
                    </div>
                    <p class="mx-auto text-muted fst-italic px-3 small" style="max-width: 650px; font-family: 'Poppins', sans-serif; font-size: 0.88rem; line-height: 1.5;">
                        <?php 
                        if($cat === 'local_delicacy') echo "Taste the rich heritage of Vinzons. Every delicacy reflects the creativity, tradition, and livelihood of our local makers.";
                        if($cat === 'handicraft') echo "Celebrate the incredible artistry of Vinzons with handcrafted souvenirs woven with skill and passion.";
                        if($cat === 'fresh_produce') echo "From vibrant local farms straight to your table — discover nature's finest harvests.";
                        ?>
                    </p>
                </div>

                <!-- Spotlight Banner Feature -->
                <?php 
                    $spotlightProduct = $categorySpotlights[$cat]; 
                    $imgSpot = $spotlightProduct['image'] ? asset_url($spotlightProduct['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80';
                ?>
                <div style="max-width: 950px; margin: 0 auto 3rem auto;">
                    <div class="card border-0 shadow-sm spotlight-card">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-5 col-lg-4 p-3">
                                <div class="shadow-sm overflow-hidden" style="border-radius: 12px;">
                                    <img src="<?= e($imgSpot) ?>" class="img-fluid w-100 spotlight-img" alt="">
                                </div>
                            </div>
                            <div class="col-md-7 col-lg-8 p-4">
                                <span class="badge rounded-pill mb-2 bg-warning text-dark shadow-sm" style="font-size: 0.65rem; font-family: 'Montserrat', sans-serif; font-weight: 700;">
                                    <i class="fa-solid fa-star me-1"></i> Seller's Pick
                                </span>
                                <div class="small fw-bold mb-1" style="letter-spacing: 1px; color: #1b4332; font-family: 'Montserrat', sans-serif; font-size: 0.75rem;">
                                    <?= $categorySpotlightTitles[$cat] ?>
                                </div>
                                <h3 class="fw-bold text-dark mb-2" style="font-family: 'Montserrat', sans-serif; font-size: 1.6rem;">
                                    <?= e($spotlightProduct['product_name']) ?>
                                </h3>
                                <p class="text-secondary mb-4 small line-clamp-2" style="max-width: 90%; font-size: 0.85rem; line-height: 1.45;">
                                    <?= e(str_limit((string)$spotlightProduct['description'], 140)) ?>
                                </p>
                                <div>
                                    <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int)$spotlightProduct['business_id'] ?>&product_id=<?= (int)$spotlightProduct['id'] ?>" class="btn text-white fw-bold px-4 py-2 shadow-sm rounded-pill" style="background: #f39200; font-size: 0.8rem;">
                                        <i class="fa-solid fa-basket-shopping me-2"></i> Order Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shops under this Category -->
                <?php foreach ($groupedByCategory[$cat] as $shopId => $shopData): ?>
                    <div class="shop-section mb-5 position-relative">
                        
                        <!-- Beautiful Shop Header Banner Row -->
                        <div class="shop-header-card p-3 mb-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm border border-light" style="width: 44px; height: 44px; flex-shrink: 0;">
                                    <?php if(!empty($shopData['shop_info']['logo'])): ?>
                                        <img src="<?= asset_url($shopData['shop_info']['logo']) ?>" class="w-100 h-100 rounded-circle object-fit-cover" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid fa-store text-secondary" style="font-size: 1rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="m-0 fw-bold" style="font-family: Impact, sans-serif; color: #1b4332; letter-spacing: 0.5px; font-size: 1.35rem; text-transform: uppercase; line-height: 1.1;">
                                        <?= e($shopData['shop_info']['name']) ?>
                                    </h3>
                                    <span class="text-muted d-block" style="font-family: 'Dancing Script', cursive; font-size: 1.05rem; color: #f39200 !important; line-height: 1;">Proudly Vinzons</span>
                                </div>
                            </div>
                            <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= $shopId ?>" class="btn btn-sm text-white fw-bold px-4 py-2 rounded-pill shadow-sm" style="background: #1b4332; font-size: 0.75rem; transition: background 0.2s;" onmouseover="this.style.background='#f39200';" onmouseout="this.style.background='#1b4332';">
                                <i class="fa-regular fa-comment-dots me-1.5"></i> Inquire Seller
                            </a>
                        </div>
                        
                        <!-- Horizontal Product Row Slider -->
                        <div class="position-relative">
                            <div class="d-flex overflow-auto gap-3 product-carousel px-1" id="carousel-<?= $cat ?>-<?= $shopId ?>">
                                <?php foreach ($shopData['products'] as $p): ?>
                                    <div class="product-card-tourism flex-shrink-0 d-flex flex-column" style="width: 175px;">
                                        <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80'; ?>
                                        <div class="w-100 aspect-ratio-1x1 position-relative overflow-hidden" style="border-radius: 12px 12px 0 0; aspect-ratio: 1/1; background-color: #fcfcfc;">
                                            <img src="<?= e($img) ?>" class="object-fit-cover w-100 h-100" style="transition: transform 0.4s ease;" alt="">
                                        </div>
                                        <div class="p-2.5 bg-white d-flex flex-column flex-grow-1" style="border-radius: 0 0 12px 12px;">
                                            <h6 class="fw-bold mb-1 text-truncate text-dark" style="font-family: 'Montserrat', sans-serif; font-size: 0.85rem;" title="<?= e($p['product_name']) ?>"><?= e($p['product_name']) ?></h6>
                                            <p class="text-secondary mb-0 line-clamp-2" style="font-size: 0.7rem; line-height: 1.4; flex-grow: 1;">
                                                <?= e(str_limit((string)$p['description'], 60)) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Spacer for right padding -->
                                <?php if(count($shopData['products']) > 3): ?>
                                    <div class="flex-shrink-0" style="width: 15px;"></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Compact Scroll Arrow Overlay -->
                            <?php if(count($shopData['products']) > 4): ?>
                                <button class="scroll-btn-tourism" onclick="document.getElementById('carousel-<?= $cat ?>-<?= $shopId ?>').scrollBy({left: 190, behavior: 'smooth'})">
                                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem;"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- LIST OF SHOPS (Local Sellers Section) -->
    <div class="mt-5 pt-5 border-top border-2" style="border-color: rgba(27,67,50,0.1) !important;">
        <div class="text-center mb-5">
            <div class="category-stamp" style="border-color: #f39200; padding: 0.5rem 2.5rem;">
                <h2 class="m-0" style="font-family: Impact, sans-serif; color: #1b4332; letter-spacing: 1.5px; font-size: 1.5rem;">
                    MEET OUR <span style="color: #f39200;">LOCAL SELLERS</span>
                </h2>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($allShops as $shop): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= $shop['id'] ?>" class="text-decoration-none">
                        <div class="product-card-tourism p-4 h-100 d-flex flex-column shadow-sm" style="background: #ffffff; width: 100%;">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm border" style="width: 48px; height: 48px; flex-shrink: 0; border-color: #e8f5e9 !important;">
                                    <?php if(!empty($shop['logo'])): ?>
                                        <img src="<?= asset_url($shop['logo']) ?>" class="w-100 h-100 rounded-circle object-fit-cover" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid fa-store text-muted" style="font-size: 0.9rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="text-truncate">
                                    <div class="fw-bold text-dark text-truncate" style="font-family: 'Montserrat', sans-serif; font-size: 0.95rem;"><?= e($shop['name']) ?></div>
                                    <span class="badge bg-light text-dark border py-1" style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase;"><?= e($shop['type']) ?></span>
                                </div>
                            </div>
                            <div class="mt-auto pt-2 border-top border-light" style="font-size: 0.75rem; font-family: 'Montserrat', sans-serif;">
                                <div class="text-muted text-truncate mb-0.5"><i class="fa-solid fa-phone me-1.5 text-secondary"></i> <?= e($shop['contact']) ?></div>
                                <div class="text-muted text-truncate"><i class="fa-solid fa-location-dot me-1.5 text-secondary"></i> <?= e($shop['address']) ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require BASE_PATH . '/includes/footer.php'; ?>