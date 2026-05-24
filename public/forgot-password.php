<?php

declare(strict_types=1);

$pageTitle = 'Forgot password';
$activeNav = '';
$bodyClass = 'lk-auth-page';
require_once dirname(__DIR__) . '/bootstrap.php';

$debugLink = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once BASE_PATH . '/middleware/csrf.php';
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid session token.');
        redirect(BASE_URL . 'forgot-password.php');
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare('SELECT id, password_hash, auth_provider, google_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $isGoogleOnly = $user
            && (string) ($user['auth_provider'] ?? '') === 'google'
            && !empty($user['google_id']);

        if ($isGoogleOnly) {
            $_SESSION['flash_google_reset_notice'] = true;
            redirect(BASE_URL . 'forgot-password.php');
        }

        if ($user && !$isGoogleOnly && !empty($user['password_hash'])) {
            $token = create_password_reset($email);
            if ($token) {
                $resetUrl = rtrim(
                    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
                    '/'
                ) . BASE_URL . 'reset-password.php?email=' . rawurlencode($email) . '&token=' . rawurlencode($token);
                $body = '<p>Hi,</p>'
                    . '<p>We received a request to reset your LikhaLokal password.</p>'
                    . '<p>Click the link below to reset your password:<br>'
                    . '<a href="' . htmlspecialchars($resetUrl) . '">' . htmlspecialchars($resetUrl) . '</a></p>'
                    . '<p>This link expires in 1 hour.</p>'
                    . '<p>If you did not request this, ignore this email.</p>';
                if (!send_app_mail($email, 'LikhaLokal Password Reset', $body) && APP_DEBUG) {
                    $debugLink = $resetUrl;
                }
            }
        }
    }
    set_flash('success', 'If that email exists, we sent a password reset link.');
    if (APP_DEBUG && $debugLink) {
        $_SESSION['flash_debug_reset'] = $debugLink;
    }
    redirect(BASE_URL . 'forgot-password.php');
}

require_once BASE_PATH . '/middleware/csrf.php';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
$debugReset = $_SESSION['flash_debug_reset'] ?? null;
unset($_SESSION['flash_debug_reset']);
$googleResetNotice = !empty($_SESSION['flash_google_reset_notice']);
unset($_SESSION['flash_google_reset_notice']);
?>
<div class="lk-auth-wrap">
    <div class="row justify-content-center w-100">
        <div class="col-md-5">
            <div class="card card-lk shadow lk-auth-card">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Forgot password</h1>
                    <?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <?php if ($googleResetNotice): ?>
                        <div class="alert alert-info">
                            This account uses Google sign-in. Please use Continue with Google to log in.
                            <div class="mt-2">
                                <a class="btn btn-sm btn-lk-google google-auth-btn" href="<?= e(BASE_URL) ?>google-login.php?source=login">
                                    <span class="lk-google-g" aria-hidden="true">G</span> Continue with Google
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($debugReset): ?><div class="alert alert-info small"><strong>Debug reset link:</strong><br><a href="<?= e($debugReset) ?>"><?= e($debugReset) ?></a></div><?php endif; ?>
                    <p class="text-muted small">Enter your email and we&rsquo;ll send reset instructions if an account exists.</p>
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button class="btn btn-lk-orange w-100" type="submit">Send reset link</button>
                    </form>
                    <p class="text-center small mt-4 mb-0"><a href="<?= e(BASE_URL) ?>login.php">Back to login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
