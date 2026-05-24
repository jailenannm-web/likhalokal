<?php

declare(strict_types=1);

function public_home_url(): string
{
    return BASE_URL . 'index.php';
}

/**
 * Public pages only — used after login when user came from Chat Seller, review, etc.
 */
function is_safe_post_login_redirect(?string $url): bool
{
    if ($url === null || trim($url) === '') {
        return false;
    }
    $url = trim($url);
    if (str_contains($url, '..')) {
        return false;
    }

    $path = $url;
    if (preg_match('#^https?://#i', $url)) {
        $parts = parse_url($url);
        $path = ($parts['path'] ?? '') . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    foreach (['/admin/', '/seller/', '/user/', '\\admin\\', '\\seller\\', '\\user\\'] as $blocked) {
        if (str_contains($path, $blocked)) {
            return false;
        }
    }

    $publicPages = [
        'index.php',
        'tourism.php',
        'products.php',
        'local-business.php',
        'about.php',
        'vendor-profile.php',
        'attraction-detail.php',
        'events.php',
        'cultural-info.php',
        'business-directory.php',
        'search.php',
        'message.php',
        'register-business.php',
    ];

    foreach ($publicPages as $page) {
        if (str_contains($path, $page)) {
            return true;
        }
    }

    return false;
}

function post_login_redirect_url(?string $requested = null): string
{
    if ($requested !== null && is_safe_post_login_redirect($requested)) {
        if (preg_match('#^https?://#i', $requested)) {
            return $requested;
        }
        if (str_starts_with($requested, '/')) {
            return $requested;
        }
        return BASE_URL . ltrim($requested, '/');
    }

    return public_home_url();
}

function redirect_after_login(?string $requested = null): void
{
    redirect(post_login_redirect_url($requested));
}

function login_url_with_redirect(?string $returnTo = null): string
{
    $url = BASE_URL . 'login.php';
    if ($returnTo !== null && is_safe_post_login_redirect($returnTo)) {
        $url .= '?redirect=' . rawurlencode($returnTo);
    }
    return $url;
}

function current_public_return_url(): ?string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri !== '' && is_safe_post_login_redirect($uri)) {
        return $uri;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($script !== '' && is_safe_post_login_redirect($script)) {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        return $script . ($query !== '' ? '?' . $query : '');
    }
    return null;
}

function peek_login_redirect(): ?string
{
    $fromRequest = $_POST['redirect'] ?? $_GET['redirect'] ?? null;
    if (is_string($fromRequest) && $fromRequest !== '') {
        return $fromRequest;
    }
    $fromSession = $_SESSION['post_login_redirect'] ?? null;
    if (is_string($fromSession) && $fromSession !== '') {
        return $fromSession;
    }
    return null;
}

function consume_login_redirect(): ?string
{
    $url = peek_login_redirect();
    unset($_SESSION['post_login_redirect']);
    return $url;
}

function require_login(): void
{
    if (!is_logged_in()) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['success' => false, 'message' => 'Unauthorized', 'data' => []], 401);
        }
        $return = current_public_return_url();
        if ($return !== null) {
            $_SESSION['post_login_redirect'] = $return;
        }
        set_flash('error', 'Please login to continue.');
        redirect(login_url_with_redirect($return));
    }
}

function require_guest_for_register(): void
{
    if (is_logged_in()) {
        redirect_after_login();
    }
}

/** @deprecated Use dashboard_url_for_role() for dropdown links only. */
function dashboard_url_for_role(?string $role): string
{
    return match ($role) {
        'admin' => ADMIN_URL . 'dashboard.php',
        'seller' => SELLER_URL . 'dashboard.php',
        'local_user' => USER_DASH_URL . 'dashboard.php',
        default => public_home_url(),
    };
}

/** @internal Use only when explicitly sending a user to their role dashboard. */
function redirect_to_role_dashboard(): void
{
    redirect(dashboard_url_for_role(current_user_role()));
}

/** After login/register — safe public return URL, else public home. */
function redirect_by_role(): void
{
    redirect_after_login(consume_login_redirect());
}

function user_status_allows_login(string $status, ?string $role = null): bool
{
    if ($status === 'active') {
        return true;
    }
    if ($status === 'pending' && $role !== 'admin') {
        return true;
    }
    return false;
}

function google_oauth_configured(): bool
{
    $placeholderIds = ['YOUR_GOOGLE_CLIENT_ID', 'your_google_client_id_here', ''];
    $placeholderSecrets = ['YOUR_GOOGLE_CLIENT_SECRET', 'your_google_client_secret_here', ''];

    return !in_array(GOOGLE_CLIENT_ID, $placeholderIds, true)
        && !in_array(GOOGLE_CLIENT_SECRET, $placeholderSecrets, true)
        && GOOGLE_REDIRECT_URI !== '';
}
