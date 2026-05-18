<?php

declare(strict_types=1);

$active = $activeNav ?? '';
$isLoggedIn = is_logged_in();
$role = current_user_role();
$navUser = $isLoggedIn ? current_user() : null;
$navAvatar = $navUser
    ? profile_avatar_url($navUser['full_name'] ?? null, $navUser['profile_image'] ?? null)
    : '';
$lkSearchApi = preg_replace('#/public/?$#', '/api/', rtrim(BASE_URL, '/')) . 'search.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark lk-navbar fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= e(BASE_URL) ?>index.php">
            <img src="<?= asset_url('images/likhalokal-logo.png') ?>" alt="LikhaLokal Logo" style="height: 40px; width: auto; object-fit: contain;">
            <div class="d-flex flex-column lh-1">
                <span class="text-warning fw-bold" style="font-size: 1.1rem;">LikhaLokal:</span>
                <span class="text-white fw-bold" style="font-size: 0.8rem;">Tuklas, Kultura, Kabuhayan</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lkNav" aria-controls="lkNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="lkNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center text-lg-start">
                <li class="nav-item"><a class="nav-link <?= $active === 'home' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'tourism' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>tourism.php">Tourism</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'products' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>products.php">Marketplace</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'business' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>local-business.php">Local Business</a></li>
                <li class="nav-item"><a class="nav-link <?= $active === 'about' ? 'active text-warning' : '' ?>" href="<?= e(BASE_URL) ?>about.php">About</a></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-link border-0 text-white" id="lkSearchToggle" title="Search" aria-expanded="false" aria-controls="lkSearchOverlay">
                        <i class="bi bi-search"></i>
                    </button>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= e($navAvatar) ?>" alt="" class="rounded-circle" width="28" height="28" style="object-fit:cover;border:2px solid var(--lk-orange);">
                            <span class="d-none d-md-inline"><?= e($_SESSION['user_name'] ?? 'Account') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($role === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= e(ADMIN_URL) ?>dashboard.php">Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= e(ADMIN_URL) ?>messages.php">Messages</a></li>
                                <li><a class="dropdown-item" href="<?= e(ADMIN_URL) ?>business-applications.php">Business Applications</a></li>
                                <li><a class="dropdown-item" href="<?= e(ADMIN_URL) ?>manage-businesses.php">Manage Businesses</a></li>
                                <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>index.php">View Public Website</a></li>
                            <?php elseif ($role === 'seller'): ?>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>dashboard.php">Seller Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>business-profile.php">Business Profile</a></li>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>products.php">Products / Services</a></li>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>messages.php">Messages</a></li>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>reviews.php">Reviews</a></li>
                                <li><a class="dropdown-item" href="<?= e(SELLER_URL) ?>promotions.php">Promotions</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= e(USER_DASH_URL) ?>dashboard.php">My Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= e(USER_DASH_URL) ?>messages.php">My Messages</a></li>
                                <li><a class="dropdown-item" href="<?= e(USER_DASH_URL) ?>reviews.php">My Reviews</a></li>
                                <li><a class="dropdown-item" href="<?= e(USER_DASH_URL) ?>profile.php">My Profile</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= e(BASE_URL) ?>login.php" title="Login"><i class="bi bi-box-arrow-in-right fs-5"></i></a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="<?= e(BASE_URL) ?>register.php" title="Register"><i class="bi bi-person-plus fs-5"></i></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="lk-search-overlay" id="lkSearchOverlay" aria-hidden="true" data-api="<?= e($lkSearchApi) ?>">
    <div class="lk-search-box">
        <form id="lkSearchForm" role="search" autocomplete="off" onsubmit="return false;">
            <div class="input-group">
                <input type="search" class="form-control" id="lkSearchInput" placeholder="Search businesses, products, attractions…" aria-label="Search">
                <button type="button" class="btn btn-lk-orange rounded-end-pill px-4" id="lkSearchClose" aria-label="Close search"><i class="bi bi-x-lg"></i></button>
            </div>
        </form>
        <div class="lk-search-results" id="lkSearchResults"></div>
        <p class="text-center mt-3 mb-0"><a href="<?= e(BASE_URL) ?>search.php" class="text-warning small" id="lkSearchViewAll" style="display:none;">View all results</a></p>
    </div>
</div>
