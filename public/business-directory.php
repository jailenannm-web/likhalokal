<?php

declare(strict_types=1);

$pageTitle = 'Business directory';
$activeNav = 'business';
require_once dirname(__DIR__) . '/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
$sql = "SELECT b.* FROM businesses b WHERE b.status = 'approved'";
$params = [];
if ($type !== '') {
    $sql .= ' AND b.business_type = ?';
    $params[] = $type;
}
if ($q !== '') {
    $sql .= ' AND (b.business_name LIKE ? OR b.barangay LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY b.business_name ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-4">
    <h1 class="h3 mb-3">Business directory</h1>
    <form class="row g-2 mb-4" method="get">
        <div class="col-md-4"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search name or barangay"></div>
        <div class="col-md-4">
            <select class="form-select" name="type">
                <option value="">All types</option>
                <?php foreach (['food_vendor','craft_business','restaurant','travel_agency','resort','recreation','service','pasalubong','fresh_produce'] as $t): ?>
                    <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Search</button></div>
    </form>
    <div class="row g-3">
        <?php foreach ($list as $b): ?>
            <?php $avg = business_avg_rating((int) $b['id']); ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-lk h-100">
                    <div class="card-body d-flex gap-3">
                        <?php $lg = $b['logo'] ? asset_url($b['logo']) : 'https://ui-avatars.com/api/?name=' . urlencode($b['business_name']); ?>
                        <img src="<?= e($lg) ?>" width="56" height="56" class="rounded border" alt="">
                        <div class="flex-grow-1">
                            <h2 class="h6 mb-1"><?= e($b['business_name']) ?></h2>
                            <p class="small text-muted mb-1"><?= e(str_limit((string) $b['description'], 90)) ?></p>
                            <p class="small mb-1"><i class="bi bi-star-fill text-warning"></i> <?= e((string) $avg) ?></p>
                            <p class="small mb-2"><i class="bi bi-telephone"></i> <?= e($b['contact_number'] ?? '') ?></p>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(vendor_profile_url((int) $b['id'], current_request_return_url())) ?>">View profile</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
