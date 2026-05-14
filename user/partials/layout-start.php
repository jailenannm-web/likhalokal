<?php

declare(strict_types=1);

$activeUser = $activeUser ?? '';
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar-admin p-0">
            <div class="p-3 text-white-50 small text-uppercase">My account</div>
            <a class="<?= $activeUser === 'dash' ? 'active' : '' ?>" href="<?= e(USER_DASH_URL) ?>dashboard.php">Dashboard</a>
            <a class="<?= $activeUser === 'msg' ? 'active' : '' ?>" href="<?= e(USER_DASH_URL) ?>messages.php">Messages</a>
            <a class="<?= $activeUser === 'rev' ? 'active' : '' ?>" href="<?= e(USER_DASH_URL) ?>reviews.php">My reviews</a>
            <a class="<?= $activeUser === 'prof' ? 'active' : '' ?>" href="<?= e(USER_DASH_URL) ?>profile.php">Profile</a>
            <a href="<?= e(BASE_URL) ?>logout.php">Logout</a>
        </nav>
        <main class="col-md-9 col-lg-10 p-4">
