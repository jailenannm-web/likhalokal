<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/auth_helpers.php';
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/middleware/csrf.php';

init_secure_session();
try_remember_login();
if (is_logged_in()) {
    update_last_seen();
}
