<?php

declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function str_limit(?string $s, int $n = 120): string
{
    $s = (string) $s;
    if (strlen($s) <= $n) {
        return $s;
    }
    return substr($s, 0, $n) . '...';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $stmt = db()->prepare(
        'SELECT id, full_name, email, role, status, contact_number, profile_image, auth_provider FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([current_user_id()]);
    $cached = $stmt->fetch() ?: null;
    return $cached;
}

function media_url(?string $path, ?string $fallback = null): string
{
    $placeholder = $fallback ?? asset_url('images/placeholder.png');
    if ($path === null || trim($path) === '') {
        return $placeholder;
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $path = str_replace('\\', '/', trim($path));
    $path = ltrim($path, '/');
    if (str_starts_with($path, 'assets/')) {
        $path = substr($path, strlen('assets/'));
    }
    if (!str_starts_with($path, 'images/') && !str_starts_with($path, 'uploads/')) {
        if (is_file(BASE_PATH . '/assets/images/' . basename($path))) {
            $path = 'images/' . basename($path);
        }
    }
    $candidates = [
        BASE_PATH . '/assets/' . $path,
        BASE_PATH . '/public/assets/' . $path,
    ];
    foreach ($candidates as $full) {
        if (is_file($full)) {
            if (str_contains($full, DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
                return rtrim(BASE_URL, '/') . '/assets/' . preg_replace('#^images/|^uploads/#', '$0', $path);
            }
            return asset_url($path);
        }
    }
    return $placeholder;
}

/** Static design assets under public/assets (tourism hero, etc.) */
function public_asset_url(string $relativePath): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
}

function business_type_label(string $type): string
{
    return match ($type) {
        'food_vendor' => 'Food & Restaurants',
        'restaurant' => 'Restaurants & Cafes',
        'craft_business' => 'Handicrafts & Crafts',
        'travel_agency' => 'Travel Agencies',
        'resort' => 'Resorts & Stays',
        'recreation' => 'Recreation & Tours',
        'service' => 'Local Services',
        'pasalubong' => 'Pasalubong & Delicacies',
        'fresh_produce' => 'Fresh Produce',
        default => ucwords(str_replace('_', ' ', $type)),
    };
}

function business_type_icon(string $type): string
{
    return match ($type) {
        'food_vendor', 'restaurant' => 'bi-cup-hot',
        'resort' => 'bi-building',
        'travel_agency', 'recreation' => 'bi-tsunami',
        'craft_business', 'pasalubong' => 'bi-bag-check',
        'service' => 'bi-gear-wide-connected',
        'fresh_produce' => 'bi-basket',
        default => 'bi-shop',
    };
}

function db_column_exists(string $table, string $column): bool
{
    static $cache = [];
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return false;
    }
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    try {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        $cache[$key] = ((int) $stmt->fetchColumn()) > 0;
    } catch (Throwable $e) {
        $cache[$key] = false;
    }
    return $cache[$key];
}

function product_category_label(?string $category): string
{
    $category = (string) $category;
    return match ($category) {
        'local_delicacy' => 'Local Delicacy',
        'handicraft' => 'Handicraft',
        'fresh_produce' => 'Fresh Produce',
        'service' => 'Service',
        'tour_package' => 'Tour Package',
        'food' => 'Food',
        'accommodation' => 'Accommodation',
        'other' => 'Other',
        default => $category !== '' ? ucwords(str_replace('_', ' ', $category)) : 'Uncategorized',
    };
}

function product_type_label(?string $type): string
{
    $type = (string) $type;
    return match ($type) {
        'product' => 'Product',
        'service' => 'Service',
        'tour_package' => 'Tour Package',
        'accommodation' => 'Accommodation',
        'food' => 'Food',
        'other' => 'Other',
        default => $type !== '' ? ucwords(str_replace('_', ' ', $type)) : 'Product',
    };
}

function guest_action_blocked(): bool
{
    if (is_logged_in()) {
        return false;
    }
    set_flash('error', 'Please login or register to continue.');
    return true;
}

function require_local_user(): void
{
    require_login();
    if (current_user_role() !== 'local_user') {
        set_flash('error', 'This action is for local user accounts only.');
        redirect(BASE_URL . 'forbidden.php');
    }
}

function require_login_public(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please login or register to continue.';
        redirect(BASE_URL . 'login.php');
    }
}

function flash(string $key): ?string
{
    if (!isset($_SESSION['flash_' . $key])) {
        return null;
    }
    $msg = $_SESSION['flash_' . $key];
    unset($_SESSION['flash_' . $key]);
    return $msg;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash_' . $key] = $message;
}

