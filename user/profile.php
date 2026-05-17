<?php

declare(strict_types=1);

$pageTitle = 'Profile';
$activeUser = 'prof';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$stmt = db()->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$uid]);
$u = $stmt->fetch() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $fn = trim((string) ($_POST['full_name'] ?? ''));
    $cn = trim((string) ($_POST['contact_number'] ?? ''));
    $pw = (string) ($_POST['password'] ?? '');
    $profileImage = $u['profile_image'] ?? null;

    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $uploaded = save_upload($_FILES['profile_image'], 'profiles');
        if ($uploaded !== null) {
            $profileImage = $uploaded;
        } else {
            set_flash('error', 'Profile image must be JPG, PNG, or WebP under 5MB.');
            redirect(USER_DASH_URL . 'profile.php');
        }
    }

    if ($fn !== '') {
        if ($pw !== '') {
            if (strlen($pw) < 8) {
                set_flash('error', 'Password must be at least 8 characters.');
                redirect(USER_DASH_URL . 'profile.php');
            }
            $h = password_hash($pw, PASSWORD_DEFAULT);
            db()->prepare(
                'UPDATE users SET full_name=?, contact_number=?, profile_image=?, password_hash=?, updated_at=NOW() WHERE id=?'
            )->execute([$fn, $cn, $profileImage, $h, $uid]);
        } else {
            db()->prepare(
                'UPDATE users SET full_name=?, contact_number=?, profile_image=?, updated_at=NOW() WHERE id=?'
            )->execute([$fn, $cn, $profileImage, $uid]);
        }
        $_SESSION['user_name'] = $fn;
        set_flash('success', 'Profile updated successfully.');
    }
    redirect(USER_DASH_URL . 'profile.php');
}

$avatar = profile_avatar_url($u['full_name'] ?? null, $u['profile_image'] ?? null);
$completion = profile_completion_percent($u);

require __DIR__ . '/partials/layout-start.php';
?>

<h1 class="h4 fw-bold mb-4">My profile</h1>

<?php if ($m = flash('success')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3"><?= e($m) ?></div>
<?php endif; ?>
<?php if ($m = flash('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-3"><?= e($m) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="lk-profile-card p-4 text-center">
            <div class="avatar-ring mx-auto mb-3" style="width:120px;height:120px;">
                <img src="<?= e($avatar) ?>" alt="Profile photo" id="profilePreview">
            </div>
            <h2 class="h5 mb-1"><?= e($u['full_name'] ?? '') ?></h2>
            <p class="text-muted small mb-3"><?= e($u['email'] ?? '') ?></p>
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar bg-warning" style="width: <?= $completion ?>%"></div>
            </div>
            <p class="small text-muted mb-0">Profile <?= $completion ?>% complete</p>
        </div>
    </div>
    <div class="col-lg-8">
        <form method="post" enctype="multipart/form-data" class="lk-profile-card p-4">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">Profile photo</label>
                <input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/png,image/webp" id="profileImageInput">
                <div class="form-text">JPG, PNG, or WebP — max 5MB</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Full name</label>
                <input class="form-control form-control-lg" name="full_name" value="<?= e($u['full_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email <span class="text-muted fw-normal">(read-only)</span></label>
                <input class="form-control" value="<?= e($u['email'] ?? '') ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Contact number</label>
                <input class="form-control" name="contact_number" value="<?= e((string) ($u['contact_number'] ?? '')) ?>" placeholder="09XXXXXXXXX">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">New password <span class="text-muted fw-normal">(optional)</span></label>
                <input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current password">
            </div>
            <button class="btn btn-lk-orange px-4" type="submit"><i class="bi bi-check-lg me-1"></i> Save changes</button>
        </form>
    </div>
</div>

<script>
document.getElementById('profileImageInput')?.addEventListener('change', function (e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  const img = document.getElementById('profilePreview');
  if (img) img.src = URL.createObjectURL(file);
});
</script>

<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php'; ?>
