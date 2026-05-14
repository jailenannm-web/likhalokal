<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

header('X-Content-Type-Options: nosniff');

function api_require_login(): void
{
    if (!is_logged_in()) {
        json_response(['success' => false, 'message' => 'Unauthorized', 'data' => []], 401);
    }
}

function api_require_roles(array $roles): void
{
    api_require_login();
    if (!in_array(current_user_role(), $roles, true)) {
        json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
    }
}
