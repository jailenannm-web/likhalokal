<?php

declare(strict_types=1);

$pageTitle = 'Cultural information';
$activeNav = 'tourism';
require_once dirname(__DIR__) . '/bootstrap.php';

$cat = $_GET['category'] ?? '';
$sql = "SELECT * FROM cultural_information WHERE status='published'";
$params = [];
if ($cat !== '') {
    $sql .= ' AND category = ?';
    $params[] = $cat;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5">
    <h1 class="h3 mb-3">Cultural information</h1>
    <div class="mb-3">
        <a class="btn btn-sm btn-outline-secondary <?= $cat === '' ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>cultural-info.php">All</a>
        <?php foreach (['history','culture','tradition','festival','heritage','livelihood'] as $c): ?>
            <a class="btn btn-sm btn-outline-secondary <?= $cat === $c ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>cultural-info.php?category=<?= e($c) ?>"><?= e($c) ?></a>
        <?php endforeach; ?>
    </div>
    <div class="row g-4">
        <?php foreach ($list as $row): ?>
            <div class="col-md-6">
                <div class="card card-lk h-100">
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?= e($row['category']) ?></span>
                        <h2 class="h5"><?= e($row['title']) ?></h2>
                        <p class="small"><?= nl2br(e((string) $row['content'])) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
