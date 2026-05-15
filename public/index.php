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

<!-- Hero Section -->
<section class="hero mb-0" style="background-image:url('<?= e(ASSET_URL) ?>images/landing-picture.png');">
    <div class="hero-layer-image" style="background-image:url('<?= e(ASSET_URL) ?>images/landing-layer.png');"></div>
    <div class="hero-gradient"></div>
    <div class="container hero-inner py-5 mt-5">
        <div class="col-lg-10 text-white">
            <h1 class="hero-title">MADYA<br>NA SA<br>VINZONS!</h1>
            <p class="hero-sub mt-4 mb-5">"Discover Vinzons<br>Where Adventure Meets Local Culture."</p>
            <div class="d-flex gap-3 flex-wrap">
                <a href="<?= e(BASE_URL) ?>tourism.php" class="btn btn-lk-orange btn-lg shadow-lg">Explore Tourism</a>
                <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-lk-outline-white btn-lg shadow-lg">Shop Local</a>
            </div>
        </div>
    </div>
</section>

<!-- Entrepreneurs Section -->
<section class="pt-5 bg-white position-relative" style="background: url('https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80') center/cover fixed;">
    <div style="position:absolute; inset:0; background:rgba(255,255,255,0.7);"></div>
    <div class="container position-relative z-1 py-5">
        <div class="glass-banner text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
            <div class="text-dark">
                <h2 class="text-warning fw-bold mb-0" style="font-family: Impact, 'Arial Black', sans-serif; letter-spacing: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">ATTENTION</h2>
                <h1 class="fw-bold mb-3" style="font-family: Impact, 'Arial Black', sans-serif; letter-spacing: 1px;">LOCAL ENTREPRENEURS!</h1>
                <p class="mb-0 fs-5" style="max-width: 600px;">Register your business on LikhaLokal to reach tourists and residents with a digital storefront. Showcase products, receive inquiries, and grow your business in a digitally connected world.</p>
            </div>
            <div>
                <a href="<?= e(BASE_URL) ?>register.php" class="btn btn-lk-orange shadow-lg btn-lg"><i class="fa-solid fa-store me-2"></i> Register Now</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3 border-bottom pb-3 border-secondary border-opacity-25">
            <div>
                <h2 class="fw-bold mb-0" style="color: var(--lk-navy); font-family: Impact, sans-serif; letter-spacing: 1px;">FEATURED PRODUCTS</h2>
                <p class="text-muted mb-0">Shop authentic local goods — from handcrafted souvenirs to fresh agricultural produce.</p>
            </div>
            <div>
                <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-lk-outline-white text-dark border-dark px-4 py-2">View All Products <i class="fa-solid fa-arrow-right ms-2"></i></a>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach (array_slice($featured, 0, 4) as $p): ?>
                <div class="product-card">
                    <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                    <img src="<?= e($img) ?>" alt="<?= e($p['product_name']) ?>">
                    <div class="product-card-body">
                        <div>
                            <h4 class="fw-bold text-dark mb-2"><?= e($p['product_name']) ?></h4>
                            <p class="text-muted small mb-4"><?= e(str_limit((string) $p['description'], 60)) ?></p>
                        </div>
                        <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $p['business_id'] ?>" class="btn btn-lk-orange w-100"><i class="fa-solid fa-shop me-2"></i> See Sellers</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Top Tourist Spots Section -->
<section class="py-5" style="background: #f4f7f6;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="font-family: Impact, sans-serif; letter-spacing: 2px;">
                <span style="color: var(--lk-navy);">TOP</span>
                <span style="color: var(--lk-orange);">TOURIST SPOTS</span>
            </h2>
            <p class="text-muted">Explore scenic spots, historical landmarks, and hidden gems in Vinzons.</p>
        </div>
        
        <div class="tourist-gallery">
            <?php foreach (array_slice($spots, 0, 6) as $s): ?>
                <div class="tourist-spot">
                    <?php $sim = $s['image'] ? asset_url($s['image']) : 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80'; ?>
                    <img src="<?= e($sim) ?>" alt="<?= e($s['attraction_name']) ?>">
                    <div class="spot-title"><?= e($s['attraction_name']) ?></div>
                    <div class="spot-overlay">
                        <h4 class="fw-bold mb-3"><?= e($s['attraction_name']) ?></h4>
                        <p class="small text-white-50 mb-4">Discover the rich history and beauty of this iconic location in Vinzons.</p>
                        <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $s['id'] ?>" class="btn btn-lk-orange w-100">Learn More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Events Section -->
<section class="events-section">
    <div class="container">
        <div class="events-title-stack">
            <span class="small-text">EVENTS &amp;</span>
            <span class="large-text">FESTIVALS</span>
        </div>
        
        <div class="row g-4 mt-2">
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-12">
                        <div style="height: 350px; background-image: url('https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center; border-radius: 12px; position: relative;">
                            <div class="position-absolute bottom-0 start-0 p-4 w-100" style="background: linear-gradient(transparent, rgba(0,0,0,0.9)); border-radius: 0 0 12px 12px;">
                                <h3 class="text-warning mb-0" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem;">Vinzons' Day</h3>
                                <h2 class="text-white fw-bold mb-2" style="font-family: Impact, sans-serif; font-size: 2rem; letter-spacing: 1px;">115TH BIRTH ANNIVERSARY</h2>
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6">SEPTEMBER 28, 2025</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="height: 200px; background-image: url('https://images.unsplash.com/photo-1543807535-eceef0bc6599?auto=format&fit=crop&w=600&q=80'); background-size: cover; background-position: center; border-radius: 12px;"></div>
                    </div>
                    <div class="col-6">
                        <div style="height: 200px; background-image: url('https://images.unsplash.com/photo-1533174000220-db928420dbbd?auto=format&fit=crop&w=600&q=80'); background-size: cover; background-position: center; border-radius: 12px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex flex-column justify-content-center ps-lg-5">
                <h2 class="fw-bold mb-4" style="color: var(--lk-green); font-size: 2.5rem; line-height: 1.2;">Tacboan Festival /<br>Wenceslao Vinzons' Birth<br>Anniversary</h2>
                
                <p class="fw-bold fs-5 text-dark">Take a look at the major festivals in Vinzons.<br>Celebrating colors, culture, and heritage.</p>
                
                <div class="text-dark">
                    <p class="mb-3"><strong>Festival Name:</strong> Tacboan Festival</p>
                    
                    <p class="mb-3"><strong>Date:</strong> Annually in May (The main feast day of St. Peter the Apostle is June 29, but the cultural festival activities typically peak in May).</p>
                    
                    <p class="mb-3"><strong>Theme:</strong> "Rhythm, Colors, and Traditions of Vinzons"</p>
                    
                    <p class="mb-3"><strong>Historical Context:</strong> The name "Tacboan" comes from the original name of the town before it was renamed Indan and eventually Vinzons. It celebrates the town's founding in 1581 and its rich agricultural heritage.</p>
                    
                    <p class="mb-0"><strong>Fact:</strong> The former residence of the town's hero, Wenceslao "Bintao" Q. Vinzons, a patriot and martyr of World War II. It houses a public library and a museum of his memorabilia.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '';
require BASE_PATH . '/includes/footer.php';
