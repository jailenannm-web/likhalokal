<?php

declare(strict_types=1);

$pageTitle = 'Attractions';
$activeAdmin = 'att';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $stmt = db()->prepare(
        'INSERT INTO tourist_attractions (admin_id, attraction_name, category, description, history, travel_guide, entrance_fee, best_time_to_visit, address, latitude, longitude, status, created_at, updated_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
    );
    $stmt->execute([
        current_user_id(),
        trim($_POST['attraction_name'] ?? ''),
        $_POST['category'] ?? 'other',
        trim($_POST['description'] ?? ''),
        trim($_POST['history'] ?? ''),
        trim($_POST['travel_guide'] ?? ''),
        trim($_POST['entrance_fee'] ?? ''),
        trim($_POST['best_time_to_visit'] ?? ''),
        trim($_POST['address'] ?? ''),
        $_POST['latitude'] !== '' ? (float) $_POST['latitude'] : null,
        $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null,
        $_POST['status'] ?? 'published',
    ]);
    set_flash('success', 'Attraction created');
    redirect(ADMIN_URL . 'attractions.php');
}

$list = db()->query('SELECT * FROM tourist_attractions ORDER BY id DESC')->fetchAll();

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Tourist attractions</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<div class="card card-lk mb-4">
    <div class="card-body">
        <h2 class="h6">Add attraction</h2>
        <form method="post" class="row g-2">
            <?= csrf_field() ?>
            <div class="col-md-4"><input class="form-control" name="attraction_name" placeholder="Name" required></div>
            <div class="col-md-3">
                <select class="form-select" name="category"><?php foreach (['heritage_site','beach','island','church','landmark','eco_tourism','cultural_site','museum','other'] as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-md-2"><input class="form-control" name="entrance_fee" placeholder="Fee"></div>
            <div class="col-md-2"><input class="form-control" name="best_time_to_visit" placeholder="Best time"></div>
            <div class="col-md-12"><textarea class="form-control" name="description" rows="2" placeholder="Description"></textarea></div>
            <div class="col-md-12"><textarea class="form-control" name="history" rows="2" placeholder="History"></textarea></div>
            <div class="col-md-12"><textarea class="form-control" name="travel_guide" rows="2" placeholder="Travel guide"></textarea></div>
            <div class="col-md-6"><input class="form-control" name="address" placeholder="Address"></div>
            <div class="col-md-2"><input class="form-control" name="latitude" placeholder="Lat"></div>
            <div class="col-md-2"><input class="form-control" name="longitude" placeholder="Lng"></div>
            <div class="col-md-2">
                <select class="form-select" name="status"><option value="published">Published</option><option value="draft">Draft</option></select>
            </div>
            <div class="col-12"><button class="btn btn-primary btn-sm" type="submit">Save</button></div>
        </form>
    </div>
</div>
<table class="table table-sm"><thead><tr><th>ID</th><th>Name</th><th>Status</th></tr></thead><tbody>
<?php foreach ($list as $r): ?>
<tr><td><?= (int) $r['id'] ?></td><td><?= e($r['attraction_name']) ?></td><td><?= e($r['status']) ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
