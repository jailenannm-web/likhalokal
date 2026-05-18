<?php

declare(strict_types=1);

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
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/likhalokal/public/google-callback.php');

define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

/** Remember-me cookie */
define('REMEMBER_COOKIE_NAME', 'likha_remember');
define('REMEMBER_DAYS', 30);

/** SMTP (optional — forgot password) */
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', (int) (getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'noreply@likhalokal.local');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'LikhaLokal');