function json_response(array $payload, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function log_activity(?int $userId, string $action, string $description, ?string $ip = null): void
{
    $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? null);
    $stmt = db()->prepare(
        'INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$userId, $action, $description, $ip]);
}

function notify_user(int $userId, string $title, string $message, string $type = 'info'): void
{
    $stmt = db()->prepare(
        'INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())'
    );
    $stmt->execute([$userId, $title, $message, $type]);
}

function save_upload(array $file, string $subfolder = ''): ?string
{
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, UPLOAD_ALLOWED_MIME, true)) {
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
        return null;
    }
    $dir = BASE_PATH . '/assets/uploads/' . trim($subfolder, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $relative = 'uploads/' . ($subfolder ? trim($subfolder, '/') . '/' : '') . $name;
    $dest = BASE_PATH . '/assets/' . $relative;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    return $relative;
}

function app_root_url(): string
{
    $root = rtrim(preg_replace('#/public/?$#', '', rtrim(BASE_URL, '/')), '/');
    return $root !== '' ? $root : '/likhalokal';
}

function asset_url(string $path): string
{
    return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
}

function google_maps_api_configured(): bool
{
    return GOOGLE_MAPS_API_KEY !== ''
        && GOOGLE_MAPS_API_KEY !== 'YOUR_GOOGLE_MAPS_API_KEY_HERE'
        && GOOGLE_MAPS_API_KEY !== 'PASTE_REAL_GOOGLE_MAPS_API_KEY_HERE';
}

function map_picker_footer_scripts(): string
{
    $html = '<script src="' . e(asset_url('js/map-picker.js')) . '?v=1"></script>';
    if (google_maps_api_configured()) {
        $html .= '<script async defer src="https://maps.googleapis.com/maps/api/js?key='
            . urlencode(GOOGLE_MAPS_API_KEY)
            . '&callback=initLikhaMapPickers"></script>';
    }
    return $html;
}

function business_avg_rating(int $businessId): float
{
    $stmt = db()->prepare(
        "SELECT AVG(rating) AS a FROM reviews WHERE business_id = ? AND status = 'approved'"
    );
    $stmt->execute([$businessId]);
    $row = $stmt->fetch();
    return round((float) ($row['a'] ?? 0), 1);
}

function attraction_avg_rating(int $attractionId): float
{
    $stmt = db()->prepare(
        "SELECT AVG(rating) AS a FROM reviews WHERE attraction_id = ? AND status = 'approved'"
    );
    $stmt->execute([$attractionId]);
    $row = $stmt->fetch();
    return round((float) ($row['a'] ?? 0), 1);
}

function unread_messages_count(int $userId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) AS c FROM messages WHERE receiver_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) ($stmt->fetch()['c'] ?? 0);
}

function profile_avatar_url(?string $fullName = null, ?string $profileImage = null): string
{
    if ($profileImage !== null && trim($profileImage) !== '') {
        return media_url($profileImage);
    }
    $name = trim((string) ($fullName ?? 'User'));
    if ($name === '') {
        $name = 'User';
    }
    return 'https://ui-avatars.com/api/?name=' . rawurlencode($name) . '&background=F39200&color=fff&size=128';
}

function render_star_rating(int $rating, int $max = 5): string
{
    $rating = max(0, min($max, $rating));
    $html = '<span class="lk-stars" aria-label="' . $rating . ' out of ' . $max . '">';
    for ($i = 1; $i <= $max; $i++) {
        $html .= $i <= $rating
            ? '<i class="bi bi-star-fill text-warning"></i>'
            : '<i class="bi bi-star text-warning opacity-50"></i>';
    }
    return $html . '</span>';
}

function review_status_badge_class(string $status): string
{
    return match ($status) {
        'approved' => 'success',
        'pending' => 'warning',
        'rejected' => 'danger',
        default => 'secondary',
    };
}

function format_datetime_short(?string $datetime): string
{
    if ($datetime === null || $datetime === '') {
        return '';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }
    $diff = time() - $ts;
    if ($diff < 60) {
        return 'Just now';
    }
    if ($diff < 3600) {
        return (int) floor($diff / 60) . 'm ago';
    }
    if ($diff < 86400) {
        return (int) floor($diff / 3600) . 'h ago';
    }
    if ($diff < 604800) {
        return (int) floor($diff / 86400) . 'd ago';
    }
    return date('M j, Y g:i A', $ts);
}

