<?php

declare(strict_types=1);

function init_secure_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function login_user(array $user, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = (string) $user['full_name'];
    $_SESSION['user_email'] = (string) $user['email'];
    $_SESSION['user_role'] = (string) $user['role'];
    $_SESSION['role'] = (string) $user['role'];
    if ($remember) {
        issue_remember_token((int) $user['id']);
    }
    update_last_seen((int) $user['id']);
}

function logout_user(): void
{
    $uid = current_user_id();
    if ($uid) {
        clear_remember_tokens_for_user($uid);
    }
    clear_remember_cookie();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function try_remember_login(): void
{
    if (is_logged_in()) {
        return;
    }
    $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? '';
    if ($cookie === '' || !str_contains($cookie, ':')) {
        return;
    }
    if (!db_column_exists('user_remember_tokens', 'selector')) {
        return;
    }
    [$selector, $validator] = explode(':', $cookie, 2);
    if ($selector === '' || $validator === '') {
        clear_remember_cookie();
        return;
    }
    $stmt = db()->prepare(
        'SELECT rt.*, u.id, u.full_name, u.email, u.role, u.status
         FROM user_remember_tokens rt
         INNER JOIN users u ON u.id = rt.user_id
         WHERE rt.selector = ? AND rt.expires_at > NOW() LIMIT 1'
    );
    $stmt->execute([$selector]);
    $row = $stmt->fetch();
    if (!$row || !hash_equals((string) $row['token_hash'], hash('sha256', $validator))) {
        if ($row) {
            db()->prepare('DELETE FROM user_remember_tokens WHERE id = ?')->execute([(int) $row['id']]);
        }
        clear_remember_cookie();
        return;
    }
    if ($row['status'] === 'suspended') {
        clear_remember_cookie();
        return;
    }
    login_user($row, false);
}

function issue_remember_token(int $userId): void
{
    if (!db_column_exists('user_remember_tokens', 'selector')) {
        return;
    }
    clear_remember_tokens_for_user($userId);
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $hash = hash('sha256', $validator);
    $expires = date('Y-m-d H:i:s', time() + REMEMBER_DAYS * 86400);
    db()->prepare(
        'INSERT INTO user_remember_tokens (user_id, selector, token_hash, expires_at, created_at) VALUES (?,?,?,?,NOW())'
    )->execute([$userId, $selector, $hash, $expires]);
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie(
        REMEMBER_COOKIE_NAME,
        $selector . ':' . $validator,
        [
            'expires' => time() + REMEMBER_DAYS * 86400,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function clear_remember_tokens_for_user(int $userId): void
{
    if (!db_column_exists('user_remember_tokens', 'selector')) {
        return;
    }
    db()->prepare('DELETE FROM user_remember_tokens WHERE user_id = ?')->execute([$userId]);
}

function clear_remember_cookie(): void
{
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie(REMEMBER_COOKIE_NAME, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function update_last_seen(?int $userId = null): void
{
    $userId = $userId ?? current_user_id();
    if (!$userId || !db_column_exists('users', 'last_seen_at')) {
        return;
    }
    static $updatedThisRequest = false;
    if ($updatedThisRequest) {
        return;
    }
    $updatedThisRequest = true;
    db()->prepare('UPDATE users SET last_seen_at = NOW() WHERE id = ?')->execute([$userId]);
}

function user_activity_status(?string $lastSeenAt): string
{
    if ($lastSeenAt === null || $lastSeenAt === '') {
        return 'Offline';
    }
    $ts = strtotime($lastSeenAt);
    if ($ts === false) {
        return 'Offline';
    }
    $diff = time() - $ts;
    if ($diff < 300) {
        return 'Active now';
    }
    if ($diff < 3600) {
        return 'Last seen ' . (int) floor($diff / 60) . ' min ago';
    }
    if ($diff < 86400) {
        return 'Last seen ' . (int) floor($diff / 3600) . ' hr ago';
    }
    return 'Offline · ' . date('M j, g:i A', $ts);
}

function normalize_message_attachment_path(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $path = preg_replace('#^.*htdocs/likhalokal/#i', '', $path);
    $path = preg_replace('#^/?likhalokal/#i', '', $path);
    $path = ltrim($path, '/');
    if ($path === '') {
        return '';
    }
    if (!str_starts_with($path, 'assets/')) {
        if (str_starts_with($path, 'uploads/')) {
            $path = 'assets/' . $path;
        } else {
            $path = 'assets/uploads/messages/' . basename($path);
        }
    }
    return $path;
}

function smtp_configured(): bool
{
    return MAIL_HOST !== '' && MAIL_USERNAME !== '';
}

function send_app_mail(string $to, string $subject, string $bodyHtml): bool
{
    if (!smtp_configured()) {
        return false;
    }
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $autoload = BASE_PATH . '/vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }
    }
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return false;
    }
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $encryption = strtolower(MAIL_ENCRYPTION);
        $mail->SMTPSecure = $encryption === 'ssl'
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : ($encryption === 'none' ? '' : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
        $mail->Port = MAIL_PORT;
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

function create_password_reset(string $email): ?string
{
    if (!db_column_exists('password_resets', 'token_hash')) {
        return null;
    }
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + 3600);
    db()->prepare('DELETE FROM password_resets WHERE email = ? AND used_at IS NULL')->execute([$email]);
    db()->prepare(
        'INSERT INTO password_resets (email, token_hash, expires_at, created_at) VALUES (?,?,?,NOW())'
    )->execute([$email, $hash, $expires]);
    return $token;
}

function validate_password_reset(string $email, string $token): ?array
{
    if (!db_column_exists('password_resets', 'token_hash')) {
        return null;
    }
    $hash = hash('sha256', $token);
    $stmt = db()->prepare(
        'SELECT * FROM password_resets WHERE email = ? AND token_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$email, $hash]);
    return $stmt->fetch() ?: null;
}

function mark_password_reset_used(int $id): void
{
    db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$id]);
}

function first_admin_user_id(): ?int
{
    $stmt = db()->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active' ORDER BY id ASC LIMIT 1");
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function detect_auto_reply_type(string $message): string
{
    $text = mb_strtolower(trim($message));
    $map = [
        'price' => ['price', 'prices', 'how much', 'magkano', 'cost', 'rate', 'rates', 'bayad', 'presyo'],
        'availability' => ['available', 'availability', 'meron', 'may stock', 'in stock', 'still available', 'available pa'],
        'location' => ['where', 'located', 'location', 'address', 'saan', 'san', 'shop location', 'directions', 'map'],
        'payment' => ['payment', 'pay', 'gcash', 'maya', 'cash', 'bank', 'transfer', 'cod', 'mode of payment'],
        'pickup_delivery' => ['pickup', 'pick up', 'delivery', 'deliver', 'shipping', 'meet up', 'lalamove', 'courier'],
        'hours' => ['open', 'opening', 'close', 'closing', 'hours', 'time', 'schedule', 'what time', 'operating hours', 'bukas', 'sarado', 'anong oras'],
    ];
    foreach ($map as $type => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return $type;
            }
        }
    }
    return 'default';
}

function business_faq_column(string $type): string
{
    return match ($type) {
        'price' => 'faq_price',
        'availability' => 'faq_availability',
        'location' => 'faq_location',
        'payment' => 'faq_payment',
        'pickup_delivery' => 'faq_delivery',
        'hours' => 'faq_hours',
        default => 'faq_custom',
    };
}

function build_auto_reply_text(array $business, string $userMessage, ?string $productName = null): string
{
    $type = detect_auto_reply_type($userMessage);
    $col = business_faq_column($type);
    $text = trim((string) ($business[$col] ?? ''));
    if ($text === '' && $type !== 'default') {
        $text = trim((string) ($business['auto_reply_message'] ?? ''));
    }
    if ($text === '') {
        $text = "Hi! Thanks for your inquiry. We'll get back to you soon.";
    }
    $replacements = [
        '{business_name}' => (string) ($business['business_name'] ?? ''),
        '{product_name}' => (string) ($productName ?? ''),
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

function should_send_auto_reply(int $businessId, int $sellerId, int $customerId): bool
{
    $stmt = db()->prepare(
        'SELECT id FROM messages
         WHERE business_id = ? AND sender_id = ? AND receiver_id = ? AND is_auto_reply = 1
         ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute([$businessId, $sellerId, $customerId]);
    $last = $stmt->fetch();
    if (!$last) {
        return true;
    }
    $stmt = db()->prepare('SELECT created_at FROM messages WHERE id = ?');
    $stmt->execute([(int) $last['id']]);
    $row = $stmt->fetch();
    if (!$row) {
        return true;
    }
    $ts = strtotime((string) $row['created_at']);
    return $ts === false || (time() - $ts) >= 86400;
}

function message_attachment_url(?string $path): ?string
{
    if ($path === null || trim($path) === '') {
        return null;
    }
    $path = normalize_message_attachment_path($path);
    if ($path === '') {
        return null;
    }
    return rtrim(app_root_url(), '/') . '/' . $path;
}

function save_message_upload(array $file): ?array
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
    $dir = BASE_PATH . '/assets/uploads/messages';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    return [
        'path' => 'assets/uploads/messages/' . $name,
        'type' => str_starts_with($mime, 'image/') ? 'image' : 'file',
    ];
}
