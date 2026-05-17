<?php

declare(strict_types=1);

if (!defined('LK_DASH_HEADER')) {
    define('LK_DASH_HEADER', true);
    require BASE_PATH . '/includes/header.php';
}

$activeSeller = $activeSeller ?? '';
$navItems = [
    'dash' => ['label' => 'Dashboard', 'icon' => 'bi-grid-fill', 'href' => SELLER_URL . 'dashboard.php'],
    'biz' => ['label' => 'Business Profile', 'icon' => 'bi-shop', 'href' => SELLER_URL . 'business-profile.php'],
    'prod' => ['label' => 'Products / Services', 'icon' => 'bi-box-seam-fill', 'href' => SELLER_URL . 'products.php'],
    'msg' => ['label' => 'Messages', 'icon' => 'bi-chat-dots-fill', 'href' => SELLER_URL . 'messages.php'],
    'rev' => ['label' => 'Reviews', 'icon' => 'bi-star-fill', 'href' => SELLER_URL . 'reviews.php'],
    'pro' => ['label' => 'Promotions', 'icon' => 'bi-megaphone-fill', 'href' => SELLER_URL . 'promotions.php'],
];
?>
<div class="lk-dash-wrap">
    <div class="container-fluid px-0">
        <div class="row g-0">
            <nav class="col-lg-3 col-xl-2 lk-dash-sidebar d-none d-lg-block">
                <div class="lk-dash-brand">
                    <div class="text-white fw-bold mb-1" style="font-size: 0.95rem;">LikhaLokal</div>
                    <a href="<?= e(BASE_URL) ?>index.php"><i class="bi bi-arrow-left-circle me-1"></i> Back to website</a>
                </div>
                <div class="lk-dash-nav-label">Seller account</div>
                <?php foreach ($navItems as $key => $item): ?>
                <a class="lk-dash-nav <?= $activeSeller === $key ? 'active' : '' ?>" href="<?= e($item['href']) ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i>
                    <?= e($item['label']) ?>
                </a>
                <?php endforeach; ?>
                <a class="lk-dash-nav logout" href="<?= e(BASE_URL) ?>logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </nav>
            <main class="col-lg-9 col-xl-10 lk-dash-main">
                <div class="d-lg-none mb-3">
                    <div class="d-flex gap-1 flex-wrap">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= e(BASE_URL) ?>index.php"><i class="bi bi-arrow-left"></i></a>
                        <?php foreach ($navItems as $key => $item): ?>
                        <a class="btn btn-sm <?= $activeSeller === $key ? 'btn-lk-orange' : 'btn-outline-secondary' ?>" href="<?= e($item['href']) ?>">
                            <i class="bi <?= e($item['icon']) ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
