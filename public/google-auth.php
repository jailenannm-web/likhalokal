<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (is_logged_in()) {
    redirect_after_login();
}

if (!google_oauth_configured()) {
    set_flash('error', 'Google login is not configured yet. Use email and password, or set GOOGLE_CLIENT_ID in config/app.php.');
    redirect(BASE_URL . 'login.php');
}

$_SESSION['oauth_state'] = bin2hex(random_bytes(16));

$params = http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account',
]);

redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
