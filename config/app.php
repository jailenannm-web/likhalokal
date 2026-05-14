<?php

declare(strict_types=1);

/**
 * Application configuration — adjust BASE_URL / ASSET_URL for your server path.
 * XAMPP default: project in htdocs/likhalokal → URLs below.
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', '/likhalokal/public/');
}
if (!defined('ASSET_URL')) {
    define('ASSET_URL', '/likhalokal/assets/');
}
/** Dashboard bases (sibling folders to /public/) */
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', '/likhalokal/admin/');
}
if (!defined('SELLER_URL')) {
    define('SELLER_URL', '/likhalokal/seller/');
}
if (!defined('USER_DASH_URL')) {
    define('USER_DASH_URL', '/likhalokal/user/');
}

define('APP_NAME', 'Vinzons LikhaLokal: Tuklas, Kultura, Kabuhayan');
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);
