<?php

declare(strict_types=1);

$pageTitle = 'Home';
$activeNav = 'home';
require_once dirname(__DIR__) . '/bootstrap.php';

$featured = db()->query(
    "SELECT p.*, b.business_name FROM products p JOIN businesses b ON b.id = p.business_id
     WHERE b.status = 'approved' AND p.is_featured = 1 ORDER BY p.updated_at DESC LIMIT 5"
)->fetchAll();

$spots = db()->query(
    "SELECT * FROM tourist_attractions WHERE status = 'published' ORDER BY id ASC LIMIT 3"
)->fetchAll();

$events = db()->query(
    "SELECT * FROM events WHERE status = 'published' ORDER BY event_date ASC LIMIT 4"
)->fetchAll();

$announcements = db()->query(
    "SELECT * FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$err = flash('error');
$ok = flash('success');
?>
<?php if ($err): ?>
    <div class="container mt-3"><div class="alert alert-danger"><?= e($err) ?></div></div>
<?php endif; ?>
<?php if ($ok): ?>
    <div class="container mt-3"><div class="alert alert-success"><?= e($ok) ?></div></div>
<?php endif; ?>

<!-- Custom Styles for Wow Impact Blue Aesthetic -->
<style>
body {
    background-color: #f4f7f6;
    overflow-x: hidden;
}
.hero-text-shadow {
    text-shadow: 3px 3px 15px rgba(0,0,0,0.9);
}

/* Glassmorphism Buttons */
.btn-glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: bold;
    transition: all 0.4s ease;
}
.btn-glass:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    color: #fff;
}
.btn-glow-orange {
    background: linear-gradient(135deg, #f39200, #ffb347);
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: bold;
    transition: all 0.4s ease;
    box-shadow: 0 4px 15px rgba(243, 146, 0, 0.4);
}
.btn-glow-orange:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 25px rgba(243, 146, 0, 0.6);
    color: #fff;
}

/* Premium Cards */
.tourism-product-card {
    border-radius: 16px; border: none; background: #fff;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.tourism-product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,31,63,0.15) !important;
}

.tourist-spot-card {
    border-radius: 20px; overflow: hidden; position: relative;
    transition: all 0.4s ease;
}
.tourist-spot-card::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,31,63,0.9), transparent 60%); z-index: 1;
    opacity: 0.8; transition: opacity 0.4s;
}
.tourist-spot-card:hover::before { opacity: 1; }
.tourist-spot-card:hover { transform: scale(1.03); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
.tourist-spot-card img { transition: transform 0.7s ease; }
.tourist-spot-card:hover img { transform: scale(1.12); }

.spot-badge {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.4);
    color: #fff;
    transition: all 0.3s;
}
.tourist-spot-card:hover .spot-badge {
    background: #f39200;
    border-color: #f39200;
    color: #fff;
    transform: translateY(-5px);
}

.tourist-spot-card-modern {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    display: flex;
    flex-direction: column;
}
.tourist-spot-card-modern:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
}
.tourist-spot-card-modern .card-img-wrap {
    height: 250px;
    overflow: hidden;
    position: relative;
}
.tourist-spot-card-modern .card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}
.tourist-spot-card-modern:hover .card-img-wrap img {
    transform: scale(1.1);
}
.tourist-spot-card-modern .card-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 31, 63, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.4s ease;
}
.tourist-spot-card-modern:hover .card-hover-overlay {
    opacity: 1;
}
.tourist-spot-card-modern .card-title-wrap {
    padding: 1.2rem 1rem;
    text-align: center;
    background: #fff;
    color: #001F3F;
}

/* Entrepreneurs Banner */
.entrepreneurs-banner {
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.18);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.4s ease;
}
.entrepreneurs-banner:hover {
    transform: translateY(-8px);
}
.entrepreneurs-inner {
    background: url('https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
    border-radius: 18px;
    position: relative;
}
.entrepreneurs-inner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(0, 31, 63, 0.95), rgba(0, 31, 63, 0.7));
    border-radius: 18px;
}