/** @return list<array<string, mixed>> */
function user_message_conversations(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT m.business_id,
                b.business_name,
                MAX(m.created_at) AS last_at,
                (SELECT m2.message_content FROM messages m2
                 WHERE m2.business_id = m.business_id
                   AND m2.conversation_type = \'business_inquiry\'
                   AND (m2.sender_id = ? OR m2.receiver_id = ?)
                 ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
                SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
         FROM messages m
         INNER JOIN businesses b ON b.id = m.business_id
         WHERE m.business_id IS NOT NULL
           AND m.conversation_type = \'business_inquiry\'
           AND (m.sender_id = ? OR m.receiver_id = ?)
         GROUP BY m.business_id, b.business_name
         ORDER BY last_at DESC'
    );
    $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
    return $stmt->fetchAll();
}

function profile_completion_percent(array $user): int
{
    $fields = 0;
    $filled = 0;
    foreach (['full_name', 'contact_number', 'profile_image'] as $key) {
        $fields++;
        if (!empty($user[$key])) {
            $filled++;
        }
    }
    $fields++;
    if (!empty($user['email'])) {
        $filled++;
    }
    return $fields > 0 ? (int) round(($filled / $fields) * 100) : 0;
}

/** Safe in-app return URL (public pages + user/seller dashboards). */
function is_safe_return_url(?string $url): bool
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
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host !== '' && !in_array($host, ['localhost', '127.0.0.1'], true)) {
            return false;
        }
        $path = ($parts['path'] ?? '') . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    $allowed = [
        'index.php', 'tourism.php', 'products.php', 'local-business.php', 'about.php',
        'vendor-profile.php', 'attraction-detail.php', 'events.php', 'cultural-info.php',
        'business-directory.php', 'search.php', 'message.php', 'register-business.php',
        '/user/', '/seller/', '/admin/', 'user/messages.php', 'user/dashboard.php', 'user/reviews.php',
        'user/profile.php', 'seller/messages.php',
    ];
    foreach ($allowed as $seg) {
        if (str_contains($path, $seg)) {
            return true;
        }
    }
    return false;
}

function return_url_path(?string $url): string
{
    if ($url === null || trim($url) === '') {
        return '';
    }
    $url = trim($url);
    $parts = parse_url($url);
    if ($parts === false) {
        return '';
    }
    $path = (string) ($parts['path'] ?? $url);
    return '/' . ltrim(str_replace('\\', '/', $path), '/');
}

function is_internal_return_context(?string $url): bool
{
    if (!is_safe_return_url($url)) {
        return false;
    }
    return (bool) preg_match('#/(user|seller|admin)/#', strtolower(return_url_path($url)));
}

function is_public_return_context(?string $url): bool
{
    if (!is_safe_return_url($url)) {
        return false;
    }
    $path = strtolower(return_url_path($url));
    return str_contains($path, '/public/')
        || (bool) preg_match('#/(index|tourism|products|local-business|about|events|cultural-info|attraction-detail|vendor-profile)\.php$#', $path);
}

function resolve_return_url(?string $requested, string $default): string
{
    if ($requested !== null && $requested !== '' && is_safe_return_url($requested)) {
        if (preg_match('#^https?://#i', $requested)) {
            return $requested;
        }
        if (str_starts_with($requested, '/')) {
            return $requested;
        }
        if (str_starts_with($requested, 'user/') || str_starts_with($requested, 'seller/') || str_starts_with($requested, 'admin/')) {
            $base = preg_replace('#/public/?$#', '', rtrim(BASE_URL, '/'));
            return $base . '/' . ltrim($requested, '/');
        }
        return BASE_URL . ltrim($requested, '/');
    }
    return $default;
}

function vendor_profile_url(int $businessId, ?string $returnTo = null): string
{
    $url = BASE_URL . 'vendor-profile.php?id=' . $businessId;
    if ($returnTo !== null && $returnTo !== '' && is_safe_return_url($returnTo)) {
        $url .= '&return=' . rawurlencode($returnTo);
    }
    return $url;
}

function business_status_badge_class(string $status): string
{
    return match ($status) {
        'approved' => 'success',
        'pending' => 'warning',
        'rejected' => 'danger',
        'suspended' => 'secondary',
        default => 'secondary',
    };
}

