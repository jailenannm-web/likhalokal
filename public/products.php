<?php

declare(strict_types=1);

$pageTitle = 'Marketplace';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$sql = "SELECT p.*, b.business_name, b.id AS business_id, b.logo, b.address, b.contact_number, b.business_type 
        FROM products p 
        JOIN businesses b ON b.id = p.business_id 
        WHERE b.status = 'approved'
        ORDER BY p.category, b.business_name, p.is_featured DESC, p.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

// Group products by category, then by business
$groupedByCategory = [];
$allShops = []; 

foreach ($products as $p) {
    $cat = $p['category'];
    $bId = $p['business_id'];
    
    // Build category array
    if (!isset($groupedByCategory[$cat])) {
        $groupedByCategory[$cat] = [];
    }
    
    // Build business array inside category
    if (!isset($groupedByCategory[$cat][$bId])) {
        $groupedByCategory[$cat][$bId] = [
            'business_id' => $bId,
            'business_name' => $p['business_name'],
            'products' => []
        ];
    }
    
    $groupedByCategory[$cat][$bId]['products'][] = $p;
    
    // Collect unique shops for the footer section
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

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Prototype Hero -->
<section class="hero position-relative" style="min-height: 45vh; background-image:url('https://images.unsplash.com/photo-1595821035099-52d3a68d8393?auto=format&fit=crop&w=1600&q=80'); background-position: center; background-size: cover;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,31,63,0.5);"></div>
    <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5">
        <h1 class="display-3 fw-bold text-white mb-2" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 5px rgba(0,0,0,0.5);">SUPORTA LOKAL,<br>LIKHA LOKAL</h1>
        <p class="text-white" style="font-family: 'Dancing Script', cursive; font-size: 2.2rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">Mga produktong tunay, gawa ng sariling komunidad.</p>
    </div>
</section>

<div class="container py-4">
    <!-- Quick Categories -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="#cat-local_delicacy" class="text-decoration-none">
                <div class="position-relative rounded overflow-hidden shadow-sm" style="height: 120px;">
                    <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover" alt="Local Delicacies">
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">Local Delicacies</div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="#cat-handicraft" class="text-decoration-none">
                <div class="position-relative rounded overflow-hidden shadow-sm" style="height: 120px;">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover" alt="Handicrafts">
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">Handicrafts</div>
                </div>
            </a>
        </div>
    </div>
    
    <p class="text-center small text-muted px-md-5 mb-5">From handcrafted souvenirs to fresh harvests and local delicacies, discover products made with skill, tradition, and the flavors of Vinzons—crafted by nature, perfected by the community.</p>

    <div class="see-deals-divider">
        <span class="small align-top text-dark" style="font-family: 'Dancing Script', cursive; font-size: 1.5rem; margin-right: 0.5rem; transform: translateY(-10px); display: inline-block;">See</span> 
        <span style="font-family: Impact, sans-serif; color: var(--lk-green); letter-spacing: 2px;">DEALS</span>
    </div>

    <?php foreach (['local_delicacy', 'handicraft', 'fresh_produce'] as $cat): ?>
        <?php if (!empty($groupedByCategory[$cat])): ?>
            <div id="cat-<?= $cat ?>" class="mb-5">
                <!-- Category Title -->
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <div style="flex:1; border-top: 2px solid #ccc;"></div>
                    <h2 class="px-4 mb-0" style="font-family: Impact, sans-serif; color: var(--lk-green); letter-spacing: 2px; text-transform: uppercase;">
                        <?= $categoryTitles[$cat] ?>
                    </h2>
                    <div style="flex:1; border-top: 2px solid #ccc;"></div>
                </div>

                <!-- Group by Shop -->
                <div class="row g-4">
                    <?php foreach ($groupedByCategory[$cat] as $shop): ?>
                        <div class="col-12 mb-2">
                            <!-- Shop Header Box -->
                            <div class="d-flex align-items-center mb-2">
                                <div class="shop-header-box shadow-sm mb-0 flex-grow-0 me-3" style="border: 2px solid var(--lk-green); border-radius: 8px; padding: 0.5rem 1rem; display: inline-flex; align-items: center; background: white;">
                                    <i class="fa-solid fa-store fs-5 me-3" style="color: var(--lk-green);"></i>
                                    <h3 class="mb-0" style="color: var(--lk-green); font-family: Impact, sans-serif; font-size: 1.2rem; letter-spacing: 1px; min-width: 250px;">
                                        <?= e($shop['business_name']) ?>
                                    </h3>
                                    <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= $shop['business_id'] ?>" class="btn-dark-green ms-3" style="background: var(--lk-green); color: white; border-radius: 20px; font-weight: bold; padding: 0.3rem 1rem; text-decoration: none;">
                                        <i class="fa-regular fa-comment-dots me-1"></i> Chat Seller
                                    </a>
                                </div>
                            </div>
                            
                            <p class="small text-muted mb-3 fst-italic ms-1" style="max-width: 600px;">Taste the flavors and take home the craftsmanship of our town. Every product reflects the creativity and livelihood of our local makers.</p>

                            <!-- Products Grid -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                                <?php foreach ($shop['products'] as $index => $p): 
                                    $isSpotlight = ($index === 0);
                                    $gridClass = $isSpotlight ? 'product-spotlight' : '';
                                ?>
                                    <div class="card overflow-hidden shadow-sm card-lift <?= $gridClass ?>" style="border-radius: 8px; border: 1px solid rgba(0,0,0,0.05);">
                                        <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80'; ?>
                                        <div class="ratio ratio-4x3">
                                            <img src="<?= e($img) ?>" class="object-fit-cover" alt="">
                                        </div>
                                        <div class="p-3 bg-light d-flex flex-column h-100">
                                            <div class="fw-bold text-dark mb-1" style="font-size: 1rem; font-family: 'Montserrat', sans-serif;"><?= e($p['product_name']) ?></div>
                                            <div class="small text-muted mb-3" style="font-size: 0.8rem; line-height: 1.3; flex-grow: 1;">
                                                <?= e(str_limit((string)$p['description'], 70)) ?>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-2">
                                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">₱<?= e(number_format((float)$p['price'], 2)) ?></div>
                                                <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int)$p['business_id'] ?>&product_id=<?= (int)$p['id'] ?>" class="badge rounded-pill text-decoration-none text-dark shadow-sm" style="background: var(--lk-orange); font-size: 0.75rem; padding: 0.4rem 0.8rem;">
                                                    Buy Now <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.6rem;"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- LIST OF SHOPS -->
    <div class="mt-5 pt-4">
        <div class="d-flex align-items-center mb-4">
            <div style="flex:1; border-top: 2px solid #ccc;"></div>
            <h2 class="px-4 mb-0" style="font-family: Impact, sans-serif; color: var(--lk-green); letter-spacing: 2px;">
                LIST OF SHOPS
            </h2>
            <div style="flex:1; border-top: 2px solid #ccc;"></div>
        </div>

        <div class="row g-3">
            <?php foreach ($allShops as $shop): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= $shop['id'] ?>" class="text-decoration-none">
                        <div class="shop-list-card card-lift border-0" style="background: #a8f5b4; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 1rem; color: #112a20; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                            <div class="shop-list-icon bg-white bg-opacity-50" style="border-radius: 8px; padding: 1rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-store fs-3" style="color: var(--lk-navy);"></i>
                            </div>
                            <div class="text-dark">
                                <div class="fw-bold" style="font-family: 'Montserrat', sans-serif; font-size: 1.05rem;"><?= e($shop['name']) ?></div>
                                <div class="small opacity-75 mb-1" style="font-size: 0.75rem;"><?= e($shop['type']) ?></div>
                                <div class="small fw-bold" style="font-size: 0.8rem;"><i class="fa-solid fa-phone me-1"></i> <?= e($shop['contact']) ?></div>
                                <div class="small fw-bold" style="font-size: 0.8rem;"><i class="fa-solid fa-location-dot me-1"></i> <?= e($shop['address']) ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require BASE_PATH . '/includes/footer.php'; ?>
