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

<!-- Floating Icons Background -->
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
/* Premium Cards */
.tourism-product-card {
    border-radius: 16px; border: none; background: #fff;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.tourism-product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(27,67,50,0.15) !important;
}
.tourist-spot-card {
    border-radius: 20px; overflow: hidden; position: relative;
    transition: all 0.4s ease;
}
.tourist-spot-card::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(27,67,50,0.9), transparent); z-index: 1;
    opacity: 0.8; transition: opacity 0.3s;
}
.tourist-spot-card:hover::before { opacity: 1; }
.tourist-spot-card:hover { transform: scale(1.03); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.tourist-spot-card img { transition: transform 0.6s ease; }
.tourist-spot-card:hover img { transform: scale(1.1); }

/* Bento Box Tourism Layout */
.tourist-bento-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: 1fr;
}
.bento-item {
    height: 300px;
}
@media (min-width: 768px) {
    .tourist-bento-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 992px) {
    .tourist-bento-grid {
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: 250px;
    }
    .bento-item { height: 100%; }
    .bento-item:nth-child(1) { grid-column: span 2; grid-row: span 2; } /* Large Hero Spot */
    .bento-item:nth-child(2) { grid-column: span 2; grid-row: span 1; } /* Wide Spot Top Right */
    .bento-item:nth-child(3) { grid-column: span 1; grid-row: span 1; } /* Small Square */
    .bento-item:nth-child(4) { grid-column: span 1; grid-row: span 1; } /* Small Square */
    .bento-item:nth-child(5) { grid-column: span 2; grid-row: span 1; } /* Wide Bottom Left */
    .bento-item:nth-child(6) { grid-column: span 2; grid-row: span 1; } /* Wide Bottom Right */
}
</style>

<div class="floating-bg-icons">
    <i class="fa-solid fa-sun float-icon" style="top: 15%; left: 8%; animation-duration: 25s; font-size: 5rem; color: rgba(243,146,0,0.05);"></i>
    <i class="fa-solid fa-leaf float-icon" style="top: 60%; right: 5%; animation-duration: 20s; font-size: 4rem; color: rgba(27,67,50,0.04);"></i>
    <i class="fa-solid fa-basket-shopping float-icon" style="top: 85%; left: 15%; animation-duration: 18s; font-size: 3.5rem; color: rgba(243,146,0,0.05);"></i>
</div>

<!-- Immersive Hero Section -->
<section class="position-relative vh-100 d-flex align-items-center" style="background-image:url('<?= e(ASSET_URL) ?>images/landing-picture.png'); background-size: cover; background-position: center;">
    <div class="position-absolute inset-0 w-100 h-100" style="background-image:url('<?= e(ASSET_URL) ?>images/landing-layer.png'); background-size: cover; background-position: center; z-index: 1;"></div>
    <div class="position-absolute inset-0 w-100 h-100" style="background: linear-gradient(to right, rgba(27,67,50,0.9) 0%, rgba(0,0,0,0.4) 100%); z-index: 2;"></div>
    
    <div class="container position-relative z-3 pt-5 mt-5">
        <div class="row">
            <div class="col-lg-8 col-xl-7 text-white">
                <div class="d-inline-flex align-items-center bg-white bg-opacity-10 rounded-pill px-4 py-2 mb-4 border border-white border-opacity-25" style="backdrop-filter: blur(10px);">
                    <i class="fa-solid fa-location-dot text-warning me-2"></i>
                    <span class="fw-bold" style="letter-spacing: 2px; font-family: 'Montserrat', sans-serif;">CAMARINES NORTE, PHILIPPINES</span>
                </div>
                <h1 class="display-1 fw-bold mb-3" style="font-family: Impact, sans-serif; letter-spacing: 3px; line-height: 1; text-shadow: 4px 4px 15px rgba(0,0,0,0.5);">
                    MADYA<br>NA SA<br><span style="color: #f39200;">VINZONS!</span>
                </h1>
                <p class="mb-5" style="font-family: 'Dancing Script', cursive; font-size: 3rem; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); line-height: 1.2;">
                    "Discover Vinzons, Where Adventure Meets Local Culture."
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(BASE_URL) ?>tourism.php" class="btn fw-bold px-5 py-3 rounded-pill shadow-lg text-white" style="background: #f39200; font-family: 'Montserrat', sans-serif; transition: all 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fa-solid fa-map-location-dot me-2"></i> Explore Tourism
                    </a>
                    <a href="<?= e(BASE_URL) ?>products.php" class="btn bg-white bg-opacity-10 border-white text-white fw-bold px-5 py-3 rounded-pill shadow-lg" style="backdrop-filter: blur(5px); font-family: 'Montserrat', sans-serif; transition: all 0.3s;" onmouseover="this.style.background='white'; this.style.color='#1b4332';" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.color='white';">
                        <i class="fa-solid fa-bag-shopping me-2"></i> Shop Local
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Glassmorphism Entrepreneurs Banner -->
<section class="py-5 position-relative" style="background: url('https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1600&q=80') center/cover fixed;">
    <div class="position-absolute w-100 h-100 top-0 start-0" style="background: rgba(27,67,50,0.85);"></div>
    <div class="container position-relative z-1 py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="p-5 rounded-4 shadow-lg text-center" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.2);">
                    <div class="badge rounded-pill bg-warning text-dark px-4 py-2 mb-3 fw-bold fs-6 shadow-sm" style="font-family: 'Montserrat', sans-serif; letter-spacing: 2px;">CALLING ALL SELLERS</div>
                    <h2 class="display-4 fw-bold text-white mb-4" style="font-family: Impact, sans-serif; letter-spacing: 2px;">ATTENTION <span style="color: #f39200;">LOCAL ENTREPRENEURS!</span></h2>
                    <p class="text-white mx-auto fs-5 mb-5" style="max-width: 800px; line-height: 1.8;">
                        Register your business on LikhaLokal to reach tourists and residents with a digital storefront. Showcase your beautiful products, receive direct inquiries, and grow your business in a digitally connected world.
                    </p>
                    <a href="<?= e(BASE_URL) ?>register.php" class="btn btn-light text-success fw-bold px-5 py-3 rounded-pill shadow-lg fs-5" style="color: #1b4332 !important; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fa-solid fa-store me-2"></i> Register Your Business Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Local Products -->
