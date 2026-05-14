<?php

declare(strict_types=1);

$pageTitle = 'Business profile';
$activeSeller = 'biz';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$stmt = db()->prepare(
    "SELECT * FROM businesses WHERE user_id = ? ORDER BY CASE status WHEN 'approved' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END, id ASC LIMIT 1"
);
$stmt->execute([$uid]);
$b = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $logo = $b['logo'] ?? null;
    $cover = $b['cover_image'] ?? null;
    if (!empty($_FILES['logo']['tmp_name'])) {
        $up = save_upload($_FILES['logo'], 'business');
        if ($up) {
            $logo = $up;
        }
    }
    if (!empty($_FILES['cover_image']['tmp_name'])) {
        $up = save_upload($_FILES['cover_image'], 'business');
        if ($up) {
            $cover = $up;
        }
    }
    $payments = json_encode(array_values($_POST['pay'] ?? []));
    if ($b) {
        $stmt = db()->prepare(
            'UPDATE businesses SET business_name=?, business_type=?, description=?, contact_number=?, email=?, address=?, barangay=?, latitude=?, longitude=?, operating_hours=?, accepted_payments=?, logo=?, cover_image=?, updated_at=NOW() WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            trim($_POST['business_name'] ?? ''),
            $_POST['business_type'] ?? 'pasalubong',
            trim($_POST['description'] ?? ''),
            trim($_POST['contact_number'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['barangay'] ?? ''),
            $_POST['latitude'] !== '' ? (float) $_POST['latitude'] : null,
            $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null,
            trim($_POST['operating_hours'] ?? ''),
            $payments,
            $logo,
            $cover,
            (int) $b['id'],
            $uid,
        ]);
    } else {
        $stmt = db()->prepare(
            'INSERT INTO businesses (user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, logo, cover_image, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'pending\',NOW(),NOW())'
        );
        $stmt->execute([
            $uid,
            trim($_POST['business_name'] ?? ''),
            $_POST['business_type'] ?? 'pasalubong',
            trim($_POST['description'] ?? ''),
            trim($_POST['contact_number'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['barangay'] ?? ''),
            $_POST['latitude'] !== '' ? (float) $_POST['latitude'] : null,
            $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null,
            trim($_POST['operating_hours'] ?? ''),
            $payments,
            $logo,
            $cover,
        ]);
    }
    set_flash('success', 'Profile saved.');
    redirect(SELLER_URL . 'business-profile.php');
}

if (!$b) {
    $b = [
        'business_name' => '', 'business_type' => 'pasalubong', 'description' => '', 'contact_number' => '', 'email' => '',
        'address' => '', 'barangay' => '', 'latitude' => '', 'longitude' => '', 'operating_hours' => '',
        'accepted_payments' => '[]', 'logo' => '', 'cover_image' => '', 'status' => 'new',
    ];
}
$pay = json_decode((string) $b['accepted_payments'], true) ?: [];
$opts = ['Cash on pickup', 'GCash', 'Maya', 'Bank transfer', 'Pay upon booking'];

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Business profile</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="card card-lk">
<div class="card-body row g-3">
<?= csrf_field() ?>
<div class="col-md-6"><label class="form-label">Business name</label><input class="form-control" name="business_name" value="<?= e($b['business_name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Type</label>
<select class="form-select" name="business_type"><?php foreach (['food_vendor','craft_business','restaurant','travel_agency','resort','recreation','service','pasalubong','fresh_produce'] as $t): ?>
<option value="<?= e($t) ?>" <?= ($b['business_type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?= e((string) $b['description']) ?></textarea></div>
<div class="col-md-4"><label class="form-label">Contact</label><input class="form-control" name="contact_number" value="<?= e((string) $b['contact_number']) ?>"></div>
<div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e((string) $b['email']) ?>"></div>
<div class="col-md-4"><label class="form-label">Operating hours</label><input class="form-control" name="operating_hours" value="<?= e((string) $b['operating_hours']) ?>"></div>
<div class="col-md-6"><label class="form-label">Address</label><input class="form-control" name="address" value="<?= e((string) $b['address']) ?>"></div>
<div class="col-md-6"><label class="form-label">Barangay</label><input class="form-control" name="barangay" value="<?= e((string) $b['barangay']) ?>"></div>
<div class="col-md-3"><label class="form-label">Latitude</label><input class="form-control" name="latitude" value="<?= e((string) ($b['latitude'] ?? '')) ?>"></div>
<div class="col-md-3"><label class="form-label">Longitude</label><input class="form-control" name="longitude" value="<?= e((string) ($b['longitude'] ?? '')) ?>"></div>
<div class="col-md-6"><label class="form-label">Logo</label><input class="form-control" type="file" name="logo" accept="image/*"></div>
<div class="col-md-6"><label class="form-label">Cover image</label><input class="form-control" type="file" name="cover_image" accept="image/*"></div>
<div class="col-12" id="paymentsBox"><label class="form-label">Accepted payments</label><br>
<?php foreach ($opts as $o): ?>
<label class="me-3"><input type="checkbox" name="pay[]" value="<?= e($o) ?>" <?= in_array($o, $pay, true) ? 'checked' : '' ?>> <?= e($o) ?></label>
<?php endforeach; ?>
</div>
<div class="col-12"><button class="btn btn-lk-orange" type="submit">Save</button></div>
</div>
</form>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
