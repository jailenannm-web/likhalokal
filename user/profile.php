<?php

declare(strict_types=1);

$pageTitle = 'Profile';
$activeUser = 'prof';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$stmt = db()->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$uid]);
$u = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $fn = trim($_POST['full_name'] ?? '');
    $cn = trim($_POST['contact_number'] ?? '');
    $pw = (string) ($_POST['password'] ?? '');
    if ($fn !== '') {
        if ($pw !== '') {
            $h = password_hash($pw, PASSWORD_DEFAULT);
            db()->prepare('UPDATE users SET full_name=?, contact_number=?, password_hash=?, updated_at=NOW() WHERE id=?')->execute([$fn, $cn, $h, $uid]);
        } else {
            db()->prepare('UPDATE users SET full_name=?, contact_number=?, updated_at=NOW() WHERE id=?')->execute([$fn, $cn, $uid]);
        }
        $_SESSION['user_name'] = $fn;
        set_flash('success', 'Profile updated');
    }
    redirect(USER_DASH_URL . 'profile.php');
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Profile</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label class="form-label">Full name</label>
<input class="form-control mb-2" name="full_name" value="<?= e($u['full_name']) ?>" required>
<label class="form-label">Email (read-only)</label>
<input class="form-control mb-2" value="<?= e($u['email']) ?>" disabled>
<label class="form-label">Contact</label>
<input class="form-control mb-2" name="contact_number" value="<?= e((string) $u['contact_number']) ?>">
<label class="form-label">New password (optional)</label>
<input class="form-control mb-2" type="password" name="password" minlength="8" autocomplete="new-password">
<button class="btn btn-primary" type="submit">Save</button>
</form>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