/* Animations */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}
.floating-element {
    animation: float 6s ease-in-out infinite;
}
@keyframes pulseGlow {
    0% { box-shadow: 0 0 0 0 rgba(243, 146, 0, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(243, 146, 0, 0); }
    100% { box-shadow: 0 0 0 0 rgba(243, 146, 0, 0); }
}
.pulse-btn {
    animation: pulseGlow 2s infinite;
}
@keyframes bounceDown {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-15px); }
    60% { transform: translateY(-7px); }
}
.scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 2rem;
    animation: bounceDown 2s infinite;
    opacity: 0.8;
}

/* Scroll Reveal */
.reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
}
.reveal.active {
    opacity: 1;
    transform: translateY(0);
}
.delay-1 { transition-delay: 0.1s; }
.delay-2 { transition-delay: 0.2s; }
.delay-3 { transition-delay: 0.3s; }

/* Event Image Stack */
.event-img-main {
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    z-index: 2;
    position: relative;
    transition: transform 0.4s;
}
.event-img-main:hover {
    transform: scale(1.03);
    z-index: 5;
}
.event-img-side-1 {
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    margin-top: -40px;
    margin-left: 20px;
    position: relative;
    z-index: 3;
    border: 4px solid #fff;
    transition: transform 0.4s;
}
.event-img-side-1:hover {
    transform: scale(1.05) rotate(-3deg);
    z-index: 5;
}
.event-img-side-2 {
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    margin-top: -20px;
    margin-left: -20px;
    position: relative;
    z-index: 1;
    border: 4px solid #fff;
    transition: transform 0.4s;
}
.event-img-side-2:hover {
    transform: scale(1.05) rotate(3deg);
    z-index: 5;
}
</style>

<!-- Immersive Hero Section -->
<section class="position-relative vh-100 d-flex align-items-center overflow-hidden" style="background-image:url('<?= e(ASSET_URL) ?>images/landing-picture.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="position-absolute inset-0 w-100 h-100" style="background: linear-gradient(to right, rgba(0,31,63,0.8) 0%, rgba(0,0,0,0.2) 100%); z-index: 1;"></div>
    
    <!-- Floating subtle elements -->
    <div class="position-absolute rounded-circle floating-element" style="width: 300px; height: 300px; background: rgba(243,146,0,0.15); filter: blur(60px); top: 10%; right: 15%; z-index: 2;"></div>
    
    <div class="container position-relative z-3 pt-5 mt-5">
        <div class="row">
            <div class="col-lg-8 col-xl-7 text-white reveal active">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 2px;">CAMARINES NORTE, BICOL, PHILIPPINES</span>
                <h1 class="display-1 fw-bold mb-3 hero-text-shadow" style="font-family: Impact, sans-serif; letter-spacing: 2px; line-height: 1.1;">
                    MADYA NA SA<br><span style="color: #f39200;">VINZONS!</span>
                </h1>
                <p class="mb-5 hero-text-shadow" style="font-family: 'Dancing Script', cursive; font-size: 3.5rem; line-height: 1.2; text-shadow: 2px 2px 5px rgba(0,0,0,0.5);">
                    "Where Adventure Meets Local Culture."
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= e(BASE_URL) ?>tourism.php" class="btn btn-glow-orange pulse-btn"><i class="fa-solid fa-map-location-dot me-2"></i> Explore Tourism</a>
                    <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-glass"><i class="fa-solid fa-bag-shopping me-2"></i> Shop Local</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <i class="fa-solid fa-chevron-down"></i>
    </div>
</section>

<!-- Entrepreneurs Banner -->
<section class="py-5 mt-2 reveal">
    <div class="container">
        <div class="entrepreneurs-banner p-1">
            <div class="entrepreneurs-inner p-4 p-md-5">
                <div class="position-relative z-1 d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
                    <div class="text-white flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 40px; height: 3px; background: #f39200; margin-right: 15px;"></div>
                            <span class="text-warning fw-bold" style="letter-spacing: 2px;">FOR CREATORS & SELLERS</span>
                        </div>
                        <h2 class="fw-bold mb-3" style="font-family: Impact, sans-serif; letter-spacing: 1px; font-size: 3rem;">
                            ATTENTION <span style="color: #f39200;">LOCAL ENTREPRENEURS!</span>
                        </h2>
                        <p class="fs-5 mb-0 text-light" style="line-height: 1.6; max-width: 800px; font-family: 'Montserrat', sans-serif;">
                            Join the LikhaLokal digital marketplace! Showcase your authentic Vinzons products, 
                            reach more tourists, and grow your business with a stunning digital storefront.
                        </p>
                    </div>
                    <div class="flex-shrink-0 text-center text-md-end">
                        <a href="<?= e(BASE_URL) ?>register.php" class="btn btn-glow-orange pulse-btn px-5 py-3 fs-5">
                            Register Your Business <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Discover More Divider -->