<section class="py-5 position-relative">
    <div class="container py-5">
        <!-- Section Header -->
        <div class="d-flex align-items-center justify-content-center mb-5">
            <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.3);"></div>
            <div class="mx-4 text-center d-flex align-items-center flex-column position-relative">
                <span style="font-family: 'Dancing Script', cursive; font-size: 2.2rem; color: #1b4332; margin-bottom: -10px;">Authentic</span>
                <span style="font-family: Impact, sans-serif; font-size: 3.5rem; color: #f39200; letter-spacing: 3px; text-shadow: 2px 2px 0px rgba(243,146,0,0.2);">LOCAL GOODS</span>
            </div>
            <div style="flex:1; border-top: 2px dashed rgba(27,67,50,0.3);"></div>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <p class="fs-5 text-dark fw-medium" style="font-family: 'Montserrat', sans-serif;">From handcrafted souvenirs to fresh agricultural produce, discover the finest items crafted by the Vinzons community.</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach (array_slice($featured, 0, 4) as $p): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 tourism-product-card shadow-sm d-flex flex-column">
                        <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                        <div class="position-relative overflow-hidden" style="border-radius: 16px 16px 0 0;">
                            <div class="ratio ratio-1x1">
                                <img src="<?= e($img) ?>" class="object-fit-cover w-100 h-100" alt="<?= e($p['product_name']) ?>">
                            </div>
                            <div class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark shadow-sm rounded-pill px-3 py-2 z-3"><i class="fa-solid fa-star me-1"></i> Featured</div>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h5 class="fw-bold mb-2" style="font-family: 'Montserrat', sans-serif; color: #1b4332;"><?= e($p['product_name']) ?></h5>
                            <p class="small text-muted mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= e((string) $p['description']) ?>
                            </p>
                            <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $p['business_id'] ?>" class="btn w-100 fw-bold shadow-sm" style="background: rgba(27,67,50,0.08); color: #1b4332; border-radius: 10px;">
                                <i class="fa-solid fa-shop me-2 text-warning"></i> View Shop
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?= e(BASE_URL) ?>products.php" class="btn text-white fw-bold px-5 py-3 rounded-pill shadow" style="background: #1b4332; font-family: 'Montserrat', sans-serif; transition: background 0.3s;" onmouseover="this.style.background='#f39200'" onmouseout="this.style.background='#1b4332'">
                Explore All Products <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Top Tourist Spots Gallery -->
