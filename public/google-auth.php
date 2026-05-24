<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (is_logged_in()) {
    redirect(public_home_url());
}

if (!google_oauth_configured()) {
    set_flash('error', 'Google sign-in is not configured yet. Please set your Google OAuth Client ID and Secret.');
    redirect(BASE_URL . 'login.php');
}

$source = (string) ($_GET['source'] ?? 'login');
$source = $source === 'register' ? 'register' : 'login';
$accountType = (string) ($_GET['account_type'] ?? 'local_user');
$accountType = ($source === 'register' && $accountType === 'seller') ? 'seller' : 'local_user';
unset($_SESSION['post_login_redirect']);

$_SESSION['google_oauth_state'] = bin2hex(random_bytes(32));
$_SESSION['google_oauth_source'] = $source;
$_SESSION['google_oauth_account_type'] = $accountType;
$_SESSION['oauth_state'] = $_SESSION['google_oauth_state'];

$params = http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['google_oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account',
]);

redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
