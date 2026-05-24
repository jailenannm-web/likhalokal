<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

$oauthSource = (string) ($_SESSION['google_oauth_source'] ?? 'login');
$failureUrl = BASE_URL . ($oauthSource === 'register' ? 'register.php' : 'login.php');

if (!google_oauth_configured()) {
    set_flash('error', 'Google sign-in is not configured.');
    redirect($failureUrl);
}

$state = (string) ($_GET['state'] ?? '');
$expectedState = (string) ($_SESSION['google_oauth_state'] ?? $_SESSION['oauth_state'] ?? '');
if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
    unset($_SESSION['google_oauth_state'], $_SESSION['oauth_state'], $_SESSION['google_oauth_source']);
    set_flash('error', 'Google sign-in failed. Please try again.');
    redirect($failureUrl);
}
unset($_SESSION['google_oauth_state'], $_SESSION['oauth_state'], $_SESSION['google_oauth_source']);

if (isset($_GET['error'])) {
    set_flash('error', 'Google sign-in was cancelled.');
    redirect($failureUrl);
}

$code = (string) ($_GET['code'] ?? '');
if ($code === '') {
    set_flash('error', 'Google sign-in failed. Please try again.');
    redirect($failureUrl);
}

try {
    $profile = google_oauth_profile_from_code($code);
    if ($profile === null) {
        set_flash('error', 'Google sign-in failed. Please try again.');
        redirect($failureUrl);
    }

    $email = strtolower(trim((string) $profile['email']));
    $googleId = trim((string) $profile['google_id']);
    $fullName = trim((string) $profile['name']);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $googleId === '') {
        set_flash('error', 'Google sign-in failed. Please try again.');
        redirect($failureUrl);
    }
    if (empty($profile['email_verified'])) {
        set_flash('error', 'Your Google email must be verified before signing in.');
        redirect($failureUrl);
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? OR google_id = ? LIMIT 1');
    $stmt->execute([$email, $googleId]);
    $user = $stmt->fetch();

    if ($user) {
        if (!user_status_allows_login((string) $user['status'], (string) $user['role'])) {
            set_flash('error', 'Your account is not active. Please contact support.');
            redirect($failureUrl);
        }

        if (!empty($user['google_id']) && !hash_equals((string) $user['google_id'], $googleId)) {
            set_flash('error', 'Google sign-in failed. Please try again.');
            redirect($failureUrl);
        }

        $updates = [];
        $params = [];
        if (empty($user['google_id'])) {
            $updates[] = 'google_id = ?';
            $params[] = $googleId;
        }
        if (empty($user['auth_provider']) || $user['auth_provider'] === 'local') {
            $updates[] = 'auth_provider = ?';
            $params[] = empty($user['password_hash']) ? 'google' : 'local';
        }
        if (db_column_exists('users', 'email_verified_at') && empty($user['email_verified_at'])) {
            $updates[] = 'email_verified_at = NOW()';
        }
        if ($updates) {
            $updates[] = 'updated_at = NOW()';
            $params[] = (int) $user['id'];
            db()->prepare('UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);
        }

        $user['google_id'] = $googleId;
        login_user($user, false);
        log_activity((int) $user['id'], 'login', 'Google OAuth login', $_SERVER['REMOTE_ADDR'] ?? null);
        set_flash('success', 'Welcome back!');
        redirect_by_role();
    }

    $stmt = db()->prepare(
        'INSERT INTO users (full_name, email, google_id, auth_provider, password_hash, role, status, email_verified_at, created_at, updated_at)
         VALUES (?,?,?,?,?,\'local_user\',\'active\',NOW(),NOW(),NOW())'
    );
    $stmt->execute([
        $fullName !== '' ? $fullName : $email,
        $email,
        $googleId,
        'google',
        null,
    ]);
    $id = (int) db()->lastInsertId();

    login_user([
        'id' => $id,
        'full_name' => $fullName !== '' ? $fullName : $email,
        'email' => $email,
        'role' => 'local_user',
    ], false);
    log_activity($id, 'register', 'Google OAuth signup', $_SERVER['REMOTE_ADDR'] ?? null);
    set_flash('success', 'Welcome to LikhaLokal!');
    redirect_by_role();
} catch (Throwable $e) {
    error_log('Google OAuth error: ' . $e->getMessage());
    set_flash('error', 'Google sign-in failed. Please try again.');
    redirect($failureUrl);
}

/**
 * @return array{google_id:string,email:string,email_verified:bool,name:string}|null
 */
function google_oauth_profile_from_code(string $code): ?array
{
    $autoload = BASE_PATH . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }

    if (class_exists('Google\Client')) {
        return google_oauth_profile_with_client($code);
    }

    return google_oauth_profile_with_http($code);
}

/**
 * @return array{google_id:string,email:string,email_verified:bool,name:string}|null
 */
function google_oauth_profile_with_client(string $code): ?array
{
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->setScopes(['openid', 'email', 'profile']);

    $token = $client->fetchAccessTokenWithAuthCode($code);
    if (!is_array($token) || empty($token['access_token'])) {
        return null;
    }

    $payload = null;
    if (!empty($token['id_token'])) {
        $payload = $client->verifyIdToken((string) $token['id_token']);
    }
    if (!is_array($payload)) {
        $oauth = new Google\Service\Oauth2($client);
        $info = $oauth->userinfo->get();
        $payload = [
            'sub' => (string) $info->id,
            'email' => (string) $info->email,
            'email_verified' => (bool) $info->verifiedEmail,
            'name' => (string) $info->name,
        ];
    }

    return normalize_google_profile($payload);
}

/**
 * @return array{google_id:string,email:string,email_verified:bool,name:string}|null
 */
function google_oauth_profile_with_http(string $code): ?array
{
    $token = google_http_post('https://oauth2.googleapis.com/token', [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]);
    if (!$token || empty($token['access_token'])) {
        return null;
    }

    $payload = null;
    if (!empty($token['id_token'])) {
        $payload = google_http_get('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode((string) $token['id_token']));
        if (is_array($payload) && (($payload['aud'] ?? '') !== GOOGLE_CLIENT_ID)) {
            return null;
        }
    }

    if (!is_array($payload)) {
        $payload = google_http_get('https://www.googleapis.com/oauth2/v3/userinfo', (string) $token['access_token']);
    }

    return is_array($payload) ? normalize_google_profile($payload) : null;
}

/**
 * @param array<string,mixed> $payload
 * @return array{google_id:string,email:string,email_verified:bool,name:string}|null
 */
function normalize_google_profile(array $payload): ?array
{
    $googleId = (string) ($payload['sub'] ?? $payload['id'] ?? '');
    $email = strtolower(trim((string) ($payload['email'] ?? '')));
    $verified = $payload['email_verified'] ?? $payload['verified_email'] ?? false;
    $name = trim((string) ($payload['name'] ?? $payload['given_name'] ?? $email));

    if ($googleId === '' || $email === '') {
        return null;
    }

    return [
        'google_id' => $googleId,
        'email' => $email,
        'email_verified' => filter_var($verified, FILTER_VALIDATE_BOOLEAN),
        'name' => $name,
    ];
}

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
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($raw === false || $status < 200 || $status >= 300) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

/**
 * @return array<string, mixed>|null
 */
function google_http_get(string $url, ?string $accessToken = null): ?array
{
    $headers = [];
    if ($accessToken !== null && $accessToken !== '') {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($raw === false || $status < 200 || $status >= 300) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}
