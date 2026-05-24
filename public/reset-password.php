<?php

declare(strict_types=1);

$pageTitle = 'Reset password';
$activeNav = '';
$bodyClass = 'lk-auth-page';
require_once dirname(__DIR__) . '/bootstrap.php';

$email = strtolower(trim((string) ($_GET['email'] ?? $_POST['email'] ?? '')));
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once BASE_PATH . '/middleware/csrf.php';
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid session token.');
        redirect(BASE_URL . 'reset-password.php?email=' . rawurlencode($email) . '&token=' . rawurlencode($token));
    }
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (strlen($password) < 8 || $password !== $confirm) {
        set_flash('error', 'Password must be at least 8 characters and match confirmation.');
        redirect(BASE_URL . 'reset-password.php?email=' . rawurlencode($email) . '&token=' . rawurlencode($token));
    }
    $row = validate_password_reset($email, $token);
    if (!$row) {
        set_flash('error', 'Invalid or expired reset link.');
        redirect(BASE_URL . 'forgot-password.php');
    }
    $ustmt = db()->prepare('SELECT id, password_hash, auth_provider, google_id FROM users WHERE email = ? LIMIT 1');
    $ustmt->execute([$email]);
    $user = $ustmt->fetch();
    $isGoogleOnly = $user
        && (string) ($user['auth_provider'] ?? '') === 'google'
        && !empty($user['google_id']);
    if (!$user || $isGoogleOnly) {
        mark_password_reset_used((int) $row['id']);
        set_flash('error', 'This account uses Google sign-in. Please use Continue with Google to log in.');
        redirect(BASE_URL . 'login.php');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    db()->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE email = ?')->execute([$hash, $email]);
    mark_password_reset_used((int) $row['id']);
    set_flash('success', 'Password updated. You can sign in now.');
    redirect(BASE_URL . 'login.php');
}

$row = ($email !== '' && $token !== '') ? validate_password_reset($email, $token) : null;
if (!$row) {
    set_flash('error', 'Invalid or expired reset link.');
    redirect(BASE_URL . 'forgot-password.php');
}
$ustmt = db()->prepare('SELECT id, password_hash, auth_provider, google_id FROM users WHERE email = ? LIMIT 1');
$ustmt->execute([$email]);
$user = $ustmt->fetch();
$isGoogleOnly = $user
    && (string) ($user['auth_provider'] ?? '') === 'google'
    && !empty($user['google_id']);
if (!$user || $isGoogleOnly) {
    set_flash('error', 'This account uses Google sign-in. Please use Continue with Google to log in.');
    redirect(BASE_URL . 'login.php');
}

require_once BASE_PATH . '/middleware/csrf.php';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="lk-auth-wrap">
    <div class="row justify-content-center w-100">
        <div class="col-md-5">
            <div class="card card-lk shadow lk-auth-card">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Set new password</h1>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="email" value="<?= e($email) ?>">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <div class="mb-3">
                            <label class="form-label">New password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="8">
                        </div>
                        <button class="btn btn-lk-orange w-100" type="submit">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
