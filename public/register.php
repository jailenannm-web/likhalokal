<?php

declare(strict_types=1);

$pageTitle = 'Register';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (is_logged_in()) {
    redirect_after_login();
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
    $_SESSION['user_id'] = $id;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $role;
    log_activity($id, 'register', 'New registration', $_SERVER['REMOTE_ADDR'] ?? null);
    set_flash('success', $role === 'seller'
        ? 'Account created. Use your profile menu to set up your business.'
        : 'Welcome to LikhaLokal!');
    redirect_after_login();
}

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5" style="min-height: calc(100vh - 200px); display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-lg-6">
            <div class="card card-lk shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Create account</h1>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Account type</label>
                            <select name="account_type" class="form-select">
                                <option value="local_user">Local User / Tourist</option>
                                <option value="seller">Entrepreneur / Business Owner</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="8">
                        </div>
                        <button class="btn btn-lk-orange w-100 mb-2" type="submit">Register</button>
                    </form>
                    <p class="small text-center mt-3 mb-0"><a href="<?= e(BASE_URL) ?>login.php">Already have an account?</a></p>
                    <?php if (google_oauth_configured()): ?>
                        <a href="<?= e(BASE_URL) ?>google-auth.php" class="btn btn-outline-secondary w-100 mt-3">
                            <i class="fa-brands fa-google me-2"></i> Continue with Google
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary w-100 mt-3" disabled title="Set GOOGLE_CLIENT_ID in config/app.php">Continue with Google</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
