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
    return substr($s, 0, $n) . '…';
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

function asset_url(string $path): string
{
    return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
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
