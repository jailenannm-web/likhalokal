<?php

declare(strict_types=1);

$pageTitle = 'Local Business';
$activeNav = 'business';
require_once dirname(__DIR__) . '/bootstrap.php';

$featured = db()->query(
    "SELECT * FROM businesses WHERE status='approved' ORDER BY id ASC LIMIT 4"
)->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<section class="hero mb-4" style="background-image:url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1600&q=80');">
    <div class="container hero-inner py-5">
        <h1 class="display-5 hero-title">LOKAL NA NEGOSYO, LOKAL NA ASENSO</h1>
        <p class="lead hero-sub">Supporting entrepreneurs, building community livelihoods.</p>
    </div>
</section>

<div class="container py-5">
    <div class="text-center bg-dark text-white p-4 rounded mb-4">
        <h2 class="h4 text-warning mb-2">CONNECT. SHOWCASE. SELL. GROW.</h2>
        <p class="mb-0 small text-white-50">Bring your business closer to the community with LikhaLokal.</p>
    </div>

    <h2 class="h4 mb-3">Business directory categories</h2>
    <div class="row g-3 mb-5">
        <?php
        $cats = [
            ['Food & Restaurants', 'restaurant', 'bi-cup-hot'],
            ['Resorts & Homestays', 'resort', 'bi-house-door'],
            ['Pasalubongs', 'pasalubong', 'bi-bag-heart'],
            ['Services', 'service', 'bi-gear-wide-connected'],
        ];
        foreach ($cats as $c):
        ?>
            <div class="col-md-3">
                <a class="text-decoration-none" href="<?= e(BASE_URL) ?>business-directory.php?type=<?= e($c[1]) ?>">
                    <div class="card card-lk text-center p-4 h-100 border-warning border-2">
                        <i class="bi <?= e($c[2]) ?> display-4 text-warning mb-2"></i>
                        <div class="fw-bold text-dark"><?= e($c[0]) ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="h4 mb-3">Support <span class="text-warning">LOCAL</span> — Featured</h2>
    <div class="row g-3 mb-5">
        <?php foreach ($featured as $b): ?>
            <div class="col-md-3">
                <div class="card card-lk h-100">
                    <div class="card-body">
                        <h3 class="h6"><?= e($b['business_name']) ?></h3>
                        <p class="small text-muted"><?= e(str_limit((string) $b['description'], 80)) ?></p>
                        <a href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $b['id'] ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="p-4 rounded" style="background:var(--lk-sky);">
        <h3 class="h5 text-warning">How to register?</h3>
        <ol class="small">
            <li>Prepare business information and valid documents.</li>
            <li>Create a seller account and submit your application.</li>
            <li>Wait for tourism officer verification.</li>
            <li>Once approved, your storefront goes live.</li>
        </ol>
        <?php if (is_logged_in() && current_user_role() === 'seller'): ?>
            <a class="btn btn-lk-orange" href="<?= e(SELLER_URL) ?>business-profile.php">Apply / manage business</a>
        <?php else: ?>
            <a class="btn btn-lk-orange" href="#" data-require-auth>Apply business</a>
            <p class="small mt-2 mb-0">Sellers: login or register as Entrepreneur to continue.</p>
        <?php endif; ?>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