<section class="py-5" style="background: rgba(27,67,50,0.03);">
    <div class="container py-5">
        <div class="text-center mb-5 position-relative">
            <h2 class="display-4 fw-bold" style="font-family: Impact, sans-serif; color: #1b4332; letter-spacing: 2px;">
                TOP <span style="color: #f39200;">TOURIST SPOTS</span>
            </h2>
            <p class="fs-5 text-muted fw-medium mt-3" style="font-family: 'Montserrat', sans-serif;">Explore scenic spots, historical landmarks, and hidden gems in Vinzons.</p>
        </div>
        
        <div class="tourist-bento-grid">
            <?php foreach (array_slice($spots, 0, 6) as $s): ?>
                <div class="bento-item">
                    <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $s['id'] ?>" class="text-decoration-none d-block h-100">
                        <div class="tourist-spot-card shadow-sm h-100 w-100">
                            <?php $sim = $s['image'] ? asset_url($s['image']) : 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80'; ?>
                            <img src="<?= e($sim) ?>" class="w-100 h-100 object-fit-cover" alt="<?= e($s['attraction_name']) ?>">
                            <div class="position-absolute bottom-0 start-0 w-100 p-4 z-3 text-white">
                                <h4 class="fw-bold mb-2 shadow-sm" style="font-family: 'Montserrat', sans-serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);"><?= e($s['attraction_name']) ?></h4>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning text-dark rounded-pill shadow-sm"><i class="fa-solid fa-location-arrow me-1"></i> Discover</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Events & Culture Section -->
<section class="py-5 bg-white border-top border-5" style="border-color: #f39200 !important;">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <span class="badge bg-light text-success border px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm" style="font-family: 'Montserrat', sans-serif; letter-spacing: 2px; color: #1b4332 !important;">CULTURE & HERITAGE</span>
                <h2 class="display-4 fw-bold mb-4" style="color: #1b4332; font-family: Impact, sans-serif; line-height: 1.1; letter-spacing: 1px;">
                    TACBOAN <span style="color: #f39200;">FESTIVAL</span>
                </h2>
                <p class="fs-5 text-muted mb-4" style="line-height: 1.8;">
                    Experience the vibrant colors, rhythmic dances, and rich traditions of Vinzons! Celebrate our history, honoring our founding and our local hero, Wenceslao Q. Vinzons.
                </p>
                <ul class="list-unstyled mb-5 text-dark" style="font-family: 'Montserrat', sans-serif;">
                    <li class="mb-3 d-flex align-items-start"><i class="fa-solid fa-calendar-check text-warning mt-1 me-3 fs-5"></i> <span><strong>Annually in May</strong> — Peak cultural festivities leading to the feast of St. Peter the Apostle.</span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fa-solid fa-masks-theater text-warning mt-1 me-3 fs-5"></i> <span><strong>Theme:</strong> "Rhythm, Colors, and Traditions of Vinzons"</span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fa-solid fa-landmark text-warning mt-1 me-3 fs-5"></i> <span><strong>History:</strong> Formerly known as 'Tacboan', celebrating our roots since 1581.</span></li>
                </ul>
                <a href="<?= e(BASE_URL) ?>tourism.php" class="btn text-white fw-bold px-5 py-3 rounded-pill shadow" style="background: #1b4332; transition: all 0.3s;" onmouseover="this.style.background='#f39200'; this.style.transform='translateY(-3px)';" onmouseout="this.style.background='#1b4332'; this.style.transform='translateY(0)';">
                    See All Events
                </a>
            </div>
            <div class="col-lg-7">
                <div class="position-relative">
                    <div class="position-absolute bg-warning rounded-circle" style="width: 150px; height: 150px; top: -30px; right: -30px; opacity: 0.2; z-index: 0; filter: blur(20px);"></div>
                    <div class="position-absolute rounded-circle" style="background: #1b4332; width: 200px; height: 200px; bottom: -40px; left: -40px; opacity: 0.1; z-index: 0; filter: blur(30px);"></div>
                    
                    <div class="row g-3 position-relative z-1">
                        <div class="col-12">
                            <div class="rounded-4 shadow-lg overflow-hidden position-relative" style="height: 350px;">
                                <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1000&q=80" class="w-100 h-100 object-fit-cover" alt="Festival Main">
                                <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white" style="background: linear-gradient(transparent, rgba(27,67,50,0.95));">
                                    <h3 class="mb-0 fw-bold" style="font-family: 'Montserrat', sans-serif;">Vinzons' Day</h3>
                                    <p class="text-warning fw-bold m-0" style="font-family: 'Dancing Script', cursive; font-size: 1.5rem;">September 28, 2025</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-4 shadow-sm overflow-hidden" style="height: 200px;">
                                <img src="https://images.unsplash.com/photo-1543807535-eceef0bc6599?auto=format&fit=crop&w=600&q=80" class="w-100 h-100 object-fit-cover" alt="Festival Side 1">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-4 shadow-sm overflow-hidden" style="height: 200px;">
                                <img src="https://images.unsplash.com/photo-1533174000220-db928420dbbd?auto=format&fit=crop&w=600&q=80" class="w-100 h-100 object-fit-cover" alt="Festival Side 2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '';
require BASE_PATH . '/includes/footer.php';
