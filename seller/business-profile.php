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
$hasBusinessCategory = db_column_exists('businesses', 'business_category');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(SELLER_URL . 'business-profile.php');
    }

    $businessName = trim((string) ($_POST['business_name'] ?? ''));
    $businessType = (string) ($_POST['business_type'] ?? 'pasalubong');
    $businessCategory = trim((string) ($_POST['business_category'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $contact = trim((string) ($_POST['contact_number'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $barangay = trim((string) ($_POST['barangay'] ?? ''));
    $latitude = trim((string) ($_POST['latitude'] ?? ''));
    $longitude = trim((string) ($_POST['longitude'] ?? ''));
    $hours = trim((string) ($_POST['operating_hours'] ?? ''));
    $errors = [];

    if ($businessName === '' || strlen($businessName) > 200) {
        $errors[] = 'Business name is required and must be 200 characters or fewer.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($latitude !== '' && (!is_numeric($latitude) || (float) $latitude < -90 || (float) $latitude > 90)) {
        $errors[] = 'Latitude must be between -90 and 90.';
    }
    if ($longitude !== '' && (!is_numeric($longitude) || (float) $longitude < -180 || (float) $longitude > 180)) {
        $errors[] = 'Longitude must be between -180 and 180.';
    }

    $logo = $b['logo'] ?? null;
    $cover = $b['cover_image'] ?? null;
    if (!empty($_FILES['logo']['tmp_name'])) {
        $up = save_upload($_FILES['logo'], 'businesses');
        if ($up) {
            $logo = $up;
        } else {
            $errors[] = 'Logo must be a JPG, PNG, or WEBP file under the upload limit.';
        }
    }
    if (!empty($_FILES['cover_image']['tmp_name'])) {
        $up = save_upload($_FILES['cover_image'], 'businesses');
        if ($up) {
            $cover = $up;
        } else {
            $errors[] = 'Cover image must be a JPG, PNG, or WEBP file under the upload limit.';
        }
    }
    if (!empty($errors)) {
        set_flash('error', implode(' ', $errors));
        redirect(SELLER_URL . 'business-profile.php');
    }

    $payments = json_encode(array_values($_POST['pay'] ?? []));
    $latValue = $latitude !== '' ? (float) $latitude : null;
    $lngValue = $longitude !== '' ? (float) $longitude : null;
    if ($b) {
        $sql = 'UPDATE businesses SET business_name=?, business_type=?, description=?, contact_number=?, email=?, address=?, barangay=?, latitude=?, longitude=?, operating_hours=?, accepted_payments=?, logo=?, cover_image=?';
        $params = [$businessName, $businessType, $description, $contact, $email, $address, $barangay, $latValue, $lngValue, $hours, $payments, $logo, $cover];
        if ($hasBusinessCategory) {
            $sql .= ', business_category=?';
            $params[] = $businessCategory;
        }
        $sql .= ', updated_at=NOW() WHERE id=? AND user_id=?';
        $params[] = (int) $b['id'];
        $params[] = $uid;
        db()->prepare($sql)->execute($params);
    } else {
        $columns = 'user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, logo, cover_image';
        $marks = '?,?,?,?,?,?,?,?,?,?,?,?,?,?';
        $params = [$uid, $businessName, $businessType, $description, $contact, $email, $address, $barangay, $latValue, $lngValue, $hours, $payments, $logo, $cover];
        if ($hasBusinessCategory) {
            $columns .= ', business_category';
            $marks .= ',?';
            $params[] = $businessCategory;
        }
        $stmt = db()->prepare(
            'INSERT INTO businesses (' . $columns . ', status, created_at, updated_at)
             VALUES (' . $marks . ',\'pending\',NOW(),NOW())'
        );
        $stmt->execute($params);
    }
    set_flash('success', 'Profile saved.');
    redirect(SELLER_URL . 'business-profile.php');
}

if (!$b) {
    $b = [
        'business_name' => '', 'business_type' => 'pasalubong', 'description' => '', 'contact_number' => '', 'email' => '',
        'address' => '', 'barangay' => '', 'latitude' => '', 'longitude' => '', 'operating_hours' => '',
        'accepted_payments' => '[]', 'logo' => '', 'cover_image' => '', 'status' => 'new', 'business_category' => '',
    ];
}
$pay = json_decode((string) $b['accepted_payments'], true) ?: [];
$opts = ['Cash on pickup', 'GCash', 'Maya', 'Bank transfer', 'Pay upon booking'];

require __DIR__ . '/partials/layout-start.php';
?>
<?php require BASE_PATH . '/includes/partials/dash-flash.php'; $status = (string) ($b['status'] ?? 'new'); ?>
<div class="lk-dash-inner-head d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="lk-dash-page-title mb-1">Business profile</h1>
        <p class="lk-dash-page-lead text-muted mb-0">Update your listing details, location, payments, and images.</p>
    </div>
    <?php if ($status !== 'new'): ?><span class="badge bg-<?= business_status_badge_class($status) ?> text-uppercase"><?= e($status) ?></span><?php endif; ?>
</div>
<div class="lk-panel"><div class="lk-panel-body">
<form method="post" enctype="multipart/form-data" class="row g-3">
<?= csrf_field() ?>
<div class="col-md-6"><label class="form-label">Business name</label><input class="form-control" name="business_name" value="<?= e($b['business_name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Type</label>
<select class="form-select" name="business_type"><?php foreach (['food_vendor','craft_business','restaurant','travel_agency','resort','recreation','service','pasalubong','fresh_produce'] as $t): ?>
<option value="<?= e($t) ?>" <?= ($b['business_type'] ?? '') === $t ? 'selected' : '' ?>><?= e(business_type_label($t)) ?></option><?php endforeach; ?></select></div>
<?php if ($hasBusinessCategory): ?>
<div class="col-md-6"><label class="form-label">Business category</label><input class="form-control" name="business_category" value="<?= e((string) ($b['business_category'] ?? '')) ?>" placeholder="Example: Pasalubong, Fresh Produce, Tours"></div>
<?php endif; ?>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?= e((string) $b['description']) ?></textarea></div>
<div class="col-md-4"><label class="form-label">Contact</label><input class="form-control" name="contact_number" value="<?= e((string) $b['contact_number']) ?>"></div>
<div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e((string) $b['email']) ?>"></div>
<div class="col-md-4"><label class="form-label">Operating hours</label><input class="form-control" name="operating_hours" value="<?= e((string) $b['operating_hours']) ?>"></div>
<div class="col-md-6"><label class="form-label">Address</label><input class="form-control" name="address" value="<?= e((string) $b['address']) ?>"></div>
<div class="col-md-6"><label class="form-label">Barangay</label><input class="form-control" name="barangay" value="<?= e((string) $b['barangay']) ?>"></div>
<div class="col-12">
    <label class="form-label">Business Location</label>
    <p class="small text-muted mb-2">Tap the map to set your business location.</p>
    <div id="businessLocationPicker" class="lk-map-picker" style="height: 280px; background: #e9ecef; border-radius: 12px; overflow: hidden;"></div>
</div>
<div class="col-md-6"><label class="form-label">Latitude</label><input class="form-control" name="latitude" id="latitudeInput" value="<?= e((string) ($b['latitude'] ?? '')) ?>" readonly></div>
<div class="col-md-6"><label class="form-label">Longitude</label><input class="form-control" name="longitude" id="longitudeInput" value="<?= e((string) ($b['longitude'] ?? '')) ?>" readonly></div>
<div class="col-md-6">
<label class="form-label">Logo</label><input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
<?php if (!empty($b['logo'])): ?><img src="<?= e(media_url($b['logo'])) ?>" alt="" class="rounded-circle mt-2 shadow-sm" style="width:76px;height:76px;object-fit:cover;"><?php endif; ?>
</div>
<div class="col-md-6">
<label class="form-label">Cover image</label><input class="form-control" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
<?php if (!empty($b['cover_image'])): ?><img src="<?= e(media_url($b['cover_image'])) ?>" alt="" class="rounded mt-2 shadow-sm" style="width:160px;height:76px;object-fit:cover;"><?php endif; ?>
</div>
<div class="col-12" id="paymentsBox"><label class="form-label">Accepted payments</label><br>
<?php foreach ($opts as $o): ?>
<label class="me-3"><input type="checkbox" name="pay[]" value="<?= e($o) ?>" <?= in_array($o, $pay, true) ? 'checked' : '' ?>> <?= e($o) ?></label>
<?php endforeach; ?>
</div>
<div class="col-12"><button class="btn btn-lk-orange" type="submit"><i class="bi bi-save me-1"></i> Save profile</button></div>
</form></div></div>
<?php
$extraScripts = '<script src="' . e(asset_url('js/maps.js')) . '"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const mapEl = document.getElementById("businessLocationPicker");
    const latInput = document.getElementById("latitudeInput");
    const lngInput = document.getElementById("longitudeInput");
    
    // Default to Vinzons, Camarines Norte
    const defaultLat = 14.12;
    const defaultLng = 122.87;
    
    // Get current values or use defaults
    let currentLat = latInput.value ? parseFloat(latInput.value) : defaultLat;
    let currentLng = lngInput.value ? parseFloat(lngInput.value) : defaultLng;
    
    // Check if we have valid coordinates
    const hasValidCoords = !isNaN(currentLat) && !isNaN(currentLng);
    if (!hasValidCoords) {
        currentLat = defaultLat;
        currentLng = defaultLng;
    }
    
    // Initialize the map
    likhaMapsLoadScript(function(ok) {
        if (!ok) {
            // Fallback: show manual entry message
            mapEl.innerHTML = \'<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="bi bi-map fs-1 mb-2"></i><p class="mb-0 text-center px-3">Map picker unavailable. You may enter coordinates manually.</p></div>\';
            latInput.readOnly = false;
            lngInput.readOnly = false;
            return;
        }
        
        try {
            const map = new google.maps.Map(mapEl, {
                zoom: 14,
                center: { lat: currentLat, lng: currentLng },
                mapTypeControl: false,
                streetViewControl: false,
            });
            
            let marker = new google.maps.Marker({
                position: { lat: currentLat, lng: currentLng },
                map: map,
                draggable: true
            });
            
            // Update inputs when marker is dragged
            marker.addListener("dragend", function() {
                const position = marker.getPosition();
                latInput.value = position.lat();
                lngInput.value = position.lng();
            });
            
            // Update marker and inputs when map is clicked
            map.addListener("click", function(e) {
                const position = e.latLng;
                marker.setPosition(position);
                latInput.value = position.lat();
                lngInput.value = position.lng();
            });
            
        } catch (err) {
            console.warn("Map picker failed", err);
            mapEl.innerHTML = \'<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="bi bi-map fs-1 mb-2"></i><p class="mb-0 text-center px-3">Map picker unavailable. You may enter coordinates manually.</p></div>\';
            latInput.readOnly = false;
            lngInput.readOnly = false;
        }
    });
});
</script>';
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
