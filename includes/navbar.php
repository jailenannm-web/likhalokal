<?php

declare(strict_types=1);

$active = $activeNav ?? '';
$isLoggedIn = is_logged_in();
$role = current_user_role();
?>
<nav class="navbar navbar-expand-lg navbar-dark lk-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= e(BASE_URL) ?>index.php">
            <i class="bi bi-binoculars fs-4 text-warning"></i>
            <span class="brand-text">LikhaLokal <small class="d-none d-md-inline text-white-50 fw-normal">Tuklas, Kultura, Kabuhayan</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lkNav" aria-controls="lkNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="lkNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center text-lg-start">
                <li class="nav-item"><a class="nav-link <?= $active === 'home' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'tourism' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>tourism.php">Tourism</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'products' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'business' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>local-business.php">Local Business</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'about' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>about.php">About</a></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= e(BASE_URL) ?>products.php" title="Search products"><i class="bi bi-search"></i></a></li>
                <li class="nav-item"><span class="nav-link disabled opacity-50" title="Cart (placeholder)"><i class="bi bi-cart3"></i></span></li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= e($_SESSION['user_name'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($role === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= e(ADMIN_URL) ?>dashboard.php">Admin Dashboard</a></li>
                            <?php elseif ($role === 'seller'): ?>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>dashboard.php">Seller Dashboard</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= e(USER_DASH_URL) ?>dashboard.php">My Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm ms-lg-2" href="<?= e(BASE_URL) ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-warning btn-sm text-dark ms-lg-2 mt-2 mt-lg-0" href="<?= e(BASE_URL) ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
