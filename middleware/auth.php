<?php

declare(strict_types=1);

function require_login(): void
{
    if (!is_logged_in()) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['success' => false, 'message' => 'Unauthorized', 'data' => []], 401);
        }
        set_flash('error', 'Please login to continue.');
        redirect(BASE_URL . 'login.php');
    }
}

function require_guest_for_register(): void
{
    if (is_logged_in()) {
        redirect(dashboard_url_for_role(current_user_role()));
    }
}

function dashboard_url_for_role(?string $role): string
{
    return match ($role) {
        'admin' => ADMIN_URL . 'dashboard.php',
        'seller' => SELLER_URL . 'dashboard.php',
        'local_user' => USER_DASH_URL . 'dashboard.php',
        default => BASE_URL . 'index.php',
    };
}
