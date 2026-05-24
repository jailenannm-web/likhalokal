<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';
require_login();
require_once BASE_PATH . '/middleware/role.php';
require_role(['local_user']);

$isDashboardLayout = true;
$extraHead = ($extraHead ?? '') . '<link rel="stylesheet" href="' . e(asset_url('css/dashboard.css')) . '?v=4">';
$extraHead .= '<link rel="stylesheet" href="' . e(asset_url('css/user-dashboard.css')) . '?v=4">';
$bodyClass = trim(($bodyClass ?? '') . ' lk-dash-page lk-dash-user');
