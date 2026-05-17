<?php

declare(strict_types=1);

if (!defined('LK_DASH_HEADER')) {
    define('LK_DASH_HEADER', true);
    require BASE_PATH . '/includes/header.php';
}

$activeAdmin = $activeAdmin ?? '';
$navItems = [
    'dash' => ['label' => 'Dashboard', 'icon' => 'bi-grid-fill', 'href' => ADMIN_URL . 'dashboard.php'],
    'apps' => ['label' => 'Business Applications', 'icon' => 'bi-file-earmark-check-fill', 'href' => ADMIN_URL . 'business-applications.php?tab=pending'],
    'biz' => ['label' => 'Manage Businesses', 'icon' => 'bi-building', 'href' => ADMIN_URL . 'businesses.php'],
    'att' => ['label' => 'Tourist Attractions', 'icon' => 'bi-geo-alt-fill', 'href' => ADMIN_URL . 'attractions.php'],
    'evt' => ['label' => 'Events', 'icon' => 'bi-calendar-event-fill', 'href' => ADMIN_URL . 'events.php'],
    'ann' => ['label' => 'Announcements', 'icon' => 'bi-megaphone-fill', 'href' => ADMIN_URL . 'announcements.php'],
    'cul' => ['label' => 'Cultural Information', 'icon' => 'bi-book-fill', 'href' => ADMIN_URL . 'cultural-info.php'],
    'rev' => ['label' => 'Reviews', 'icon' => 'bi-star-fill', 'href' => ADMIN_URL . 'reviews.php'],
    'usr' => ['label' => 'Users', 'icon' => 'bi-people-fill', 'href' => ADMIN_URL . 'users.php'],
    'rep' => ['label' => 'Reports', 'icon' => 'bi-bar-chart-fill', 'href' => ADMIN_URL . 'reports.php'],
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
                <div class="lk-dash-nav-label">Administration</div>
                <?php foreach ($navItems as $key => $item): ?>
                <a class="lk-dash-nav <?= $activeAdmin === $key ? 'active' : '' ?>" href="<?= e($item['href']) ?>">
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
                        <a class="btn btn-sm <?= $activeAdmin === $key ? 'btn-lk-orange' : 'btn-outline-secondary' ?>" href="<?= e($item['href']) ?>">
                            <i class="bi <?= e($item['icon']) ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
