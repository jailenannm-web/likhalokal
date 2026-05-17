<?php

declare(strict_types=1);

$pageTitle = 'Search';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';

$businesses = [];
$products = [];
$attractions = [];
$events = [];
$announcements = [];
$cultural = [];

if ($q !== '') {
    $pdo = db();

    $stmt = $pdo->prepare(
        "SELECT id, business_name, business_type, description, address
         FROM businesses
         WHERE status = 'approved'
           AND (business_name LIKE ? OR description LIKE ? OR address LIKE ?)
         ORDER BY business_name ASC
         LIMIT 20"
    );
    $stmt->execute([$like, $like, $like]);
    $businesses = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT p.id, p.product_name, p.category, p.price, p.image, p.business_id, b.business_name
         FROM products p
         INNER JOIN businesses b ON b.id = p.business_id AND b.status = 'approved'
         WHERE p.availability = 'available'
           AND (p.product_name LIKE ? OR p.description LIKE ? OR p.category LIKE ?)
         ORDER BY p.product_name ASC
         LIMIT 24"
    );
    $stmt->execute([$like, $like, $like]);
    $products = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT id, attraction_name, category, description, image
         FROM tourist_attractions
         WHERE status = 'published'
           AND (attraction_name LIKE ? OR description LIKE ? OR category LIKE ?)
         ORDER BY attraction_name ASC
         LIMIT 20"
    );
    $stmt->execute([$like, $like, $like]);
    $attractions = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT id, title, description, event_date, location
         FROM events
         WHERE status = 'published'
           AND (title LIKE ? OR description LIKE ? OR location LIKE ?)
         ORDER BY event_date DESC
         LIMIT 15"
    );
    $stmt->execute([$like, $like, $like]);
    $events = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT id, title, content, created_at
         FROM announcements
         WHERE status = 'published'
           AND (title LIKE ? OR content LIKE ?)
         ORDER BY created_at DESC
         LIMIT 15"
    );
    $stmt->execute([$like, $like]);
    $announcements = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT id, title, content, category, image
         FROM cultural_information
         WHERE status = 'published'
           AND (title LIKE ? OR content LIKE ? OR category LIKE ?)
         ORDER BY title ASC
         LIMIT 15"
    );
    $stmt->execute([$like, $like, $like]);
    $cultural = $stmt->fetchAll();
}

$totalResults = count($businesses) + count($products) + count($attractions)
    + count($events) + count($announcements) + count($cultural);

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<main class="container py-5" style="margin-top: 90px;">
    <h1 class="h3 mb-4">Search LikhaLokal</h1>
    <form method="get" action="<?= e(BASE_URL) ?>search.php" class="row g-2 mb-4">
        <div class="col-md-10">
            <input type="search" name="q" class="form-control form-control-lg" placeholder="Search businesses, products, attractions, events…" value="<?= e($q) ?>" autofocus>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-warning btn-lg">Search</button>
        </div>
    </form>

    <?php if ($q === ''): ?>
        <p class="text-muted">Enter a keyword to search across the platform.</p>
    <?php elseif ($totalResults === 0): ?>
        <p class="text-muted">No results found for <strong><?= e($q) ?></strong>.</p>
    <?php else: ?>
        <p class="text-muted mb-4"><?= (int) $totalResults ?> result(s) for <strong><?= e($q) ?></strong></p>

        <?php if ($businesses): ?>
        <section class="mb-5">
            <h2 class="h5 text-primary border-bottom pb-2">Businesses</h2>
            <div class="row g-3">
                <?php foreach ($businesses as $b): ?>
                <div class="col-md-6">
                    <a href="<?= e(vendor_profile_url((int) $b['id'], current_request_return_url())) ?>" class="card h-100 text-decoration-none text-dark shadow-sm">
                        <div class="card-body">
                            <h3 class="h6 mb-1"><?= e($b['business_name']) ?></h3>
                            <p class="small text-muted mb-0"><?= e(business_type_label($b['business_type'])) ?> · <?= e(str_limit($b['description'], 100)) ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($products): ?>
        <section class="mb-5">
            <h2 class="h5 text-primary border-bottom pb-2">Marketplace Items</h2>
            <div class="row g-3">
                <?php foreach ($products as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?= e(vendor_profile_url((int) $p['business_id'], current_request_return_url())) ?>" class="card h-100 text-decoration-none text-dark shadow-sm">
                        <img src="<?= e(media_url($p['image'] ?? null)) ?>" class="card-img-top" alt="<?= e($p['product_name']) ?>" style="height:140px;object-fit:cover;">
                        <div class="card-body p-2">
                            <h3 class="h6 mb-0"><?= e($p['product_name']) ?></h3>
                            <p class="small text-muted mb-0"><?= e($p['business_name']) ?> · ₱<?= number_format((float) $p['price'], 2) ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($attractions): ?>
        <section class="mb-5">
            <h2 class="h5 text-primary border-bottom pb-2">Tourist Attractions</h2>
            <ul class="list-group">
                <?php foreach ($attractions as $a): ?>
                <li class="list-group-item">
                    <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $a['id'] ?>"><?= e($a['attraction_name']) ?></a>
                    <span class="text-muted small"> — <?= e(ucwords(str_replace('_', ' ', $a['category']))) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php if ($events): ?>
        <section class="mb-5">
            <h2 class="h5 text-primary border-bottom pb-2">Events</h2>
            <ul class="list-group">
                <?php foreach ($events as $ev): ?>
                <li class="list-group-item">
                    <a href="<?= e(BASE_URL) ?>events.php#event-<?= (int) $ev['id'] ?>"><?= e($ev['title']) ?></a>
                    <span class="text-muted small"> — <?= e($ev['event_date']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php if ($announcements): ?>
        <section class="mb-5">
            <h2 class="h5 text-primary border-bottom pb-2">Announcements</h2>
            <ul class="list-group">
                <?php foreach ($announcements as $an): ?>
                <li class="list-group-item">
                    <a href="<?= e(BASE_URL) ?>index.php#announcements"><?= e($an['title']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php if ($cultural): ?>
        <section class="mb-5">
            <h2 class="h5 text-primary border-bottom pb-2">Cultural Information</h2>
            <ul class="list-group">
                <?php foreach ($cultural as $c): ?>
                <li class="list-group-item">
                    <a href="<?= e(BASE_URL) ?>cultural-info.php"><?= e($c['title']) ?></a>
                    <span class="text-muted small"> — <?= e($c['category']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require BASE_PATH . '/includes/footer.php'; ?>
