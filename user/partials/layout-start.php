<?php

declare(strict_types=1);

if (!defined('LK_DASH_HEADER')) {
    define('LK_DASH_HEADER', true);
    require BASE_PATH . '/includes/header.php';
}

$activeUser = $activeUser ?? '';
$navItems = [
    'dash' => ['label' => 'Dashboard', 'icon' => 'bi-grid-fill', 'href' => USER_DASH_URL . 'dashboard.php'],
    'msg' => ['label' => 'Messages', 'icon' => 'bi-chat-dots-fill', 'href' => USER_DASH_URL . 'messages.php'],
    'rev' => ['label' => 'My Reviews', 'icon' => 'bi-star-fill', 'href' => USER_DASH_URL . 'reviews.php'],
    'prof' => ['label' => 'Profile', 'icon' => 'bi-person-fill', 'href' => USER_DASH_URL . 'profile.php'],
];
?>
<div class="lk-dash-wrap lk-user-wrap">
    <div class="container-fluid px-0">
        <div class="row g-0">
            <nav class="col-lg-3 col-xl-2 lk-dash-sidebar lk-user-sidebar d-none d-lg-block">
                <div class="lk-dash-brand lk-user-brand">
                    <div class="text-white fw-bold mb-1" style="font-size: 0.95rem;">LikhaLokal</div>
                    <a href="<?= e(BASE_URL) ?>index.php"><i class="bi bi-arrow-left-circle me-1"></i> Back to website</a>
                </div>
                <div class="lk-dash-nav-label nav-label">My account</div>
                <?php foreach ($navItems as $key => $item): ?>
                <a class="lk-dash-nav lk-user-nav <?= $activeUser === $key ? 'active' : '' ?>" href="<?= e($item['href']) ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i>
                    <?= e($item['label']) ?>
                </a>
                <?php endforeach; ?>
                <a class="lk-dash-nav lk-user-nav logout" href="<?= e(BASE_URL) ?>logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </nav>
            <main class="col-lg-9 col-xl-10 lk-dash-main lk-user-main">
                <div class="d-lg-none mb-3">
                    <div class="d-flex gap-1 flex-wrap">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= e(BASE_URL) ?>index.php"><i class="bi bi-arrow-left"></i></a>
                        <?php foreach ($navItems as $key => $item): ?>
                        <a class="btn btn-sm <?= $activeUser === $key ? 'btn-lk-orange' : 'btn-outline-secondary' ?>" href="<?= e($item['href']) ?>">
                            <i class="bi <?= e($item['icon']) ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