/** @return list<array<string, mixed>> */
function seller_message_threads(int $sellerUserId, int $businessId): array
{
    if ($businessId < 1) {
        return [];
    }
    $stmt = db()->prepare(
        'SELECT
            CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS customer_id,
            u.full_name AS customer_name,
            MAX(m.created_at) AS last_at,
            (SELECT m2.message_content FROM messages m2
             WHERE m2.business_id = ?
               AND m2.conversation_type = \'business_inquiry\'
               AND (m2.sender_id = u.id OR m2.receiver_id = u.id)
             ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
            SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
         FROM messages m
         INNER JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
         WHERE m.business_id = ?
           AND m.conversation_type = \'business_inquiry\'
           AND (m.sender_id = ? OR m.receiver_id = ?)
         GROUP BY customer_id, u.full_name
         HAVING customer_id != ?
         ORDER BY last_at DESC
         LIMIT 10'
    );
    $stmt->execute([
        $sellerUserId,
        $businessId,
        $sellerUserId,
        $sellerUserId,
        $businessId,
        $sellerUserId,
        $sellerUserId,
        $sellerUserId,
    ]);
    return $stmt->fetchAll();
}

/** @return list<array<string, mixed>> */
function admin_support_threads(int $adminId, ?string $filterRole = null): array
{
    $sql = "SELECT u.id AS peer_id, u.full_name, u.role, u.last_seen_at,
            (SELECT m2.message_content FROM messages m2
             WHERE m2.conversation_type = 'admin_support'
               AND ((m2.sender_id = u.id AND m2.receiver_id = ?) OR (m2.sender_id = ? AND m2.receiver_id = u.id))
             ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
            (SELECT MAX(created_at) FROM messages m3
             WHERE m3.conversation_type = 'admin_support'
               AND ((m3.sender_id = u.id AND m3.receiver_id = ?) OR (m3.sender_id = ? AND m3.receiver_id = u.id))) AS last_at,
            (SELECT COUNT(*) FROM messages m4
             WHERE m4.conversation_type = 'admin_support' AND m4.sender_id = u.id AND m4.receiver_id = ? AND m4.is_read = 0) AS unread_count
            FROM users u
            WHERE u.role != 'admin' AND u.id IN (
                SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END
                FROM messages WHERE conversation_type = 'admin_support' AND (sender_id = ? OR receiver_id = ?)
            )";
    $params = [$adminId, $adminId, $adminId, $adminId, $adminId, $adminId, $adminId, $adminId];
    if ($filterRole !== null && $filterRole !== '') {
        $sql .= ' AND u.role = ?';
        $params[] = $filterRole;
    }
    $sql .= ' ORDER BY last_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function role_display_label(string $role): string
{
    return match ($role) {
        'local_user' => 'Local User',
        'seller' => 'Seller',
        'admin' => 'Tourism Admin',
        default => ucfirst(str_replace('_', ' ', $role)),
    };
}

/** @return list<string> */
function business_faq_column_names(string $type): array
{
    return match ($type) {
        'price' => ['faq_price', 'faq_price_response'],
        'availability' => ['faq_availability', 'faq_availability_response'],
        'location' => ['faq_location', 'faq_location_response'],
        'payment' => ['faq_payment', 'faq_payment_response'],
        'pickup_delivery' => ['faq_delivery', 'faq_pickup_delivery_response'],
        'hours' => ['faq_hours', 'faq_hours_response'],
        'custom' => ['faq_custom', 'faq_custom_response'],
        default => ['faq_custom', 'faq_custom_response'],
    };
}

function business_faq_value(array $business, string $type): string
{
    foreach (business_faq_column_names($type) as $column) {
        if (!array_key_exists($column, $business)) {
            continue;
        }
        $value = trim((string) $business[$column]);
        if ($value !== '') {
            return $value;
        }
    }
    return '';
}

function business_auto_reply_enabled(array $business): bool
{
    if (!db_column_exists('businesses', 'auto_reply_enabled')) {
        return true;
    }
    return (int) ($business['auto_reply_enabled'] ?? 0) === 1;
}

function message_matches_faq_keyword(string $text, string $keyword): bool
{
    $keyword = mb_strtolower(trim($keyword));
    if ($keyword === '') {
        return false;
    }
    if (mb_strlen($keyword) <= 3) {
        return preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $text) === 1;
    }
    return str_contains($text, $keyword);
}

function detect_auto_reply_type(string $message): string
{
    $text = mb_strtolower(trim($message));
    if ($text === '') {
        return 'default';
    }

    $map = [
        'hours' => [
            'operating hours', 'what time', 'anong oras', 'opening hours', 'closing time',
            'open', 'opening', 'close', 'closing', 'hours', 'schedule', 'bukas', 'sarado',
        ],
        'price' => ['how much', 'magkano', 'price', 'prices', 'presyo', 'cost', 'rate', 'rates', 'bayad'],
        'availability' => [
            'still available', 'available pa', 'may stock', 'in stock', 'available', 'availability', 'meron',
        ],
        'location' => [
            'shop location', 'directions', 'located', 'location', 'address', 'saan', 'san', 'map', 'where',
        ],
        'payment' => [
            'mode of payment', 'gcash', 'maya', 'payment', 'transfer', 'bank', 'cash', 'pay', 'cod',
        ],
        'pickup_delivery' => [
            'pick up', 'pickup', 'meet up', 'delivery', 'deliver', 'shipping', 'lalamove', 'courier',
        ],
        'custom' => [],
    ];

    foreach ($map as $type => $keywords) {
        usort($keywords, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));
        foreach ($keywords as $keyword) {
            if (message_matches_faq_keyword($text, $keyword)) {
                return $type;
            }
        }
    }

    return 'default';
}

function build_auto_reply_text(array $business, string $userMessage, ?string $productName = null): string
{
    $type = detect_auto_reply_type($userMessage);

    if ($type === 'default') {
        $text = trim((string) ($business['auto_reply_message'] ?? ''));
        if ($text === '') {
            $text = business_faq_value($business, 'custom');
        }
    } else {
        $text = business_faq_value($business, $type);
        if ($text === '') {
            $text = trim((string) ($business['auto_reply_message'] ?? ''));
        }
    }

    if ($text === '') {
        $text = "Hi! Thanks for your inquiry. We'll get back to you soon.";
    }

    $productLabel = ($productName !== null && trim($productName) !== '') ? trim($productName) : 'this item';
    $replacements = [
        '{business_name}' => (string) ($business['business_name'] ?? ''),
        '{product_name}' => $productLabel,
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

function should_send_auto_reply(int $businessId, int $sellerId, int $customerId): bool
{
    $convFilter = db_column_exists('messages', 'conversation_type')
        ? " AND conversation_type = 'business_inquiry'"
        : '';

    $stmt = db()->prepare(
        'SELECT sender_id, is_auto_reply, created_at
         FROM messages
         WHERE business_id = ?
           AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))'
        . $convFilter . '
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $stmt->execute([$businessId, $sellerId, $customerId, $customerId, $sellerId]);
    $last = $stmt->fetch();
    if ($last
        && (int) ($last['is_auto_reply'] ?? 0) === 1
        && (int) ($last['sender_id'] ?? 0) === $sellerId
    ) {
        $ts = strtotime((string) ($last['created_at'] ?? ''));
        if ($ts !== false && (time() - $ts) < 60) {
            return false;
        }
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM messages
         WHERE business_id = ? AND sender_id = ? AND receiver_id = ? AND is_auto_reply = 1'
        . $convFilter . '
           AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'
    );
    $stmt->execute([$businessId, $sellerId, $customerId]);
    return (int) $stmt->fetchColumn() === 0;
}

/**
 * Insert seller auto-reply for a business inquiry. Returns new message ID or null if skipped.
 */
function insert_business_auto_reply(
    int $businessId,
    int $customerId,
    int $sellerId,
    string $userMessage,
    ?int $productId
): ?int {
    $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? LIMIT 1');
    $stmt->execute([$businessId]);
    $business = $stmt->fetch();
    if (!$business || !business_auto_reply_enabled($business)) {
        return null;
    }
    if (!should_send_auto_reply($businessId, $sellerId, $customerId)) {
        return null;
    }

    $productName = null;
    if ($productId) {
        $ps = db()->prepare('SELECT product_name FROM products WHERE id = ? LIMIT 1');
        $ps->execute([$productId]);
        $pr = $ps->fetch();
        $productName = $pr['product_name'] ?? null;
    }

    $replyText = build_auto_reply_text($business, $userMessage, $productName);
    $content = $replyText !== '' ? $replyText : "Hi! Thanks for your inquiry. We'll get back to you soon.";

    $stmt = db()->prepare(
        'INSERT INTO messages (sender_id, receiver_id, business_id, product_id, message_content, is_read, is_auto_reply, attachment_path, attachment_type, inquiry_context, conversation_type, created_at)
         VALUES (?,?,?,?,?,0,1,NULL,NULL,NULL,\'business_inquiry\',NOW())'
    );
    $stmt->execute([$sellerId, $customerId, $businessId, $productId, $content]);

    return (int) db()->lastInsertId();
}

function current_request_return_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri !== '' && is_safe_return_url($uri)) {
        return $uri;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if ($script !== '') {
        $path = $script . ($qs !== '' ? '?' . $qs : '');
        if (is_safe_return_url($path)) {
            return $path;
        }
    }
    return '';
}
