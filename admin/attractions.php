<?php

declare(strict_types=1);

$pageTitle = 'Attractions';
$activeAdmin = 'att';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$validCategories = ['heritage_site', 'beach', 'island', 'church', 'landmark', 'eco_tourism', 'cultural_site', 'museum', 'other'];
$validStatuses = ['published', 'draft'];
$emptyAttraction = [
    'id' => 0,
    'attraction_name' => '',
    'category' => 'other',
    'description' => '',
    'history' => '',
    'travel_guide' => '',
    'entrance_fee' => '',
    'best_time_to_visit' => '',
    'address' => '',
    'latitude' => '',
    'longitude' => '',
    'image' => '',
    'status' => 'published',
];

function admin_attraction_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM tourist_attractions WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $attraction = $stmt->fetch();
    return $attraction ?: null;
}

function admin_valid_coord(string $value, float $min, float $max): bool
{
    if ($value === '') {
        return true;
    }
    if (!is_numeric($value)) {
        return false;
    }
    $number = (float) $value;
    return $number >= $min && $number <= $max;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(ADMIN_URL . 'attractions.php');
    }

    $action = (string) ($_POST['action'] ?? 'create');
    $attractionId = (int) ($_POST['attraction_id'] ?? 0);

    if ($action === 'delete') {
        db()->prepare('DELETE FROM tourist_attractions WHERE id = ?')->execute([$attractionId]);
        set_flash('success', 'Attraction deleted.');
        redirect(ADMIN_URL . 'attractions.php');
    }

    if ($action === 'toggle') {
        $attraction = admin_attraction_by_id($attractionId);
        if ($attraction) {
            $next = ($attraction['status'] ?? '') === 'published' ? 'draft' : 'published';
            db()->prepare('UPDATE tourist_attractions SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$next, $attractionId]);
            set_flash('success', $next === 'published' ? 'Attraction published.' : 'Attraction unpublished.');
        }
        redirect(ADMIN_URL . 'attractions.php');
    }

    $name = trim((string) ($_POST['attraction_name'] ?? ''));
    $category = in_array(($_POST['category'] ?? ''), $validCategories, true) ? (string) $_POST['category'] : 'other';
    $description = trim((string) ($_POST['description'] ?? ''));
    $history = trim((string) ($_POST['history'] ?? ''));
    $travelGuide = trim((string) ($_POST['travel_guide'] ?? ''));
    $entranceFee = trim((string) ($_POST['entrance_fee'] ?? ''));
    $bestTime = trim((string) ($_POST['best_time_to_visit'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $latitude = trim((string) ($_POST['latitude'] ?? ''));
    $longitude = trim((string) ($_POST['longitude'] ?? ''));
    $status = in_array(($_POST['status'] ?? ''), $validStatuses, true) ? (string) $_POST['status'] : 'draft';
    $errors = [];

    if ($name === '' || strlen($name) > 200) {
        $errors[] = 'Attraction name is required and must be 200 characters or fewer.';
    }
    if (strlen($entranceFee) > 120) {
        $errors[] = 'Entrance fee must be 120 characters or fewer.';
    }
    if (strlen($bestTime) > 255) {
        $errors[] = 'Best time to visit must be 255 characters or fewer.';
    }
    if (strlen($address) > 255) {
        $errors[] = 'Location must be 255 characters or fewer.';
    }
    if (!admin_valid_coord($latitude, -90, 90)) {
        $errors[] = 'Latitude must be between -90 and 90.';
    }
    if (!admin_valid_coord($longitude, -180, 180)) {
        $errors[] = 'Longitude must be between -180 and 180.';
    }

    $existing = $attractionId > 0 ? admin_attraction_by_id($attractionId) : null;
    $image = $existing['image'] ?? null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload = save_upload($_FILES['image'], 'attractions');
        if ($upload) {
            $image = $upload;
        } else {
            $errors[] = 'Attraction image must be a JPG, PNG, or WEBP file under the upload limit.';
        }
    } elseif (isset($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Attraction image upload failed.';
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', $errors));
        $target = $attractionId > 0 ? ADMIN_URL . 'attractions.php?edit=' . $attractionId : ADMIN_URL . 'attractions.php';
        redirect($target);
    }

    $latValue = $latitude !== '' ? (float) $latitude : null;
    $lngValue = $longitude !== '' ? (float) $longitude : null;

    if ($action === 'update' && $existing) {
        $stmt = db()->prepare(
            'UPDATE tourist_attractions
             SET attraction_name = ?, category = ?, description = ?, history = ?, travel_guide = ?,
                 entrance_fee = ?, best_time_to_visit = ?, address = ?, latitude = ?, longitude = ?,
                 image = ?, status = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $name,
            $category,
            $description,
            $history,
            $travelGuide,
            $entranceFee,
            $bestTime,
            $address,
            $latValue,
            $lngValue,
            $image,
            $status,
            $attractionId,
        ]);
        set_flash('success', 'Attraction updated.');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO tourist_attractions
             (admin_id, attraction_name, category, description, history, travel_guide, entrance_fee,
              best_time_to_visit, address, latitude, longitude, image, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            current_user_id(),
            $name,
            $category,
            $description,
            $history,
            $travelGuide,
            $entranceFee,
            $bestTime,
            $address,
            $latValue,
            $lngValue,
            $image,
            $status,
        ]);
        set_flash('success', 'Attraction created.');
    }
    redirect(ADMIN_URL . 'attractions.php');
}

$editId = (int) ($_GET['edit'] ?? 0);
$editingAttraction = $editId > 0 ? admin_attraction_by_id($editId) : null;
$formAttraction = $editingAttraction ?: $emptyAttraction;
$list = db()->query('SELECT * FROM tourist_attractions ORDER BY id DESC')->fetchAll();

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="lk-dash-page-title mb-1">Tourist attractions</h1>
        <p class="lk-dash-page-lead text-muted mb-0">Create, edit, publish, and maintain public tourism attraction records.</p>
    </div>
    <?php if ($editingAttraction): ?>
        <a href="<?= e(ADMIN_URL) ?>attractions.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i> Cancel edit</a>
    <?php endif; ?>
</div>

<div class="lk-panel mb-4">
    <div class="lk-panel-header">
        <h2><i class="bi <?= $editingAttraction ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2 text-warning"></i><?= $editingAttraction ? 'Edit attraction' : 'Add attraction' ?></h2>
    </div>
    <div class="lk-panel-body">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $editingAttraction ? 'update' : 'create' ?>">
            <input type="hidden" name="attraction_id" value="<?= (int) ($formAttraction['id'] ?? 0) ?>">
            <div class="col-md-6">
                <label class="form-label">Attraction name</label>
                <input class="form-control" name="attraction_name" required maxlength="200" value="<?= e((string) $formAttraction['attraction_name']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category">
                    <?php foreach ($validCategories as $category): ?>
                        <option value="<?= e($category) ?>" <?= ($formAttraction['category'] ?? '') === $category ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $category))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <?php foreach ($validStatuses as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($formAttraction['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Overview / description</label>
                <textarea class="form-control" name="description" rows="3"><?= e((string) $formAttraction['description']) ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">History / background</label>
                <textarea class="form-control" name="history" rows="4"><?= e((string) $formAttraction['history']) ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Travel guide / quick tips</label>
                <textarea class="form-control" name="travel_guide" rows="4"><?= e((string) $formAttraction['travel_guide']) ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Entrance fee</label>
                <input class="form-control" name="entrance_fee" maxlength="120" value="<?= e((string) $formAttraction['entrance_fee']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Best time to visit</label>
                <input class="form-control" name="best_time_to_visit" maxlength="255" value="<?= e((string) $formAttraction['best_time_to_visit']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Location / address</label>
                <input class="form-control" name="address" maxlength="255" value="<?= e((string) $formAttraction['address']) ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Attraction Location</label>
                <p class="small text-muted mb-2">Tap the map to set the tourist attraction location.</p>
                <div id="attractionMapPicker" class="lk-map-picker"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Latitude</label>
                <input class="form-control" name="latitude" id="attractionLatitude" value="<?= e((string) ($formAttraction['latitude'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Longitude</label>
                <input class="form-control" name="longitude" id="attractionLongitude" value="<?= e((string) ($formAttraction['longitude'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Attraction image</label>
                <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <?php if (!empty($formAttraction['image'])): ?>
                    <div class="form-text">Current image will be kept if no new image is uploaded.</div>
                <?php endif; ?>
            </div>
            <?php if (!empty($formAttraction['image'])): ?>
                <div class="col-md-2">
                    <img src="<?= e(media_url($formAttraction['image'])) ?>" alt="" class="rounded shadow-sm w-100" style="height:94px;object-fit:cover;">
                </div>
            <?php endif; ?>
            <div class="col-12">
                <button class="btn btn-lk-orange" type="submit"><i class="bi bi-save me-1"></i><?= $editingAttraction ? ' Save changes' : ' Add attraction' ?></button>
            </div>
        </form>
    </div>
</div>

<div class="lk-panel">
    <div class="lk-panel-header">
        <h2>All attractions</h2>
        <span class="badge bg-light text-dark"><?= count($list) ?> attraction(s)</span>
    </div>
    <div class="lk-dash-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Attraction</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $attraction): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= e(media_url($attraction['image'] ?? null, asset_url('images/placeholder.png'))) ?>" alt="" class="rounded" style="width:70px;height:54px;object-fit:cover;">
                            <div>
                                <strong class="d-block"><?= e($attraction['attraction_name']) ?></strong>
                                <span class="small text-muted"><?= e(str_limit((string) ($attraction['description'] ?? ''), 80)) ?></span>
                            </div>
                        </div>
                    </td>
                    <td><?= e(ucwords(str_replace('_', ' ', (string) $attraction['category']))) ?></td>
                    <td><?= e($attraction['address'] ?: 'Location not provided') ?></td>
                    <td><span class="badge bg-<?= ($attraction['status'] ?? '') === 'published' ? 'success' : 'secondary' ?>"><?= e(ucfirst((string) $attraction['status'])) ?></span></td>
                    <td class="text-end">
                        <?php if (($attraction['status'] ?? '') === 'published'): ?>
                            <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $attraction['id'] ?>" class="btn btn-sm btn-outline-success" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i></a>
                        <?php endif; ?>
                        <a href="<?= e(ADMIN_URL) ?>attractions.php?edit=<?= (int) $attraction['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="attraction_id" value="<?= (int) $attraction['id'] ?>">
                            <button class="btn btn-sm btn-outline-primary" type="submit">
                                <i class="bi <?= ($attraction['status'] ?? '') === 'published' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                            </button>
                        </form>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this attraction?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="attraction_id" value="<?= (int) $attraction['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($list)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No attractions yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$extraScripts = '<script src="' . e(asset_url('js/maps.js')) . '"></script>
<script>document.addEventListener("DOMContentLoaded", function () { if (window.initMapPickers) window.initMapPickers(); });</script>';
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
?>
