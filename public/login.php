<?php

declare(strict_types=1);

$pageTitle = 'Login';
$activeNav = '';
$bodyClass = 'lk-auth-page';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (is_logged_in()) {
    redirect_by_role();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once BASE_PATH . '/middleware/csrf.php';
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid session token.');
        redirect(BASE_URL . 'login.php');
    }
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $remember = !empty($_POST['remember_me']);
    $stmt = db()->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !user_status_allows_login((string) $user['status'], (string) ($user['role'] ?? ''))) {
        set_flash('error', 'Invalid credentials or inactive account.');
        redirect(BASE_URL . 'login.php');
    }
    if (empty($user['password_hash'])) {
        set_flash('error', 'Please continue with Google or reset your password to create a local password.');
        redirect(BASE_URL . 'login.php');
    }
    if (!password_verify($password, (string) $user['password_hash'])) {
        set_flash('error', 'Invalid credentials or suspended account.');
        redirect(BASE_URL . 'login.php');
    }
    login_user($user, $remember);
    log_activity((int) $user['id'], 'login', 'Web login', $_SERVER['REMOTE_ADDR'] ?? null);
    set_flash('success', 'Welcome back!');
    redirect_by_role();
}

$loginRedirect = peek_login_redirect();
require_once BASE_PATH . '/middleware/csrf.php';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="lk-auth-wrap">
    <div class="row justify-content-center w-100">
        <div class="col-md-5">
            <div class="card card-lk shadow lk-auth-card">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Welcome back</h1>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <?php if ($loginRedirect !== null && is_safe_post_login_redirect($loginRedirect)): ?>
                        <input type="hidden" name="redirect" value="<?= e($loginRedirect) ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-flex justify-content-between">
                                <span>Password</span>
                                <a href="<?= e(BASE_URL) ?>forgot-password.php" class="small">Forgot password?</a>
                            </label>
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="current-password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember_me" id="rememberMe" value="1">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <button class="btn btn-lk-orange w-100 mb-3" type="submit">Login</button>
                    </form>
                    <div class="lk-auth-divider auth-divider"><span>or</span></div>
                    <?php if (google_oauth_configured()): ?>
                        <?php
                        $googleLoginUrl = BASE_URL . 'google-login.php?source=login';
                        if ($loginRedirect !== null && is_safe_post_login_redirect($loginRedirect)) {
                            $googleLoginUrl .= '&redirect=' . rawurlencode($loginRedirect);
                        }
                        ?>
                        <a href="<?= e($googleLoginUrl) ?>" class="btn btn-lk-google google-auth-btn w-100">
                            <span class="lk-google-g" aria-hidden="true">G</span> Continue with Google
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-lk-google google-auth-btn w-100" disabled>
                            <span class="lk-google-g" aria-hidden="true">G</span> Continue with Google
                        </button>
                        <p class="small text-muted text-center mt-2 mb-0">Set Google OAuth credentials to enable Google sign-in.</p>
                    <?php endif; ?>
                    <p class="auth-switch-text">Don&rsquo;t have an account? <a href="<?= e(BASE_URL) ?>register.php">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
