<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';
require_login();
require_once BASE_PATH . '/middleware/role.php';
require_role(['admin']);
