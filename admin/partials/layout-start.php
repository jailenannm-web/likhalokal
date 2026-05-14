<?php

declare(strict_types=1);

$activeAdmin = $activeAdmin ?? '';
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar-admin p-0">
            <div class="p-3 text-white-50 small text-uppercase">Admin</div>
            <a class="<?= $activeAdmin === 'dash' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>dashboard.php">Dashboard</a>
            <a class="<?= $activeAdmin === 'biz' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>businesses.php">Businesses</a>
            <a class="<?= $activeAdmin === 'att' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>attractions.php">Attractions</a>
            <a class="<?= $activeAdmin === 'evt' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>events.php">Events</a>
            <a class="<?= $activeAdmin === 'ann' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>announcements.php">Announcements</a>
            <a class="<?= $activeAdmin === 'cul' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>cultural-info.php">Cultural Info</a>
            <a class="<?= $activeAdmin === 'rev' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>reviews.php">Reviews</a>
            <a class="<?= $activeAdmin === 'msg' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>messages.php">Messages</a>
            <a class="<?= $activeAdmin === 'usr' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>users.php">Users</a>
            <a class="<?= $activeAdmin === 'rep' ? 'active' : '' ?>" href="<?= e(ADMIN_URL) ?>reports.php">Reports</a>
            <a href="<?= e(BASE_URL) ?>logout.php">Logout</a>
        </nav>
        <main class="col-md-9 col-lg-10 p-4">
