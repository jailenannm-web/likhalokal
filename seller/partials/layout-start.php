<?php

declare(strict_types=1);

$activeSeller = $activeSeller ?? '';
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar-admin p-0">
            <div class="p-3 text-white-50 small text-uppercase">Seller</div>
            <a class="<?= $activeSeller === 'dash' ? 'active' : '' ?>" href="<?= e(SELLER_URL) ?>dashboard.php">Dashboard</a>
            <a class="<?= $activeSeller === 'biz' ? 'active' : '' ?>" href="<?= e(SELLER_URL) ?>business-profile.php">Business profile</a>
            <a class="<?= $activeSeller === 'prod' ? 'active' : '' ?>" href="<?= e(SELLER_URL) ?>products.php">Products</a>
            <a class="<?= $activeSeller === 'msg' ? 'active' : '' ?>" href="<?= e(SELLER_URL) ?>messages.php">Messages</a>
            <a class="<?= $activeSeller === 'rev' ? 'active' : '' ?>" href="<?= e(SELLER_URL) ?>reviews.php">Reviews</a>
            <a class="<?= $activeSeller === 'pro' ? 'active' : '' ?>" href="<?= e(SELLER_URL) ?>promotions.php">Promotions</a>
            <a href="<?= e(BASE_URL) ?>logout.php">Logout</a>
        </nav>
        <main class="col-md-9 col-lg-10 p-4">
