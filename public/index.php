<?php

declare(strict_types=1);

$pageTitle = 'Home';
$activeNav = 'home';
require_once dirname(__DIR__) . '/bootstrap.php';

$featured = db()->query(
    "SELECT p.*, b.business_name FROM products p JOIN businesses b ON b.id = p.business_id
     WHERE b.status = 'approved' AND p.is_featured = 1 ORDER BY p.updated_at DESC LIMIT 8"
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

<section class="hero mb-0" style="background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80');">
    <div class="container hero-inner py-5">
        <div class="col-lg-8">
            <h1 class="display-4 hero-title">MADYA NA SA VINZONS!</h1>
            <p class="lead hero-sub">Discover Vinzons — Where Adventure Meets Local Culture.</p>
            <a href="<?= e(BASE_URL) ?>tourism.php" class="btn btn-warning btn-lg text-dark fw-semibold me-2">Explore Tourism</a>
            <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-outline-light btn-lg">Shop Local</a>
        </div>
    </div>
</section>

<section class="py-4 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <h2 class="h4 text-warning mb-2">ATTENTION LOCAL ENTREPRENEURS!</h2>
                <p class="mb-0 small text-white-50">Register your business on LikhaLokal to reach tourists and residents with a digital storefront. Showcase products, receive inquiries, and grow your livelihood.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= e(BASE_URL) ?>local-business.php" class="btn btn-warning text-dark fw-bold">Join the platform</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Discover <span class="accent">MORE</span></h2>
            <p class="text-secondary">FEATURED PRODUCTS</p>
            <p class="mx-auto col-lg-8">Shop authentic local goods — from handcrafted souvenirs to fresh agricultural produce, all made with love by local artisans.</p>
            <a href="<?= e(BASE_URL) ?>products.php" class="btn btn-lk-orange">Show more</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $p): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-lk h-100">
                        <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                        <img src="<?= e($img) ?>" class="w-100" alt="">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= e($p['product_name']) ?></h5>
                            <p class="small text-muted flex-grow-1"><?= e(str_limit((string) $p['description'], 90)) ?></p>
                            <p class="fw-bold text-success mb-2">₱<?= e(number_format((float) $p['price'], 2)) ?></p>
                            <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $p['business_id'] ?>" class="btn btn-sm btn-outline-secondary">See Sellers</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$featured): ?>
                <p class="text-muted">No featured products yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-4">TOP <span class="text-primary">TOURIST</span> <span class="accent">SPOTS</span></h2>
        <div class="row g-4">
            <?php foreach ($spots as $s): ?>
                <div class="col-md-4">
                    <div class="card card-lk tourism-card h-100">
                        <?php $sim = $s['image'] ? asset_url($s['image']) : 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80'; ?>
                        <img src="<?= e($sim) ?>" alt="">
                        <div class="card-body">
                            <h5><?= e($s['attraction_name']) ?></h5>
                            <p class="small text-muted"><?= e(str_limit((string) $s['description'], 120)) ?></p>
                            <a class="btn btn-sm btn-primary" href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $s['id'] ?>">View details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="text-center text-muted mt-3 small">Explore scenic spots, historical landmarks, and hidden gems in Vinzons.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-4">EVENTS &amp; <span class="accent">FESTIVALS</span></h2>
        <div class="row g-4">
            <?php foreach ($events as $ev): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-lk h-100 border-0">
                        <div class="card-body">
                            <span class="badge bg-warning text-dark mb-2"><?= e($ev['event_date']) ?></span>
                            <h5><?= e($ev['title']) ?></h5>
                            <p class="small"><?= e(str_limit((string) $ev['description'], 140)) ?></p>
                            <p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?= e($ev['location'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-4 bg-white border-top">
    <div class="container">
        <h3 class="h5 mb-3">Announcements</h3>
        <ul class="list-unstyled mb-0">
            <?php foreach ($announcements as $a): ?>
                <li class="mb-2"><strong><?= e($a['title']) ?></strong> — <?= e(str_limit(strip_tags((string) $a['content']), 160)) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php
$extraScripts = '';
require BASE_PATH . '/includes/footer.php';
