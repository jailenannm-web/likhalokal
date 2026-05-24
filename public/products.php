<?php

declare(strict_types=1);

$pageTitle = 'Marketplace & Services | LikhaLokal';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$sql = "SELECT p.*, b.business_name, b.id AS business_id, b.logo, b.address, b.contact_number, b.business_type, b.description, b.cover_image 
        FROM products p 
        JOIN businesses b ON b.id = p.business_id 
        WHERE b.status = 'approved'
        ORDER BY p.category, b.id, p.is_featured DESC, p.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();
$pageReturn = current_request_return_url();

// Group items by category AND by business
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
            'logo' => $p['logo'] ?? null,
            'cover_image' => $p['cover_image'] ?? null,
            'description' => $p['description'] ?? null,
            'address' => $p['address'] ?? 'Vinzons',
            'contact' => $p['contact_number'] ?? 'N/A',
            'type' => $p['business_type']
        ];
    }
}

// Global configuration array including multi-purpose business types
$categoriesConfig = [
    'local_delicacy' => [
        'title' => 'LOCAL DELICACIES',
        'spotlight' => 'VINZONS BEST DELICACY',
        'icon' => '<i class="fa-solid fa-bowl-rice"></i>',
        'tagline' => 'Taste the rich heritage of Vinzons. Every delicacy reflects the creativity, tradition, and livelihood of our local makers.',
        'action_text' => 'Buy Now',
        'action_icon' => 'fa-basket-shopping',
        'badge' => 'Fresh Food'
    ],
    'handicraft' => [
        'title' => 'HANDICRAFTS',
        'spotlight' => 'VINZONS BEST HANDICRAFT',
        'icon' => '<i class="fa-solid fa-hands-holding-circle"></i>',
        'tagline' => 'Celebrate the incredible artistry of Vinzons with handcrafted souvenirs woven with skill and passion.',
        'action_text' => 'Order Handcraft',
        'action_icon' => 'fa-bag-shopping',
        'badge' => 'Artisan Handcrafted'
    ],
    'fresh_produce' => [
        'title' => 'FRESH PRODUCE',
        'spotlight' => 'FRESH FROM VINZONS',
        'icon' => '<i class="fa-solid fa-seedling"></i>',
        'tagline' => "From vibrant local farms straight to your table — discover nature's finest harvests.",
        'action_text' => 'Purchase Direct',
        'action_icon' => 'fa-carrot',
        'badge' => 'Organic Certified'
    ],
    'service' => [
        'title' => 'SERVICES TO AVAIL',
        'spotlight' => 'TRUSTED LOCAL SKILLS',
        'icon' => '<i class="fa-solid fa-bell-concierge"></i>',
        'tagline' => 'Hire talented local providers for your custom requirements, installations, artistic gigs, and technical jobs.',
        'action_text' => 'Avail Service',
        'action_icon' => 'fa-file-signature',
        'badge' => 'Verified Vendor'
    ],
    'tour_package' => [
        'title' => 'TOURS & PACKAGES',
        'spotlight' => 'EXPLORE & UNWIND IN VINZONS',
        'icon' => '<i class="fa-solid fa-map-location-dot"></i>',
        'tagline' => 'Reserve farm stays, dynamic tour experiences, cultural workshops, and rental services directly managed by locals.',
        'action_text' => 'Book Spot Now',
        'action_icon' => 'fa-calendar-check',
        'badge' => 'Eco Tour / Stay'
    ],
    'food' => [
        'title' => 'FOOD',
        'spotlight' => 'LOCAL FOOD FINDS',
        'icon' => '<i class="fa-solid fa-utensils"></i>',
        'tagline' => 'Discover ready-to-eat favorites, food trays, and local flavors from community sellers.',
        'action_text' => 'Order Food',
        'action_icon' => 'fa-basket-shopping',
        'badge' => 'Local Food'
    ],
    'other' => [
        'title' => 'OTHER OFFERINGS',
        'spotlight' => 'CUSTOM REQUESTS',
        'icon' => '<i class="fa-solid fa-comments-dollar"></i>',
        'tagline' => 'Looking for wholesale trade assets, unique raw products, or heavy bespoke fabrication? Contact our vendors directly.',
        'action_text' => 'Request Quote',
        'action_icon' => 'fa-paper-plane',
        'badge' => 'Inquiry Only'
    ]
];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<style>
/* Tourism & Organic Vibe Enhancements */
body {
    background: linear-gradient(135deg, #fff3e0 0%, #e8f5e9 40%, #ffffff 100%);
    background-attachment: fixed;
    padding-top: 0 !important; /* Eliminate white line space under the navbar */
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
    max-height: 220px;
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

/* Premium Smaller Vendor Card Grid & Card styles matching list of agencies section */
.vendors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    align-items: stretch;
    margin-top: 25px;
}

.vendor-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 6px 20px rgba(27, 67, 50, 0.03);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    text-align: left;
}

