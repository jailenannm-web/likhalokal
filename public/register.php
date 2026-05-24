<?php

declare(strict_types=1);

$pageTitle = 'Register';
$activeNav = '';
$bodyClass = 'lk-auth-page';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (is_logged_in()) {
    redirect_by_role();
}

require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid session token.');
        redirect(BASE_URL . 'register.php');
    }
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $contact = trim((string) ($_POST['contact_number'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    $accountType = (string) ($_POST['account_type'] ?? 'local_user');
    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $confirm) {
        set_flash('error', 'Please check all fields. Password must be at least 8 characters and match.');
        redirect(BASE_URL . 'register.php');
    }
    if ($accountType === 'admin') {
        set_flash('error', 'Admin accounts cannot be registered publicly.');
        redirect(BASE_URL . 'register.php');
    }
    $role = $accountType === 'seller' ? 'seller' : 'local_user';
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        set_flash('error', 'Email already registered.');
        redirect(BASE_URL . 'register.php');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO users (full_name, email, password_hash, contact_number, role, status, created_at, updated_at) VALUES (?,?,?,?,?,\'active\',NOW(),NOW())'
    );
    $stmt->execute([$fullName, $email, $hash, $contact, $role]);
    $id = (int) db()->lastInsertId();
    login_user([
        'id' => $id,
        'full_name' => $fullName,
        'email' => $email,
        'role' => $role,
    ], false);
    log_activity($id, 'register', 'New registration', $_SERVER['REMOTE_ADDR'] ?? null);
    set_flash('success', $role === 'seller'
        ? 'Account created. Please submit your business application.'
        : 'Welcome to LikhaLokal!');
    if ($role === 'seller') {
        redirect(BASE_URL . 'register-business.php');
    }
    redirect_by_role();
}

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="lk-auth-wrap">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card card-lk shadow lk-auth-card">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Create account</h1>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Account type</label>
                            <select name="account_type" class="form-select" required>
                                <option value="local_user">Local User / Tourist</option>
                                <option value="seller">Entrepreneur / Business Owner</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full name</label>
                            <input type="text" name="full_name" class="form-control" required autocomplete="name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact number</label>
                            <input type="text" name="contact_number" class="form-control" autocomplete="tel">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <button class="btn btn-lk-orange w-100 mb-3" type="submit">Register</button>
                    </form>
                    <div class="lk-auth-divider auth-divider"><span>or</span></div>
                    <?php if (google_oauth_configured()): ?>
                        <a href="<?= e(BASE_URL) ?>google-login.php?source=register" class="btn btn-lk-google google-auth-btn w-100">
                            <span class="lk-google-g" aria-hidden="true">G</span> Continue with Google
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-lk-google google-auth-btn w-100" disabled>
                            <span class="lk-google-g" aria-hidden="true">G</span> Continue with Google
                        </button>
                        <p class="small text-muted text-center mt-2 mb-0">Set Google OAuth credentials to enable Google sign-in.</p>
                    <?php endif; ?>
                    <p class="auth-switch-text">Already have an account? <a href="<?= e(BASE_URL) ?>login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
