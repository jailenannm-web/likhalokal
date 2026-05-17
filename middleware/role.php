<?php

declare(strict_types=1);

function require_role(array $roles): void
{
    require_login();
    $role = current_user_role();
    if (!in_array($role, $roles, true)) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['success' => false, 'message' => 'Forbidden', 'data' => []], 403);
        }
        set_flash('error', 'You do not have access to this page.');
        redirect(BASE_URL . 'forbidden.php');
    }
}
