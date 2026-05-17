<?php

declare(strict_types=1);

$pageTitle = 'Complete your account';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/middleware/csrf.php';

require_login();

$uid = current_user_id();
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$uid]);
$user = $stmt->fetch();
if (!$user) {
    redirect(BASE_URL . 'login.php');
}

if ($user['role'] === 'admin') {
    redirect_by_role();
}

$needsCompletion = ($user['status'] === 'pending') || !empty($_SESSION['needs_role_completion']);
if (!$needsCompletion && $user['status'] === 'active') {
    redirect_by_role();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid session token.');
        redirect(BASE_URL . 'complete-account.php');
    }
    $accountType = (string) ($_POST['account_type'] ?? 'local_user');
    if ($accountType !== 'seller' && $accountType !== 'local_user') {
        $accountType = 'local_user';
    }
    $contact = trim((string) ($_POST['contact_number'] ?? ''));
    db()->prepare('UPDATE users SET role = ?, contact_number = ?, status = \'active\', updated_at = NOW() WHERE id = ?')
        ->execute([$accountType, $contact, $uid]);
    $_SESSION['user_role'] = $accountType;
    unset($_SESSION['needs_role_completion']);
    log_activity($uid, 'complete_account', 'Role set to ' . $accountType, $_SERVER['REMOTE_ADDR'] ?? null);
    set_flash('success', $accountType === 'seller'
        ? 'Account ready. Use your profile menu to manage your business.'
        : 'Welcome to LikhaLokal!');
    redirect_after_login();
}

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5" style="min-height: calc(100vh - 200px); display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-6">
            <div class="card card-lk shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Complete your account</h1>
                    <p class="text-muted small">Choose how you will use LikhaLokal. Admin accounts cannot be created here.</p>
                    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">I am a…</label>
                            <select name="account_type" class="form-select" required>
                                <option value="local_user">Local user / tourist</option>
                                <option value="seller">Entrepreneur / business owner</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact number (optional)</label>
                            <input type="text" name="contact_number" class="form-control" value="<?= e((string) ($user['contact_number'] ?? '')) ?>">
                        </div>
                        <button type="submit" class="btn btn-lk-orange w-100">Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
