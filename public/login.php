<?php

declare(strict_types=1);

$pageTitle = 'Login';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

if (is_logged_in()) {
    redirect_after_login(consume_login_redirect());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once BASE_PATH . '/middleware/csrf.php';
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid session token.');
        redirect(BASE_URL . 'login.php');
    }
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] === 'suspended' || !password_verify($password, $user['password_hash'])) {
        set_flash('error', 'Invalid credentials or suspended account.');
        redirect(BASE_URL . 'login.php');
    }
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    log_activity((int) $user['id'], 'login', 'Web login', $_SERVER['REMOTE_ADDR'] ?? null);
    set_flash('success', 'Welcome back!');
    redirect_after_login(consume_login_redirect());
}

$loginRedirect = peek_login_redirect();

require_once BASE_PATH . '/middleware/csrf.php';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5" style="min-height: calc(100vh - 200px); display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-5">
            <div class="card card-lk shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Login</h1>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <?php if ($loginRedirect !== null && is_safe_post_login_redirect($loginRedirect)): ?>
                        <input type="hidden" name="redirect" value="<?= e($loginRedirect) ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <button class="btn btn-lk-orange w-100 mb-2" type="submit">Login</button>
                    </form>
                    <a href="<?= e(BASE_URL) ?>register.php" class="d-block text-center small">Register now</a>
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
