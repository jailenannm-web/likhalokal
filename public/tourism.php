<?php

declare(strict_types=1);

$pageTitle = 'Tourism';
$activeNav = 'tourism';
require_once dirname(__DIR__) . '/bootstrap.php';

$cat = $_GET['category'] ?? '';
$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM tourist_attractions WHERE status = 'published'";
$params = [];
if ($cat !== '') {
    $sql .= ' AND category = ?';
    $params[] = $cat;
}
if ($q !== '') {
    $sql .= ' AND (attraction_name LIKE ? OR description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY attraction_name ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

$extraHead = '';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<section class="hero mb-4" style="background-image:url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1600&q=80');">
    <div class="container hero-inner py-5">
        <h1 class="display-5 hero-title">TUKLAS LAKBAY LOKAL</h1>
        <p class="lead hero-sub">Byahe, Kwento, at Kultura.</p>
    </div>
</section>

<div class="container pb-5">
    <form class="row g-2 mb-4" method="get">
        <div class="col-md-4">
            <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search attractions">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All categories</option>
                <?php foreach (['heritage_site','beach','island','church','landmark','eco_tourism','cultural_site','museum','other'] as $c): ?>
                    <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>
    </form>

    <p class="text-center text-muted col-lg-8 mx-auto mb-5">From heritage walks to pristine shores, Vinzons offers nature, culture, and history for every traveler.</p>

    <div class="row g-4">
        <?php foreach ($list as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-lk h-100 tourism-card">
                    <?php $im = $a['image'] ? asset_url($a['image']) : 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=800&q=80'; ?>
                    <img src="<?= e($im) ?>" alt="">
                    <div class="card-body d-flex flex-column">
                        <span class="badge badge-soft mb-2"><?= e($a['category']) ?></span>
                        <h5><?= e($a['attraction_name']) ?></h5>
                        <p class="small text-muted flex-grow-1"><?= e(str_limit((string) $a['description'], 140)) ?></p>
                        <p class="small mb-1"><strong>Entrance:</strong> <?= e($a['entrance_fee'] ?? '—') ?></p>
                        <p class="small mb-3"><strong>Best time:</strong> <?= e($a['best_time_to_visit'] ?? '—') ?></p>
                        <div class="d-flex gap-2 mt-auto">
                            <a class="btn btn-sm btn-primary" href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $a['id'] ?>">View details</a>
                            <?php
                            $dir = ($a['latitude'] && $a['longitude'])
                                ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($a['latitude'] . ',' . $a['longitude'])
                                : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($a['address'] ?: $a['attraction_name']);
                            ?>
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="<?= e($dir) ?>">Get directions</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5">
        <h3 class="h5 mb-2">Map overview</h3>
        <div id="tourismMap" style="height:360px;border-radius:12px;background:#e9ecef;"></div>
    </div>
</div>

<?php
$first = $list[0] ?? null;
$lat = $first['latitude'] ?? '';
$lng = $first['longitude'] ?? '';
$title = $first['attraction_name'] ?? 'Vinzons';
$addr = $first['address'] ?? '';
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/maps.js"></script><script>
document.addEventListener("DOMContentLoaded", function () {
  likhaInitMap(document.getElementById("tourismMap"), ' . json_encode($lat) . ', ' . json_encode($lng) . ', ' . json_encode($title) . ', ' . json_encode($addr) . ');
});
</script>';
require BASE_PATH . '/includes/footer.php';
