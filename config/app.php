<?php

declare(strict_types=1);

$localConfig = __DIR__ . '/local.php';

if (file_exists($localConfig)) {
    require_once $localConfig;
}

/**
 * Application configuration — adjust BASE_URL / ASSET_URL for your server path.
 * XAMPP default: project in htdocs/likhalokal → URLs below.
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', getenv('LIKHA_BASE_URL') ?: '/likhalokal/public/');
}
if (!defined('ASSET_URL')) {
    define('ASSET_URL', getenv('LIKHA_ASSET_URL') ?: '/likhalokal/assets/');
}
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', getenv('LIKHA_ADMIN_URL') ?: '/likhalokal/admin/');
}
if (!defined('SELLER_URL')) {
    define('SELLER_URL', getenv('LIKHA_SELLER_URL') ?: '/likhalokal/seller/');
}
if (!defined('USER_DASH_URL')) {
    define('USER_DASH_URL', getenv('LIKHA_USER_DASH_URL') ?: '/likhalokal/user/');
}

define('APP_NAME', 'Vinzons LikhaLokal: Tuklas, Kultura, Kabuhayan');
define('APP_DEBUG', (getenv('LIKHA_DEBUG') ?: '1') === '1');

/** Google Maps — https://console.cloud.google.com/apis/credentials */
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

/** Google OAuth */
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/likhalokal/public/google-callback.php');

define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

/** Remember-me cookie */
define('REMEMBER_COOKIE_NAME', 'likha_remember');
define('REMEMBER_DAYS', 30);

/** SMTP (optional — forgot password). Set via env in production. */
define('MAIL_HOST', getenv('MAIL_HOST') ?: (getenv('SMTP_HOST') ?: ''));
define('MAIL_PORT', (int) (getenv('MAIL_PORT') ?: (getenv('SMTP_PORT') ?: 587)));
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: (getenv('SMTP_USER') ?: ''));
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: (getenv('SMTP_PASS') ?: ''));
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: (getenv('SMTP_FROM_EMAIL') ?: 'noreply@likhalokal.local'));
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: (getenv('SMTP_FROM_NAME') ?: 'LikhaLokal'));
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');

/** @deprecated Use MAIL_* constants */
define('SMTP_HOST', MAIL_HOST);
define('SMTP_PORT', MAIL_PORT);
define('SMTP_USER', MAIL_USERNAME);
define('SMTP_PASS', MAIL_PASSWORD);
define('SMTP_FROM_EMAIL', MAIL_FROM_EMAIL);
define('SMTP_FROM_NAME', MAIL_FROM_NAME);