<section class="py-2 reveal delay-1">
    <div class="container">
        <div class="d-flex align-items-center justify-content-center">
            <div style="flex:1; border-top: 2px solid rgba(0,31,63,0.2); position: relative;">
                <div style="position:absolute; right:0; top:-3px; width:8px; height:8px; border-radius:50%; background:#001F3F;"></div>
            </div>
            <div class="mx-4 text-center d-flex align-items-center flex-column position-relative">
                <span style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #001F3F; margin-bottom: -15px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Discover</span>
                <span style="font-family: Impact, sans-serif; font-size: 3.8rem; color: #f39200; letter-spacing: 3px; text-shadow: 2px 2px 0px rgba(243,146,0,0.2);">MORE</span>
            </div>
            <div style="flex:1; border-top: 2px solid rgba(0,31,63,0.2); position: relative;">
                <div style="position:absolute; left:0; top:-3px; width:8px; height:8px; border-radius:50%; background:#001F3F;"></div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Local Products -->
<section class="py-5 position-relative" style="background: radial-gradient(circle at top right, #ffffff 0%, #f4f7f6 100%);">
    <!-- Subtle Background Graphic -->
    <i class="fa-solid fa-basket-shopping position-absolute text-secondary" style="font-size: 25rem; top: 10%; right: -5%; opacity: 0.03; z-index: 0; transform: rotate(-15deg);"></i>
    
    <div class="container position-relative z-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5 gap-3 reveal">
            <div>
                <span class="badge bg-soft text-primary mb-2 px-3 py-2 rounded-pill fw-bold shadow-sm" style="background: #fff; color: #f39200 !important; border: 1px solid #f39200;"><i class="fa-solid fa-medal me-2"></i> QUALITY CRAFTED</span>
                <h2 class="mb-2 mt-2" style="font-family: Impact, sans-serif; color: #001F3F; font-size: 3.5rem; letter-spacing: 1px;">FEATURED <span style="color: #f39200;">PRODUCTS</span></h2>
                <p class="fs-5 text-secondary fw-medium m-0" style="font-family: 'Montserrat', sans-serif; max-width: 600px;">
                    Discover authentic local goods — from handcrafted souvenirs to fresh agricultural produce. Connect directly with Vinzons artisans to order.
                </p>
            </div>
            <div>
                <a href="<?= e(BASE_URL) ?>products.php" class="btn text-white fw-bold px-5 py-3 d-inline-flex align-items-center shadow-sm" style="background: #001F3F; border-radius: 50px; transition: all 0.3s;" onmouseover="this.style.background='#f39200'; this.style.transform='translateY(-3px)'" onmouseout="this.style.background='#001F3F'; this.style.transform='translateY(0)'">
                    View All Collection <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- First Large Item -->
            <?php if (!empty($featured[0])): $p = $featured[0]; $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
            <div class="col-lg-6 reveal delay-1">
                <div class="card h-100 tourism-product-card shadow-lg position-relative overflow-hidden group" style="border-radius: 24px;">
                    <img src="<?= e($img) ?>" class="object-fit-cover w-100 h-100" style="min-height: 500px; transition: transform 0.8s ease;" alt="<?= e($p['product_name']) ?>" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                    
                    <div class="position-absolute top-0 start-0 m-4 z-3">
                        <span class="badge rounded-pill px-4 py-2 shadow" style="background: rgba(255,255,255,0.9); color: #001F3F; font-size: 0.85rem;"><i class="fa-solid fa-crown text-warning me-1"></i> Artisan Spotlight</span>
                    </div>
                    
                    <!-- Glassmorphism Details Card -->
                    <div class="position-absolute bottom-0 start-0 w-100 p-4 m-3 z-3" style="width: calc(100% - 2rem) !important;">
                        <div class="p-4 rounded-4 shadow-lg" style="background: rgba(0, 31, 63, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.2);">
                            <h3 class="fw-bold mb-2 text-white" style="font-family: 'Montserrat', sans-serif; font-size: 1.8rem;"><?= e($p['product_name']) ?></h3>
                            <p class="text-light mb-4" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.95rem; opacity: 0.9;">
                                <?= e((string) $p['description']) ?>
                            </p>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-warning fw-bold"><i class="fa-solid fa-store me-2"></i><?= e((string) $p['business_name']) ?></div>
                                <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $p['business_id'] ?>" class="btn btn-glow-orange fw-bold px-4 py-2 rounded-pill">Inquire Now <i class="fa-regular fa-comment-dots ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-lg-6">
                <div class="row g-4">
                    <?php foreach (array_slice($featured, 1, 4) as $idx => $p): ?>
                        <div class="col-md-6 reveal delay-<?= ($idx % 2) + 1 ?>">
                            <div class="card h-100 tourism-product-card shadow-sm position-relative overflow-hidden" style="border-radius: 20px;">
                                <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                                <div class="overflow-hidden position-relative" style="height: 220px;">
                                    <img src="<?= e($img) ?>" class="object-fit-cover w-100 h-100" style="transition: transform 0.6s ease;" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'" alt="<?= e($p['product_name']) ?>">
                                    <div class="position-absolute top-0 end-0 m-3">
                                        <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.color='#dc3545'; this.style.transform='scale(1.1)'" onmouseout="this.style.color='#ccc'; this.style.transform='scale(1)'">
                                            <i class="fa-solid fa-heart text-secondary"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-white d-flex flex-column" style="height: 160px;">
                                    <h5 class="fw-bold mb-1 text-truncate" style="color: #001F3F; font-size: 1.15rem;"><?= e($p['product_name']) ?></h5>
                                    <p class="small text-muted mb-auto text-truncate" style="font-size: 0.9rem;"><i class="fa-solid fa-store me-1 text-warning"></i> <?= e((string) $p['business_name']) ?></p>
                                    
                                    <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
                                        <span class="fw-bold" style="font-size: 0.9rem; color: #17a2b8;"><i class="fa-regular fa-comments me-1"></i> Message Seller</span>
                                        <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $p['business_id'] ?>" class="btn btn-sm text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; background: #001F3F; transition: all 0.3s;" onmouseover="this.style.background='#f39200'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='#001F3F'; this.style.transform='scale(1)'">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Top Tourist Spots -->
