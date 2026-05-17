<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/middleware/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
