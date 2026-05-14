<?php

declare(strict_types=1);

$pageTitle = 'Products';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
$q = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, b.business_name, b.id AS business_id FROM products p JOIN businesses b ON b.id = p.business_id WHERE b.status = 'approved'";
$params = [];
if ($category !== '') {
    $sql .= ' AND p.category = ?';
    $params[] = $category;
}
if ($q !== '') {
    $sql .= ' AND (p.product_name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($sort === 'price_asc') {
    $sql .= ' ORDER BY p.price ASC';
} elseif ($sort === 'price_desc') {
    $sql .= ' ORDER BY p.price DESC';
} else {
    $sql .= ' ORDER BY p.created_at DESC';
}
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$featured = db()->query(
    "SELECT p.*, b.business_name FROM products p JOIN businesses b ON b.id = p.business_id WHERE b.status='approved' AND p.is_featured=1 ORDER BY p.price DESC LIMIT 1"
)->fetch();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<section class="hero mb-4" style="background-image:url('https://images.unsplash.com/photo-1587330979470-3595ac045ab0?auto=format&fit=crop&w=1600&q=80');">
    <div class="container hero-inner py-5">
        <h1 class="display-5 hero-title">SUPORTA LOKAL, LIKHA LOKAL</h1>
        <p class="lead hero-sub">Mga produktong tunay, gawa ng sariling komunidad.</p>
    </div>
</section>

<div class="container pb-5">
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a class="text-decoration-none" href="<?= e(BASE_URL) ?>products.php?category=local_delicacy">
                <div class="ratio ratio-4x3 rounded overflow-hidden shadow">
                    <img class="object-fit-cover" src="https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80" alt="">
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 text-white" style="background:linear-gradient(transparent,rgba(0,0,0,.75))">Local Delicacies</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="text-decoration-none" href="<?= e(BASE_URL) ?>products.php?category=handicraft">
                <div class="ratio ratio-4x3 rounded overflow-hidden shadow">
                    <img class="object-fit-cover" src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80" alt="">
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 text-white" style="background:linear-gradient(transparent,rgba(0,0,0,.75))">Handicrafts</div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <p class="text-muted small mb-0">From handcrafted souvenirs to fresh harvests and local delicacies, discover products made with skill, tradition, and the flavors of Vinzons.</p>
        </div>
    </div>

    <form class="row g-2 mb-4 align-items-end" method="get">
        <div class="col-md-3">
            <label class="form-label small mb-0">Search</label>
            <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Product name">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Category</label>
            <select class="form-select" name="category">
                <option value="">All</option>
                <option value="local_delicacy" <?= $category === 'local_delicacy' ? 'selected' : '' ?>>Local delicacies</option>
                <option value="handicraft" <?= $category === 'handicraft' ? 'selected' : '' ?>>Handicrafts</option>
                <option value="fresh_produce" <?= $category === 'fresh_produce' ? 'selected' : '' ?>>Fresh produce</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-0">Sort</label>
            <select class="form-select" name="sort">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price low–high</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price high–low</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100" type="submit">Apply</button>
        </div>
    </form>

    <?php if ($featured): ?>
        <div class="card card-lk mb-4" style="background:linear-gradient(to right,#e8f5e9,#fff);">
            <div class="row g-0 align-items-center">
                <div class="col-md-4">
                    <?php $fi = $featured['image'] ? asset_url($featured['image']) : 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=800&q=80'; ?>
                    <img src="<?= e($fi) ?>" class="img-fluid rounded-start w-100 h-100 object-fit-cover" style="min-height:200px;" alt="">
                </div>
                <div class="col-md-8 p-4">
                    <span class="badge bg-success mb-2">Featured</span>
                    <h3><?= e($featured['product_name']) ?></h3>
                    <p class="mb-2"><?= e(str_limit((string) $featured['description'], 220)) ?></p>
                    <p class="fw-bold text-success">₱<?= e(number_format((float) $featured['price'], 2)) ?> · <?= e($featured['business_name']) ?></p>
                    <a class="btn btn-lk-orange btn-sm" href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $featured['business_id'] ?>">See sellers</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="section-title mb-3">See <span class="accent">DEALS</span></h2>
    <div class="row g-4">
        <?php foreach ($products as $p): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card card-lk h-100">
                    <?php $img = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80'; ?>
                    <img src="<?= e($img) ?>" class="w-100" style="height:160px;object-fit:cover;" alt="">
                    <div class="card-body d-flex flex-column">
                        <h5 class="h6"><?= e($p['product_name']) ?></h5>
                        <p class="small text-muted flex-grow-1"><?= e(str_limit((string) $p['description'], 80)) ?></p>
                        <p class="fw-bold mb-1">₱<?= e(number_format((float) $p['price'], 2)) ?></p>
                        <p class="small text-secondary mb-2"><?= e($p['business_name']) ?></p>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $p['business_id'] ?>">See sellers</a>
                            <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
                                <a class="btn btn-sm btn-lk-orange" href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $p['business_id'] ?>&product_id=<?= (int) $p['id'] ?>">Chat seller</a>
                            <?php else: ?>
                                <a class="btn btn-sm btn-lk-orange" href="#" data-require-auth>Chat seller</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require BASE_PATH . '/includes/footer.php'; ?>