<section class="position-relative overflow-hidden" style="background: url('<?= e(ASSET_URL) ?>images/landing-picture.png') center/cover fixed; padding-top: 120px; padding-bottom: 90px;">
    <!-- Mountain Cutout Top Divider -->
    <div class="position-absolute top-0 start-0 w-100 overflow-hidden" style="line-height: 0; z-index: 3;">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="display: block; width: 100%; height: 80px;">
            <polygon points="0,0 1200,0 1200,40 1000,10 750,60 500,20 250,70 0,30" fill="#ffffff" />
        </svg>
    </div>
    
    <!-- Warmer travel-themed overlay -->
    <div class="position-absolute inset-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(255,255,255,0.85) 0%, rgba(230,243,255,0.95) 100%); z-index: 1;"></div>
    
    <div class="container position-relative z-2 py-4">
        <div class="text-center mb-5 reveal position-relative">
            <!-- Giant subtle compass background graphic -->
            <i class="fa-solid fa-compass position-absolute text-primary" style="font-size: 12rem; top: -60px; left: 50%; transform: translateX(-50%); opacity: 0.05; z-index: -1;"></i>
            
            <span class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3 fw-bold shadow-sm" style="background: rgba(243,146,0,0.15); color: #001F3F; border: 1px solid rgba(243,146,0,0.3); font-size: 0.85rem; letter-spacing: 2px;">
                <i class="fa-solid fa-map-location-dot me-2 text-warning fs-5"></i> DISCOVER VINZONS
            </span>
            <h2 class="display-4 fw-bold" style="font-family: Impact, sans-serif; letter-spacing: 2px;">
                <span style="color: #001F3F;">TOP</span> <span style="color: #f39200;">TOURIST SPOTS</span>
            </h2>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php foreach (array_slice($spots, 0, 3) as $idx => $s): ?>
                <div class="col-md-4 reveal delay-<?= $idx + 1 ?>">
                    <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $s['id'] ?>" class="text-decoration-none d-block h-100">
                        <div class="tourist-spot-card-modern shadow-lg h-100 w-100 position-relative">
                            <div class="card-img-wrap">
                                <?php $sim = $s['image'] ? asset_url($s['image']) : 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80'; ?>
                                <img src="<?= e($sim) ?>" alt="<?= e($s['attraction_name']) ?>">
                                
                                <!-- Travel Badges -->
                                <?php if ($idx === 0): ?>
                                <div class="position-absolute top-0 start-0 m-3 z-3">
                                    <span class="badge bg-danger rounded-pill px-3 py-2 shadow-sm" style="font-size: 0.75rem;"><i class="fa-solid fa-fire me-1"></i> Popular</span>
                                </div>
                                <?php elseif ($idx === 1): ?>
                                <div class="position-absolute top-0 start-0 m-3 z-3">
                                    <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm" style="font-size: 0.75rem;"><i class="fa-solid fa-leaf me-1"></i> Nature</span>
                                </div>
                                <?php else: ?>
                                <div class="position-absolute top-0 start-0 m-3 z-3">
                                    <span class="badge bg-info rounded-pill px-3 py-2 shadow-sm text-dark" style="font-size: 0.75rem;"><i class="fa-solid fa-camera me-1"></i> Scenic</span>
                                </div>
                                <?php endif; ?>

                                <div class="card-hover-overlay">
                                    <span class="btn btn-glow-orange rounded-pill px-4 py-2 fw-bold text-white"><i class="fa-solid fa-route me-2"></i> View Details</span>
                                </div>
                            </div>
                            <div class="card-title-wrap d-flex flex-column justify-content-center py-4" style="min-height: 120px;">
                                <h5 class="mb-2 fw-bold text-truncate" style="font-size: 1.15rem; letter-spacing: 0.5px;"><?= e($s['attraction_name']) ?></h5>
                                <div class="text-secondary d-flex align-items-center justify-content-center" style="font-size: 0.85rem; font-family: 'Montserrat', sans-serif;">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i> Vinzons, Camarines Norte
                                </div>
                                <div class="mt-2" style="color: #f39200; font-size: 0.8rem;">
                                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                                    <span class="text-muted ms-1">(4.8)</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5 pt-4 reveal delay-2">
            <p class="text-dark fw-medium mb-3" style="font-family: 'Montserrat', sans-serif; text-shadow: 1px 1px 3px rgba(255,255,255,0.8); font-size: 1.1rem;">
                Explore scenic spots, historical landmarks, and hidden gems in Vinzons.<br>
                Your next adventure starts here.
            </p>
            <a href="<?= e(BASE_URL) ?>tourism.php" class="btn fw-bold px-5 py-3 rounded-pill mt-2 shadow-sm" style="background: #001F3F; color: white; transition: all 0.3s; border: 2px solid #001F3F;" onmouseover="this.style.background='transparent'; this.style.color='#001F3F';">
                <i class="fa-solid fa-map me-2"></i> See All Destinations
            </a>
        </div>
    </div>
