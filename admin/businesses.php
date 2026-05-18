<?php

declare(strict_types=1);

$pageTitle = 'Manage Businesses';
$activeAdmin = 'biz';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$hasBusinessCategory = db_column_exists('businesses', 'business_category');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token');
        redirect(ADMIN_URL . 'businesses.php');
    }
    $act = $_POST['admin_action'] ?? '';
    $id = (int) ($_POST['business_id'] ?? 0);
    
    // Suspend/Reactivate/Delete actions
    if ($act === 'suspend') {
        $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ?');
        $stmt->execute([$id]);
        $biz = $stmt->fetch();
        if ($biz) {
            db()->prepare("UPDATE businesses SET status='suspended' WHERE id=?")->execute([$id]);
            log_activity(current_user_id(), 'admin_business', 'suspend #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business suspended successfully.');
        }
        redirect(ADMIN_URL . 'businesses.php');
    } elseif ($act === 'reactivate') {
        $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ?');
        $stmt->execute([$id]);
        $biz = $stmt->fetch();
        if ($biz) {
            db()->prepare("UPDATE businesses SET status='approved' WHERE id=?")->execute([$id]);
            log_activity(current_user_id(), 'admin_business', 'reactivate #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business reactivated successfully.');
        }
        redirect(ADMIN_URL . 'businesses.php');
    } elseif ($act === 'delete') {
        $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ?');
        $stmt->execute([$id]);
        $biz = $stmt->fetch();
        if ($biz) {
            // Check if business has products
            $prodCheck = db()->prepare('SELECT COUNT(*) FROM products WHERE business_id = ?');
            $prodCheck->execute([$id]);
            $productCount = $prodCheck->fetchColumn();
            
            if ($productCount > 0) {
                set_flash('error', 'Cannot delete business with products. Please suspend instead.');
                redirect(ADMIN_URL . 'businesses.php');
            }
            
            db()->prepare('DELETE FROM businesses WHERE id=?')->execute([$id]);
            log_activity(current_user_id(), 'admin_business', 'delete #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business deleted successfully.');
        }
        redirect(ADMIN_URL . 'businesses.php');
    }
    
    // Add/Edit business form handling
    if ($act === 'add' || $act === 'edit') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $businessName = trim((string) ($_POST['business_name'] ?? ''));
        $businessType = trim((string) ($_POST['business_type'] ?? ''));
        $businessCategory = trim((string) ($_POST['business_category'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $contactNumber = trim((string) ($_POST['contact_number'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $barangay = trim((string) ($_POST['barangay'] ?? ''));
        $latitude = trim((string) ($_POST['latitude'] ?? ''));
        $longitude = trim((string) ($_POST['longitude'] ?? ''));
        $operatingHours = trim((string) ($_POST['operating_hours'] ?? ''));
        $acceptedPayments = $_POST['pay'] ?? [];
        $status = trim((string) ($_POST['status'] ?? 'approved'));
        
        if (empty($businessName) || empty($businessType) || empty($userId)) {
            set_flash('error', 'Business name, type, and owner are required.');
            redirect(ADMIN_URL . 'businesses.php');
        }
        
        $existingBiz = null;
        if ($act === 'edit' && $id > 0) {
            $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $existingBiz = $stmt->fetch() ?: null;
        }

        // Handle file uploads
        $logoPath = $existingBiz['logo'] ?? null;
        $coverPath = $existingBiz['cover_image'] ?? null;
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = save_upload($_FILES['logo'], 'businesses');
        }
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $coverPath = save_upload($_FILES['cover_image'], 'businesses');
        }
        
        $paymentsJson = json_encode($acceptedPayments);
        
        if ($act === 'add') {
            $columns = 'user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, status, created_at, updated_at';
            $values = '?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW()';
            $params = [$userId, $businessName, $businessType, $description, $contactNumber, $email, $address, $barangay, $latitude ?: null, $longitude ?: null, $operatingHours, $paymentsJson, $status];
            
            if ($hasBusinessCategory) {
                $columns = 'user_id, business_name, business_type, business_category, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, status, created_at, updated_at';
                $values = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW()';
                $params = [$userId, $businessName, $businessType, $businessCategory ?: null, $description, $contactNumber, $email, $address, $barangay, $latitude ?: null, $longitude ?: null, $operatingHours, $paymentsJson, $status];
            }
            
            if ($logoPath) {
                $columns .= ', logo';
                $values .= ',?';
                $params[] = $logoPath;
            }
            if ($coverPath) {
                $columns .= ', cover_image';
                $values .= ',?';
                $params[] = $coverPath;
            }
            
            db()->prepare("INSERT INTO businesses ($columns) VALUES ($values)")->execute($params);
            $newId = (int) db()->lastInsertId();
            if ($status === 'approved' && $newId > 0) {
                db()->prepare('UPDATE businesses SET approved_by = ?, approved_at = NOW() WHERE id = ?')->execute([current_user_id(), $newId]);
            }
            log_activity(current_user_id(), 'admin_business', 'add business', $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business added successfully.');
        } elseif ($act === 'edit') {
            $updates = 'business_name=?, business_type=?, description=?, contact_number=?, email=?, address=?, barangay=?, latitude=?, longitude=?, operating_hours=?, accepted_payments=?, status=?, updated_at=NOW()';
            $params = [$businessName, $businessType, $description, $contactNumber, $email, $address, $barangay, $latitude ?: null, $longitude ?: null, $operatingHours, $paymentsJson, $status, $id];
            
            if ($hasBusinessCategory) {
                $updates = 'business_name=?, business_type=?, business_category=?, description=?, contact_number=?, email=?, address=?, barangay=?, latitude=?, longitude=?, operating_hours=?, accepted_payments=?, status=?, updated_at=NOW()';
                $params = [$businessName, $businessType, $businessCategory ?: null, $description, $contactNumber, $email, $address, $barangay, $latitude ?: null, $longitude ?: null, $operatingHours, $paymentsJson, $status, $id];
            }
            
            if ($logoPath) {
                $updates .= ', logo=?';
                $params[] = $logoPath;
            }
            if ($coverPath) {
                $updates .= ', cover_image=?';
                $params[] = $coverPath;
            }
            
            db()->prepare("UPDATE businesses SET $updates WHERE id=?")->execute($params);
            log_activity(current_user_id(), 'admin_business', 'edit #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business updated successfully.');
        }
        redirect(ADMIN_URL . 'businesses.php');
    }
}

// Handle edit mode
$editId = (int) ($_GET['edit'] ?? 0);
$editBusiness = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ?');
    $stmt->execute([$editId]);
    $editBusiness = $stmt->fetch();
}

// Get seller users for dropdown
$sellers = db()->query("SELECT id, full_name, email FROM users WHERE role='seller' ORDER BY full_name ASC")->fetchAll();

// Get business list based on tab
$tab = $_GET['tab'] ?? 'approved';
$where = '';
$params = [];
if ($tab === 'approved') {
    $where = "WHERE b.status='approved'";
} elseif ($tab === 'pending') {
    $where = "WHERE b.status='pending'";
} elseif ($tab === 'rejected') {
    $where = "WHERE b.status='rejected'";
} elseif ($tab === 'suspended') {
    $where = "WHERE b.status='suspended'";
}
// 'all' shows everything

$list = db()->query(
    "SELECT b.*, u.email AS owner_email, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id $where ORDER BY b.created_at DESC"
)->fetchAll();

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';

// If in edit mode, show form
if ($editBusiness):
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Edit Business</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Update business information.</p>
</div>
<div class="lk-panel"><div class="lk-panel-body">
<form method="post" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>
    <input type="hidden" name="business_id" value="<?= (int) $editBusiness['id'] ?>">
    <input type="hidden" name="admin_action" value="edit">
    
    <div class="col-md-6">
        <label class="form-label">Owner</label>
        <select class="form-select" name="user_id" required>
            <?php foreach ($sellers as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= (int) $editBusiness['user_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['full_name'] . ' (' . $s['email'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Business name</label>
        <input class="form-control" name="business_name" value="<?= e($editBusiness['business_name']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Type</label>
        <select class="form-select" name="business_type" required>
            <?php foreach (['food_vendor','craft_business','restaurant','travel_agency','resort','recreation','service','pasalubong','fresh_produce'] as $t): ?>
                <option value="<?= e($t) ?>" <?= $editBusiness['business_type'] === $t ? 'selected' : '' ?>><?= e(business_type_label($t)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($hasBusinessCategory): ?>
    <div class="col-md-6">
        <label class="form-label">Business category</label>
        <input class="form-control" name="business_category" value="<?= e((string) ($editBusiness['business_category'] ?? '')) ?>" placeholder="Example: Pasalubong, Fresh Produce, Tours">
    </div>
    <?php endif; ?>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3"><?= e((string) ($editBusiness['description'] ?? '')) ?></textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Contact number</label>
        <input class="form-control" name="contact_number" value="<?= e((string) ($editBusiness['contact_number'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" value="<?= e((string) ($editBusiness['email'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Operating hours</label>
        <input class="form-control" name="operating_hours" value="<?= e((string) ($editBusiness['operating_hours'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input class="form-control" name="address" value="<?= e((string) ($editBusiness['address'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Barangay</label>
        <input class="form-control" name="barangay" value="<?= e((string) ($editBusiness['barangay'] ?? '')) ?>">
    </div>
    <div class="col-12">
        <label class="form-label">Location on map</label>
        <p class="small text-muted mb-2">Tap the map to set the business location.</p>
        <div id="businessMapPicker" class="lk-map-picker"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input class="form-control" name="latitude" id="businessLatitude" value="<?= e((string) ($editBusiness['latitude'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input class="form-control" name="longitude" id="businessLongitude" value="<?= e((string) ($editBusiness['longitude'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Logo</label>
        <input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
        <?php if (!empty($editBusiness['logo'])): ?><img src="<?= e(media_url($editBusiness['logo'])) ?>" alt="" class="rounded-circle mt-2 shadow-sm" style="width:76px;height:76px;object-fit:cover;"><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Cover image</label>
        <input class="form-control" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
        <?php if (!empty($editBusiness['cover_image'])): ?><img src="<?= e(media_url($editBusiness['cover_image'])) ?>" alt="" class="rounded mt-2 shadow-sm" style="width:160px;height:76px;object-fit:cover;"><?php endif; ?>
    </div>
    <div class="col-12" id="paymentsBox"><label class="form-label">Accepted payments</label><br>
        <?php 
        $pay = json_decode((string) ($editBusiness['accepted_payments'] ?? '[]'), true) ?: [];
        $opts = ['Cash on pickup', 'GCash', 'Maya', 'Bank transfer', 'Pay upon booking'];
        foreach ($opts as $o): ?>
        <label class="me-3"><input type="checkbox" name="pay[]" value="<?= e($o) ?>" <?= in_array($o, $pay, true) ? 'checked' : '' ?>> <?= e($o) ?></label>
        <?php endforeach; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="approved" <?= $editBusiness['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="pending" <?= $editBusiness['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="rejected" <?= $editBusiness['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="suspended" <?= $editBusiness['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>
    </div>
    <div class="col-12">
        <button class="btn btn-lk-orange" type="submit"><i class="bi bi-save me-1"></i> Save changes</button>
        <a href="<?= e(ADMIN_URL) ?>businesses.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form></div></div>
<?php else: ?>
<?php if (isset($_GET['add'])): ?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Add Business</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Register a new business listing.</p>
</div>
<div class="lk-panel"><div class="lk-panel-body">
<form method="post" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>
    <input type="hidden" name="admin_action" value="add">
    
    <div class="col-md-6">
        <label class="form-label">Owner</label>
        <select class="form-select" name="user_id" required>
            <option value="">Select seller</option>
            <?php foreach ($sellers as $s): ?>
                <option value="<?= (int) $s['id'] ?>"><?= e($s['full_name'] . ' (' . $s['email'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Business name</label>
        <input class="form-control" name="business_name" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Type</label>
        <select class="form-select" name="business_type" required>
            <?php foreach (['food_vendor','craft_business','restaurant','travel_agency','resort','recreation','service','pasalubong','fresh_produce'] as $t): ?>
                <option value="<?= e($t) ?>"><?= e(business_type_label($t)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($hasBusinessCategory): ?>
    <div class="col-md-6">
        <label class="form-label">Business category</label>
        <input class="form-control" name="business_category" placeholder="Example: Pasalubong, Fresh Produce, Tours">
    </div>
    <?php endif; ?>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3"></textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Contact number</label>
        <input class="form-control" name="contact_number">
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input class="form-control" name="email">
    </div>
    <div class="col-md-4">
        <label class="form-label">Operating hours</label>
        <input class="form-control" name="operating_hours">
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input class="form-control" name="address">
    </div>
    <div class="col-md-6">
        <label class="form-label">Barangay</label>
        <input class="form-control" name="barangay">
    </div>
    <div class="col-12">
        <label class="form-label">Location on map</label>
        <p class="small text-muted mb-2">Tap the map to set the business location.</p>
        <div id="businessMapPicker" class="lk-map-picker"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input class="form-control" name="latitude" id="businessLatitude">
    </div>
    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input class="form-control" name="longitude" id="businessLongitude">
    </div>
    <div class="col-md-6">
        <label class="form-label">Logo</label>
        <input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="col-md-6">
        <label class="form-label">Cover image</label>
        <input class="form-control" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="col-12" id="paymentsBox"><label class="form-label">Accepted payments</label><br>
        <?php 
        $opts = ['Cash on pickup', 'GCash', 'Maya', 'Bank transfer', 'Pay upon booking'];
        foreach ($opts as $o): ?>
        <label class="me-3"><input type="checkbox" name="pay[]" value="<?= e($o) ?>"> <?= e($o) ?></label>
        <?php endforeach; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="rejected">Rejected</option>
            <option value="suspended">Suspended</option>
        </select>
    </div>
    <div class="col-12">
        <button class="btn btn-lk-orange" type="submit"><i class="bi bi-plus-lg me-1"></i> Add business</button>
        <a href="<?= e(ADMIN_URL) ?>businesses.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form></div></div>
<?php else: ?>
<div class="lk-dash-inner-head lk-admin-manage-head d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <span class="badge bg-success mb-2">Business directory</span>
        <h1 class="lk-dash-page-title mb-1">Manage Businesses</h1>
        <p class="lk-dash-page-lead text-muted mb-0">
            Monitor and maintain <strong>existing businesses</strong> on the platform. Use filters for status.
            New seller registrations are reviewed on <a href="<?= e(ADMIN_URL) ?>business-applications.php">Business Applications</a>.
        </p>
    </div>
    <a href="?add=1" class="btn btn-lk-orange"><i class="bi bi-plus-lg me-1"></i> Add Business</a>
</div>
<?php if ($tab === 'pending'): ?>
<div class="alert alert-warning border-0 shadow-sm small">
    <i class="bi bi-info-circle me-1"></i>
    Pending registrations are reviewed on <a href="<?= e(ADMIN_URL) ?>business-applications.php" class="alert-link">Business Applications</a>.
    This tab shows pending records already in the database.
</div>
<?php endif; ?>
<div class="lk-dash-tabs btn-group mb-3 flex-wrap" role="group">
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'approved' ? 'active' : '' ?>" href="?tab=approved">Approved</a>
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'pending' ? 'active' : '' ?>" href="?tab=pending">Pending</a>
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'rejected' ? 'active' : '' ?>" href="?tab=rejected">Rejected</a>
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'suspended' ? 'active' : '' ?>" href="?tab=suspended">Suspended</a>
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'all' ? 'active' : '' ?>" href="?tab=all">All</a>
</div>
<div class="lk-panel lk-admin-manage-panel">
<div class="lk-panel-header d-flex justify-content-between align-items-center">
    <h2 class="mb-0"><i class="bi bi-building me-2 text-warning"></i> Business listings</h2>
    <span class="badge bg-light text-dark"><?= count($list) ?> shown</span>
</div>
<div class="lk-dash-table-wrap">
    <table class="table table-hover align-middle mb-0 lk-admin-manage-table">
        <thead><tr><th>Business</th><th>Owner</th><th>Type / Category</th><th>Contact</th><th>Location</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($list as $b): ?>
            <tr>
                <td>
                    <strong class="d-block"><?= e($b['business_name']) ?></strong>
                    <span class="small text-muted">#<?= (int) $b['id'] ?></span>
                </td>
                <td class="small">
                    <div><?= e($b['owner_name']) ?></div>
                    <div class="text-muted"><?= e($b['owner_email']) ?></div>
                </td>
                <td class="small">
                    <div><?= e(business_type_label((string) $b['business_type'])) ?></div>
                    <div class="text-muted"><?= e($b['business_category'] ?: '—') ?></div>
                </td>
                <td class="small"><?= e($b['contact_number'] ?: '—') ?></td>
                <td class="small"><?= e(trim(($b['address'] ?? '') . ($b['barangay'] ? ', ' . $b['barangay'] : '')) ?: '—') ?></td>
                <td><span class="badge bg-<?= business_status_badge_class((string) $b['status']) ?>"><?= e($b['status']) ?></span></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="<?= e(vendor_profile_url((int) $b['id'], current_request_return_url())) ?>" target="_blank" rel="noopener">View Profile</a>
                    <a class="btn btn-sm btn-outline-secondary" href="?edit=<?= (int) $b['id'] ?>">Edit</a>
                    <?php if ($b['status'] === 'approved'): ?>
                        <form method="post" class="d-inline" onsubmit="return confirm('Suspend this business?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="suspend">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Suspend</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($b['status'] === 'suspended'): ?>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="reactivate">
                            <button class="btn btn-sm btn-success" type="submit">Reactivate</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this business permanently?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                        <input type="hidden" name="admin_action" value="delete">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($list)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No businesses in this list.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php
$needsMap = $editBusiness || isset($_GET['add']);
if ($needsMap) {
    $extraScripts = ($extraScripts ?? '') . '<script src="' . e(asset_url('js/maps.js')) . '"></script>
<script>document.addEventListener("DOMContentLoaded", function () { if (window.initMapPickers) window.initMapPickers(); });</script>';
}
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