.vendor-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 30px rgba(27, 67, 50, 0.08);
    border-color: rgba(243, 146, 0, 0.25);
}

.vendor-card-banner {
    width: 100%;
    height: 100px;
    background-size: cover;
    background-position: center;
    position: relative;
    border-bottom: 3px solid #f39200;
}

.vendor-card-logo {
    position: absolute;
    bottom: -20px;
    left: 15px;
    width: 40px;
    height: 40px;
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    border: 2px solid #ffffff;
    z-index: 2;
}

.vendor-card-logo img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.vendor-card-body {
    padding: 28px 16px 16px 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    text-align: left;
}

.vendor-card-badge {
    display: inline-block;
    font-size: 0.58rem;
    font-weight: 800;
    color: #1b4332;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}

.vendor-card-body h3 {
    margin: 0 0 4px 0;
    color: #1b4332;
    font-size: 0.92rem;
    font-weight: 850;
    line-height: 1.2;
    font-family: 'Montserrat', sans-serif;
}

.vendor-card-body h3 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.vendor-card-body h3 a:hover {
    color: #f39200;
}

.vendor-card-body p {
    margin: 0 0 10px 0;
    font-size: 0.72rem;
    color: #555;
    line-height: 1.4;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.vendor-card-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    padding-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.vendor-card-phone {
    font-size: 0.7rem;
    font-weight: 700;
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

.vendor-card-phone i {
    color: #f39200;
}

.vendor-msg-btn {
    background-color: #1b4332;
    color: #ffffff !important;
    border: none;
    border-radius: 50px;
    padding: 4px 12px;
    font-size: 0.68rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(27, 67, 50, 0.15);
}

.vendor-msg-btn:hover {
    background-color: #f39200;
    color: #00152b !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(243, 146, 0, 0.25);
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
    <i class="fa-solid fa-map-location-dot float-icon" style="top: 55%; left: 15%; animation-duration: 22s; font-size: 2.4rem; color: rgba(27,67,50,0.02);"></i>
    <i class="fa-solid fa-bell-concierge float-icon" style="top: 85%; right: 10%; animation-duration: 17s; font-size: 2.2rem; color: rgba(27,67,50,0.03);"></i>
</div>

<!-- Enhanced Hero Section -->
<section class="hero position-relative" style="min-height: 60vh; background-image: url('<?= asset_url('images/products-hero2.png') ?>'), url('<?= asset_url('images/products-hero.png') ?>'); background-position: center; background-size: cover, cover; background-repeat: no-repeat, no-repeat;">
    <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5 hero-text-animate">
        <h1 class="display-3 fw-bold text-white mb-2" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4);">
            EXPLORE, BOOK, & BUY<br><span style="color: #ffda79;">LIKHA LOKAL</span>
        </h1>
        <p class="text-white" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Mga produkto at serbisyong tatak Vinzons.</p>
    </div>
</section>

<div class="container py-5">
    
    <!-- Expanded Quick Categories Carousel -->
    <div class="position-relative mb-4">
        <div class="d-flex overflow-auto gap-4 pb-3 product-carousel px-1" id="quick-cat-scroll">
            
            <a href="#cat-local_delicacy" class="text-decoration-none flex-shrink-0" style="width: 280px; max-width: 75vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 140px;">
                    <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=500&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Local Delicacies">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(27,67,50,0.95)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-bowl-rice me-2" style="color: #f39200;"></i>Delicacies
                    </div>
                </div>
            </a>

            <a href="#cat-handicraft" class="text-decoration-none flex-shrink-0" style="width: 280px; max-width: 75vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 140px;">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=500&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Handicrafts">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(27,67,50,0.95)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-hands-holding-circle me-2" style="color: #f39200;"></i>Handicrafts
                    </div>
                </div>
            </a>

            <a href="#cat-fresh_produce" class="text-decoration-none flex-shrink-0" style="width: 280px; max-width: 75vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 140px;">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=500&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Fresh Produce">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(27,67,50,0.95)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-seedling me-2" style="color: #f39200;"></i>Fresh Produce
                    </div>
                </div>
            </a>

            <a href="#cat-service" class="text-decoration-none flex-shrink-0" style="width: 280px; max-width: 75vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 140px;">
                    <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=500&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Local Services">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(27,67,50,0.95)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-bell-concierge me-2" style="color: #f39200;"></i>Avail Services
                    </div>
                </div>
            </a>

            <a href="#cat-tour_package" class="text-decoration-none flex-shrink-0" style="width: 280px; max-width: 75vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 140px;">
                    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=500&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Book Experiences">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(27,67,50,0.95)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-map-location-dot me-2" style="color: #f39200;"></i>Bookings & Tours
                    </div>
                </div>
            </a>

            <a href="#cat-other" class="text-decoration-none flex-shrink-0" style="width: 280px; max-width: 75vw;">
                <div class="position-relative overflow-hidden shadow-sm quick-cat-card" style="height: 140px;">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=500&q=80" class="w-100 h-100 object-fit-cover quick-cat-img" alt="Inquiries">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white fw-bold fs-5" style="background: linear-gradient(transparent, rgba(27,67,50,0.95)); font-family: 'Montserrat', sans-serif;">
                        <i class="fa-solid fa-comments-dollar me-2" style="color: #f39200;"></i>Inquire / Wholesale
                    </div>
                </div>
            </a>

            <!-- Right Padding Spacer -->
            <div class="flex-shrink-0" style="width: 20px;"></div>
        </div>
    </div>

    <!-- Multi-purpose Tourism Slogan Text -->
    <p class="text-center px-md-5 mb-5 mx-auto fw-medium" style="max-width: 900px; font-family: 'Montserrat', sans-serif; font-size: 1.15rem; line-height: 1.8; color: #1b4332; font-style: italic;">
        "From organic harvests and masterfully handcrafted items to unique community eco-tours and skilled local services—discover options crafted with precision, managed with care, and powered by the families of Vinzons."
    </p>
    
    <!-- See DEALS Divider -->
    <div class="d-flex align-items-center justify-content-center mb-5 mt-5">
        <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.4);"></div>
        <div class="mx-4 text-center d-flex align-items-center flex-column position-relative">
            <i class="fa-solid fa-sun position-absolute" style="color: rgba(243, 146, 0, 0.15); font-size: 5rem; z-index: -1; top: -15px;"></i>
            <span class="text-dark" style="font-family: 'Dancing Script', cursive; font-size: 2.2rem; color: #1b4332 !important; margin-bottom: -15px;">Explore</span>
            <span style="font-family: Impact, sans-serif; font-size: 3.5rem; color: #f39200; letter-spacing: 3px; text-shadow: 2px 2px 0px rgba(243,146,0,0.2);">OFFERINGS</span>
        </div>
        <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.4);"></div>
    </div>

    <!-- Render Dynamic Loop of All Categories Configured -->
    <?php foreach ($categoriesConfig as $catKey => $catMeta): ?>
        <?php if (!empty($groupedByCategory[$catKey])): ?>
            <div id="cat-<?= $catKey ?>" class="mb-5 pb-5">
                
                <!-- Category Partition Header -->
                <div class="text-center mb-4" style="max-width: 750px; margin: 0 auto;">
                    <div class="category-divider d-flex align-items-center mb-3">
                        <div style="flex: 1; height: 1px; background: linear-gradient(to right, transparent, #1b4332);"></div>
                        <div class="category-stamp mx-3 mx-md-4">
                            <h2 class="m-0" style="font-family: 'Montserrat', sans-serif; color: #1b4332; font-size: 1.35rem !important; font-weight: 700; letter-spacing: 1px !important;">
                                <span style="color: #f39200;" class="me-2"><?= $catMeta['icon'] ?></span> 
                                <?= $catMeta['title'] ?>
                            </h2>
                        </div>
                        <div style="flex: 1; height: 1px; background: linear-gradient(to left, transparent, #1b4332);"></div>
                    </div>
                    <p class="mx-auto text-muted fst-italic px-3 small" style="max-width: 650px; font-family: 'Poppins', sans-serif; font-size: 0.88rem; line-height: 1.5;">
                        <?= e($catMeta['tagline']) ?>
                    </p>
                </div>

                <!-- Spotlight Banner Feature Component -->
                <?php 
                    $spotlightItem = $categorySpotlights[$catKey]; 
                    $imgSpot = $spotlightItem['image'] ? media_url($spotlightItem['image'], 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=600&q=80') : 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=600&q=80';
                    $itemPrice = isset($spotlightItem['price']) ? '₱' . number_format((float)$spotlightItem['price'], 2) : 'Inquire for Price';
                ?>
                <div style="max-width: 950px; margin: 0 auto 3rem auto;">
                    <div class="card border-0 shadow-sm spotlight-card hover-lift">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-5 col-lg-4 p-3">
                                <div class="shadow-sm overflow-hidden position-relative" style="border-radius: 12px;">
                                    <img src="<?= e($imgSpot) ?>" class="img-fluid w-100 spotlight-img" alt="">
                                    <div class="position-absolute top-0 end-0 bg-dark text-white fw-bold px-3 py-1 m-2 rounded-pill small bg-opacity-75">
                                        <?= $itemPrice ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7 col-lg-8 p-4">
                                <span class="badge rounded-pill mb-2 bg-warning text-dark shadow-sm" style="font-size: 0.65rem; font-family: 'Montserrat', sans-serif; font-weight: 700;">
                                    <i class="fa-solid fa-star me-1"></i> Featured Spotlight
                                </span>
                                <span class="badge rounded-pill mb-2 bg-light text-dark border" style="font-size: 0.65rem; font-family: 'Montserrat', sans-serif; font-weight: 700;">
                                    <?= e(product_category_label($spotlightItem['category'] ?? $catKey)) ?> / <?= e(product_type_label($spotlightItem['product_type'] ?? null)) ?>
                                </span>
                                <span class="badge rounded-pill mb-2 bg-<?= ($spotlightItem['availability'] ?? '') === 'available' ? 'success' : 'secondary' ?>" style="font-size: 0.65rem; font-family: 'Montserrat', sans-serif; font-weight: 700;">
                                    <?= e(ucfirst((string) ($spotlightItem['availability'] ?? 'available'))) ?>
                                </span>
                                <div class="small fw-bold mb-1" style="letter-spacing: 1px; color: #1b4332; font-family: 'Montserrat', sans-serif; font-size: 0.75rem;">
                                    <?= $catMeta['spotlight'] ?>
                                </div>
                                <h3 class="fw-bold text-dark mb-2" style="font-family: 'Montserrat', sans-serif; font-size: 1.6rem;">
                                    <?= e($spotlightItem['product_name']) ?>
                                </h3>
                                <p class="text-secondary mb-4 small line-clamp-2" style="max-width: 90%; font-size: 0.85rem; line-height: 1.45;">
                                    <?= e(str_limit((string)$spotlightItem['description'], 140)) ?>
                                </p>
                                <div>
                                    <button type="button" class="btn text-white fw-bold px-4 py-2 shadow-sm rounded-pill" style="background: #f39200; font-size: 0.8rem;"
                                        data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                        data-name="<?= e($spotlightItem['product_name']) ?>"
                                        data-image="<?= e($imgSpot) ?>"
                                        data-price="<?= e($itemPrice) ?>"
                                        data-category="<?= e(product_category_label($spotlightItem['category'] ?? $catKey)) ?>"
                                        data-type="<?= e(product_type_label($spotlightItem['product_type'] ?? null)) ?>"
                                        data-availability="<?= e($spotlightItem['availability'] ?? 'available') ?>"
                                        data-description="<?= e($spotlightItem['description']) ?>"
                                        data-shop-name="<?= e($spotlightItem['business_name']) ?>"
                                        data-shop-url="<?= e(vendor_profile_url((int)$spotlightItem['business_id'], $pageReturn)) ?>"
                                        data-shop-contact="<?= e($spotlightItem['contact_number'] ?? 'Contact not provided') ?>"
                                        data-shop-address="<?= e($spotlightItem['address'] ?? 'Vinzons') ?>"
                                        data-inquire-url="<?= e(BASE_URL . 'message.php?business_id=' . (int)$spotlightItem['business_id'] . '&product_id=' . (int)$spotlightItem['id'] . '&return=' . rawurlencode($pageReturn)) ?>">
                                        <i class="fa-solid fa-circle-info me-2"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shops under this Category Group -->
                <?php foreach ($groupedByCategory[$catKey] as $shopId => $shopData): ?>
                    <div class="shop-section mb-5 position-relative">
                        
                        <!-- Shop Header Card -->
                        <div class="shop-header-card p-3 mb-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm border border-light" style="width: 44px; height: 44px; flex-shrink: 0;">
                                    <?php if(!empty($shopData['shop_info']['logo'])): ?>
                                        <img src="<?= e(media_url($shopData['shop_info']['logo'])) ?>" class="w-100 h-100 rounded-circle object-fit-cover" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid fa-store text-secondary" style="font-size: 1rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="m-0 fw-bold" style="font-family: Impact, sans-serif; color: #1b4332; letter-spacing: 0.5px; font-size: 1.35rem; text-transform: uppercase; line-height: 1.1;">
                                        <?= e($shopData['shop_info']['name']) ?>
                                    </h3>
                                    <span class="text-muted d-block" style="font-family: 'Dancing Script', cursive; font-size: 1.05rem; color: #f39200 !important; line-height: 1;">Proud Partner Seller</span>
                                </div>
                            </div>
                            <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= $shopId ?>&intent=<?= e($catKey) ?>&return=<?= rawurlencode($pageReturn) ?>" class="btn btn-sm text-white fw-bold px-4 py-2 rounded-pill shadow-sm" style="background: #1b4332; font-size: 0.75rem; transition: background 0.2s;" onmouseover="this.style.background='#f39200';" onmouseout="this.style.background='#1b4332';">
                                <i class="fa-regular fa-comment-dots me-1.5"></i> Custom Inquiry
                            </a>
                        </div>
                        
                        <!-- Horizontal Slider Container -->
                        <div class="position-relative">
                            <div class="d-flex overflow-auto gap-3 product-carousel px-1" id="carousel-<?= $catKey ?>-<?= $shopId ?>">
                                <?php foreach ($shopData['products'] as $p): ?>
                                    <div class="product-card-tourism flex-shrink-0 d-flex flex-column hover-lift" style="width: 190px;">
                                        <?php 
                                            $img = $p['image'] ? media_url($p['image'], 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=500&q=80') : 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=500&q=80';
                                            $displayPrice = isset($p['price']) && (float)$p['price'] > 0 ? '₱' . number_format((float)$p['price'], 2) : 'Contact Vendor';
                                        ?>
                                        <a href="#" class="w-100 aspect-ratio-1x1 position-relative overflow-hidden d-block" style="border-radius: 12px 12px 0 0; aspect-ratio: 1/1; background-color: #fcfcfc;"
                                            data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                            data-name="<?= e($p['product_name']) ?>"
                                            data-image="<?= e($img) ?>"
                                            data-price="<?= e($displayPrice) ?>"
                                            data-category="<?= e(product_category_label($p['category'] ?? $catKey)) ?>"
                                            data-type="<?= e(product_type_label($p['product_type'] ?? null)) ?>"
                                            data-availability="<?= e($p['availability'] ?? 'available') ?>"
                                            data-description="<?= e($p['description']) ?>"
                                            data-shop-name="<?= e($p['business_name']) ?>"
                                            data-shop-url="<?= e(vendor_profile_url((int)$p['business_id'], $pageReturn)) ?>"
                                            data-shop-contact="<?= e($p['contact_number'] ?? 'Contact not provided') ?>"
                                            data-shop-address="<?= e($p['address'] ?? 'Vinzons') ?>"
                                            data-inquire-url="<?= e(BASE_URL . 'message.php?business_id=' . (int)$p['business_id'] . '&product_id=' . (int)$p['id'] . '&return=' . rawurlencode($pageReturn)) ?>">
                                            <img src="<?= e($img) ?>" class="object-fit-cover w-100 h-100" style="transition: transform 0.4s ease;" alt="">
                                            <span class="position-absolute bottom-0 start-0 bg-success text-white px-2 py-0.5 m-2 rounded fw-bold shadow-sm" style="font-size: 0.65rem; background-color: #1b4332 !important;">
                                                <?= $displayPrice ?>
                                            </span>
                                        </a>
                                        <div class="p-3 bg-white d-flex flex-column flex-grow-1" style="border-radius: 0 0 12px 12px;">
                                            <span class="text-uppercase text-muted tracking-wider d-block mb-1" style="font-size: 0.58rem; font-weight: 700; letter-spacing: 0.5px;">
                                                <?= e(product_category_label($p['category'] ?? $catKey)) ?> / <?= e(product_type_label($p['product_type'] ?? null)) ?>
                                            </span>
                                            <h6 class="fw-bold mb-1 text-truncate" style="font-family: 'Montserrat', sans-serif; font-size: 0.85rem;" title="<?= e($p['product_name']) ?>">
                                                <a href="#" style="transition: color 0.2s;" class="text-decoration-none text-dark hover-orange"
                                                    data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                                    data-name="<?= e($p['product_name']) ?>"
                                                    data-image="<?= e($img) ?>"
                                                    data-price="<?= e($displayPrice) ?>"
                                                    data-category="<?= e(product_category_label($p['category'] ?? $catKey)) ?>"
                                                    data-type="<?= e(product_type_label($p['product_type'] ?? null)) ?>"
                                                    data-availability="<?= e($p['availability'] ?? 'available') ?>"
                                                    data-description="<?= e($p['description']) ?>"
                                                    data-shop-name="<?= e($p['business_name']) ?>"
                                                    data-shop-url="<?= e(vendor_profile_url((int)$p['business_id'], $pageReturn)) ?>"
                                                    data-shop-contact="<?= e($p['contact_number'] ?? 'Contact not provided') ?>"
                                                    data-shop-address="<?= e($p['address'] ?? 'Vinzons') ?>"
                                                    data-inquire-url="<?= e(BASE_URL . 'message.php?business_id=' . (int)$p['business_id'] . '&product_id=' . (int)$p['id'] . '&return=' . rawurlencode($pageReturn)) ?>"><?= e($p['product_name']) ?></a>
                                            </h6>
                                            <span class="badge align-self-start mb-2 bg-<?= ($p['availability'] ?? '') === 'available' ? 'success' : 'secondary' ?>" style="font-size: 0.62rem;">
                                                <?= e(ucfirst((string) ($p['availability'] ?? 'available'))) ?>
                                            </span>
                                            <p class="text-secondary mb-3 line-clamp-2" style="font-size: 0.7rem; line-height: 1.4; flex-grow: 1;">
                                                <?= e(str_limit((string)$p['description'], 60)) ?>
                                            </p>
                                            
                                            <!-- Action Button Layer dependent on Category Context -->
                                            <button type="button" class="btn btn-outline-success border-1 rounded-pill w-100 py-1 text-center fw-bold mt-auto" style="font-size: 0.68rem; transition: all 0.2s;"
                                                data-bs-toggle="modal" data-bs-target="#productDetailModal"
                                                data-name="<?= e($p['product_name']) ?>"
                                                data-image="<?= e($img) ?>"
                                                data-price="<?= e($displayPrice) ?>"
                                                data-category="<?= e(product_category_label($p['category'] ?? $catKey)) ?>"
                                                data-type="<?= e(product_type_label($p['product_type'] ?? null)) ?>"
                                                data-availability="<?= e($p['availability'] ?? 'available') ?>"
                                                data-description="<?= e($p['description']) ?>"
                                                data-shop-name="<?= e($p['business_name']) ?>"
                                                data-shop-url="<?= e(vendor_profile_url((int)$p['business_id'], $pageReturn)) ?>"
                                                data-shop-contact="<?= e($p['contact_number'] ?? 'Contact not provided') ?>"
                                                data-shop-address="<?= e($p['address'] ?? 'Vinzons') ?>"
                                                data-inquire-url="<?= e(BASE_URL . 'message.php?business_id=' . (int)$p['business_id'] . '&product_id=' . (int)$p['id'] . '&return=' . rawurlencode($pageReturn)) ?>">
                                                <i class="fa-solid fa-circle-info me-1"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Spacer for right padding padding layout alignment -->
                                <?php if(count($shopData['products']) > 3): ?>
                                    <div class="flex-shrink-0" style="width: 15px;"></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Compact Scroll Arrow Overlay Controllers -->
                            <?php if(count($shopData['products']) > 4): ?>
                                <button class="scroll-btn-tourism" onclick="document.getElementById('carousel-<?= $catKey ?>-<?= $shopId ?>').scrollBy({left: 205, behavior: 'smooth'})">
                                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem;"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- LIST OF SHOPS (Local Sellers Ecosystem Directory Section) -->
    <div class="mt-5 pt-5 border-top border-2" style="border-color: rgba(27,67,50,0.1) !important;">
        <div class="text-center mb-5">
            <div class="category-stamp" style="border-color: #f39200; padding: 0.5rem 2.5rem;">
                <h2 class="m-0" style="font-family: Impact, sans-serif; color: #1b4332; letter-spacing: 1.5px; font-size: 1.5rem;">
                    MEET OUR <span style="color: #f39200;">COMMUNITY VENDORS</span>
                </h2>
            </div>
        </div>

        <div class="vendors-grid">
            <?php foreach ($allShops as $shop): ?>
                <?php
                $logo = media_url($shop['logo'] ?? null, asset_url('images/likhalokal-logo.png'));
                $cover = media_url($shop['cover_image'] ?? null, asset_url('images/localbg.png'));
                $profileUrl = vendor_profile_url((int) $shop['id'], current_request_return_url());
                $desc = e(str_limit((string) ($shop['description'] ?? 'Proud partner vendor offering local delicacies and handicrafts in Vinzons.'), 95));
                ?>
                <div class="vendor-card">
                    <div class="vendor-card-banner" style="background-image: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.25)), url('<?= e($cover) ?>');">
                        <div class="vendor-card-logo">
                            <img src="<?= e($logo) ?>" alt="">
                        </div>
                    </div>
                    <div class="vendor-card-body">
                        <span class="vendor-card-badge"><i class="fa-solid fa-store me-1"></i> <?= e($shop['type'] ?: 'Local Seller') ?></span>
                        <h3><a href="<?= e($profileUrl) ?>"><?= e($shop['name']) ?></a></h3>
                        <p><?= $desc ?></p>
                        <div class="vendor-card-footer">
                            <span class="vendor-card-phone">
                                <i class="fa-solid fa-phone"></i>
                                <?= e($shop['contact'] ?: 'No contact') ?>
                            </span>
                            <?php if (is_logged_in()): ?>
                                <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $shop['id'] ?>&return=<?= rawurlencode(current_request_return_url()) ?>" class="vendor-msg-btn text-decoration-none d-inline-block text-center">Message</a>
                            <?php else: ?>
                                <button type="button" class="vendor-msg-btn" data-require-auth>Message</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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

<?php require BASE_PATH . '/includes/footer.php'; ?>
