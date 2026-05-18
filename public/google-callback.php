<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (!google_oauth_configured()) {
    set_flash('error', 'Google login is not configured.');
    redirect(BASE_URL . 'login.php');
}

$state = (string) ($_GET['state'] ?? '');
$expectedState = (string) ($_SESSION['google_oauth_state'] ?? $_SESSION['oauth_state'] ?? '');
if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
    set_flash('error', 'Invalid OAuth state. Please try again.');
    redirect(BASE_URL . 'login.php');
}
unset($_SESSION['google_oauth_state'], $_SESSION['oauth_state']);

if (isset($_GET['error'])) {
    set_flash('error', 'Google sign-in was cancelled.');
    redirect(BASE_URL . 'login.php');
}

$code = (string) ($_GET['code'] ?? '');
if ($code === '') {
    set_flash('error', 'Missing authorization code.');
    redirect(BASE_URL . 'login.php');
}

$tokenResponse = google_http_post('https://oauth2.googleapis.com/token', [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
]);

if (!$tokenResponse || empty($tokenResponse['access_token'])) {
    set_flash('error', 'Could not complete Google sign-in.');
    redirect(BASE_URL . 'login.php');
}

$userInfo = google_http_get(
    'https://www.googleapis.com/oauth2/v2/userinfo',
    (string) $tokenResponse['access_token']
);

if (!$userInfo || empty($userInfo['email'])) {
    set_flash('error', 'Could not read Google profile.');
    redirect(BASE_URL . 'login.php');
}

$email = strtolower(trim((string) $userInfo['email']));
$googleId = (string) ($userInfo['id'] ?? '');
$fullName = trim((string) ($userInfo['name'] ?? $email));

$stmt = db()->prepare('SELECT * FROM users WHERE email = ? OR google_id = ? LIMIT 1');
$stmt->execute([$email, $googleId]);
$user = $stmt->fetch();

if ($user) {
    if (!user_status_allows_login((string) $user['status'], (string) $user['role'])) {
        set_flash('error', 'Your account is not active. Please contact support.');
        redirect(BASE_URL . 'login.php');
    }
    if ($googleId !== '' && empty($user['google_id'])) {
        if (empty($user['password_hash'])) {
            db()->prepare('UPDATE users SET google_id = ?, auth_provider = \'google\', updated_at = NOW() WHERE id = ?')
                ->execute([$googleId, (int) $user['id']]);
        } else {
            db()->prepare('UPDATE users SET google_id = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$googleId, (int) $user['id']]);
        }
    }
    login_user($user, false);
    log_activity((int) $user['id'], 'login', 'Google OAuth login', $_SERVER['REMOTE_ADDR'] ?? null);
    if ($user['status'] === 'pending' && $user['role'] !== 'admin') {
        redirect(BASE_URL . 'complete-account.php');
    }
    set_flash('success', 'Welcome back!');
    redirect_by_role();
}

$stmt = db()->prepare(
    'INSERT INTO users (full_name, email, google_id, auth_provider, password_hash, role, status, created_at, updated_at)
     VALUES (?,?,?,?,NULL,?,\'active\',NOW(),NOW())'
);
$stmt->execute([$fullName, $email, $googleId, 'google', 'local_user']);
$id = (int) db()->lastInsertId();

login_user([
    'id' => $id,
    'full_name' => $fullName,
    'email' => $email,
    'role' => 'local_user',
], false);
$_SESSION['needs_role_completion'] = true;

log_activity($id, 'register', 'Google OAuth signup', $_SERVER['REMOTE_ADDR'] ?? null);
redirect(BASE_URL . 'complete-account.php');

/**
 * @return array<string, mixed>|null
 */
function google_http_post(string $url, array $fields): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

/**
 * @return array<string, mixed>|null
 */
function google_http_get(string $url, string $accessToken): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}