</section>

<!-- Events & Culture Section -->
<section class="py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #fffdf8 0%, #ffeedb 100%);">
    <!-- Banderitas SVG Divider -->
    <div class="position-absolute top-0 start-0 w-100 overflow-hidden" style="line-height: 0; z-index: 1; pointer-events: none;">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="display: block; width: 100%; height: 70px; opacity: 0.8;">
            <path d="M0,20 Q150,60 300,20 Q450,60 600,20 Q750,60 900,20 Q1050,60 1200,20" stroke="rgba(0,0,0,0.1)" fill="none" stroke-width="2" />
            <polygon points="50,30 70,80 90,30" fill="#f39200" />
            <polygon points="150,45 170,95 190,45" fill="#dc3545" />
            <polygon points="250,30 270,80 290,30" fill="#28a745" />
            <polygon points="350,45 370,95 390,45" fill="#17a2b8" />
            <polygon points="450,30 470,80 490,30" fill="#f39200" />
            <polygon points="550,45 570,95 590,45" fill="#dc3545" />
            <polygon points="650,30 670,80 690,30" fill="#28a745" />
            <polygon points="750,45 770,95 790,45" fill="#17a2b8" />
            <polygon points="850,30 870,80 890,30" fill="#f39200" />
            <polygon points="950,45 970,95 990,45" fill="#dc3545" />
            <polygon points="1050,30 1070,80 1090,30" fill="#28a745" />
            <polygon points="1150,45 1170,95 1190,45" fill="#17a2b8" />
        </svg>
    </div>

    <!-- Festive floating confetti dots -->
    <div class="position-absolute rounded-circle floating-element" style="width: 20px; height: 20px; background: #dc3545; top: 15%; right: 10%; z-index: 1;"></div>
    <div class="position-absolute rounded-circle floating-element delay-1" style="width: 15px; height: 15px; background: #28a745; top: 40%; left: 5%; z-index: 1;"></div>
    <div class="position-absolute rounded-circle floating-element delay-2" style="width: 25px; height: 25px; background: #17a2b8; bottom: 20%; right: 45%; z-index: 1;"></div>
    <div class="position-absolute rounded-circle floating-element delay-3" style="width: 12px; height: 12px; background: #f39200; bottom: 10%; left: 40%; z-index: 1;"></div>

    <div class="container py-5 position-relative z-2">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal pe-lg-5">
                <div class="position-relative mt-4 mb-5">
                    <!-- Massive diffuse multi-color glow -->
                    <div class="position-absolute" style="width: 60%; height: 60%; background: #f39200; top: 0%; left: 0%; opacity: 0.4; filter: blur(70px); z-index: 0; border-radius: 50%;"></div>
                    <div class="position-absolute" style="width: 60%; height: 60%; background: #dc3545; bottom: -10%; right: 10%; opacity: 0.3; filter: blur(70px); z-index: 0; border-radius: 50%;"></div>
                    
                    <!-- Main Image -->
                    <div class="position-relative z-2" style="border-radius: 16px; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1000&q=80" class="w-100 shadow-lg" style="height: 420px; object-fit: cover; border-radius: 16px;" alt="Festival Main">
                        
                        <!-- Floating Event Date Badge -->
                        <div class="position-absolute bg-white shadow-lg p-3 rounded-4 z-3" style="top: -20px; right: -20px; transform: rotate(8deg); border-left: 6px solid #dc3545; transition: transform 0.3s;" onmouseover="this.style.transform='rotate(0deg) scale(1.1)'" onmouseout="this.style.transform='rotate(8deg) scale(1)'">
                            <div class="text-center">
                                <span class="d-block text-danger fw-bold" style="font-size: 0.85rem; letter-spacing: 2px;">MAY</span>
                                <span class="d-block fw-bold" style="font-size: 2rem; color: #001F3F; line-height: 1;">28</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overlapping Image 1 (Left) -->
                    <div class="position-absolute" style="bottom: -50px; left: -20px; z-index: 3; width: 55%; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.05) rotate(-3deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'">
                        <img src="https://images.unsplash.com/photo-1543807535-eceef0bc6599?auto=format&fit=crop&w=600&q=80" class="w-100 shadow-lg" style="height: 200px; object-fit: cover; border-radius: 12px; border: 6px solid #fff;" alt="Festival Crowd">
                    </div>

                    <!-- Overlapping Image 2 (Right) -->
                    <div class="position-absolute" style="bottom: 20px; right: -40px; z-index: 3; width: 45%; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.05) rotate(5deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'">
                        <img src="https://images.unsplash.com/photo-1533613220915-609f661a6fe1?auto=format&fit=crop&w=600&q=80" class="w-100 shadow-lg" style="height: 180px; object-fit: cover; border-radius: 12px; border: 5px solid #fff;" alt="Festival Mask">
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 ps-lg-5 reveal delay-1 mt-5 mt-lg-0">
                <div class="d-inline-flex align-items-center mb-3 px-4 py-2 rounded-pill fw-bold shadow-sm" style="background: #fff; color: #dc3545; border: 2px solid #ffeedb; font-size: 0.85rem; letter-spacing: 1px;">
                    <i class="fa-solid fa-masks-theater me-2"></i> CULTURE & HERITAGE
                </div>
                
                <h2 class="fw-bold mb-0" style="font-family: Impact, sans-serif; letter-spacing: 2px; color: #f39200; line-height: 0.85; font-size: 4.5rem; text-transform: uppercase;">
                    EVENTS &
                </h2>
                <h2 class="fw-bold mb-4" style="font-family: Impact, sans-serif; letter-spacing: 2px; color: #001F3F; line-height: 0.85; font-size: 4.5rem; text-transform: uppercase;">
                    FESTIVALS
                </h2>
                
                <h3 class="fw-bold mb-3 mt-4" style="color: #001F3F; font-family: 'Montserrat', sans-serif; font-size: 2.2rem;">Tacboan Festival</h3>
                
                <p class="fs-5 text-secondary mb-5" style="line-height: 1.7; font-family: 'Montserrat', sans-serif;">
                    Experience the rhythm, vibrant colors, and deep-rooted traditions of Vinzons. Celebrate our history, honoring our founding and local hero, Wenceslao Q. Vinzons through music, dance, and authentic local cuisine.
                </p>
                
                <ul class="list-unstyled mb-5 text-dark">
                    <li class="mb-4 d-flex align-items-start p-3 rounded-4" style="background: rgba(255,255,255,0.6); transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.background='rgba(255,255,255,0.6)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <div class="me-3 mt-1 shadow-sm" style="background: #dc3545; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-calendar-days text-white" style="font-size: 0.9rem;"></i>
                        </div>
                        <div><strong style="color: #001F3F; font-family: 'Montserrat', sans-serif; font-size: 1.15rem;">Annually in May</strong><br><span class="text-secondary" style="font-family: 'Montserrat', sans-serif; font-size: 0.95rem;">Cultural festival activities typically peak in May leading to the feast of St. Peter.</span></div>
                    </li>
                    <li class="mb-4 d-flex align-items-start p-3 rounded-4" style="background: rgba(255,255,255,0.6); transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.background='rgba(255,255,255,0.6)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <div class="me-3 mt-1 shadow-sm" style="background: #28a745; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-book-open-reader text-white" style="font-size: 0.9rem;"></i>
                        </div>
                        <div><strong style="color: #001F3F; font-family: 'Montserrat', sans-serif; font-size: 1.15rem;">Rich History</strong><br><span class="text-secondary" style="font-family: 'Montserrat', sans-serif; font-size: 0.95rem;">Celebrating the town's founding in 1581 and its rich agricultural heritage.</span></div>
                    </li>
                    <li class="mb-4 d-flex align-items-start p-3 rounded-4" style="background: rgba(255,255,255,0.6); transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.transform='translateX(10px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.background='rgba(255,255,255,0.6)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <div class="me-3 mt-1 shadow-sm" style="background: #f39200; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-monument text-white" style="font-size: 0.9rem;"></i>
                        </div>
                        <div><strong style="color: #001F3F; font-family: 'Montserrat', sans-serif; font-size: 1.15rem;">Hero's Legacy</strong><br><span class="text-secondary" style="font-family: 'Montserrat', sans-serif; font-size: 0.95rem;">The former residence of patriot Wenceslao 'Bintao' Q. Vinzons houses a public library and museum.</span></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    var reveals = document.querySelectorAll(".reveal");
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add("active");
            }
        });
    }, { threshold: 0.1 });
    
    reveals.forEach(function(reveal) {
        observer.observe(reveal);
    });
});
</script>
';
require BASE_PATH . '/includes/footer.php';
