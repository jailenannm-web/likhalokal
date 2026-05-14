<?php

declare(strict_types=1);

$pageTitle = 'Events';
$activeNav = 'home';
require_once dirname(__DIR__) . '/bootstrap.php';

$list = db()->query(
    "SELECT * FROM events WHERE status='published' ORDER BY event_date ASC LIMIT 50"
)->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5">
    <h1 class="h3 mb-4">Upcoming events</h1>
    <div class="row g-4">
        <?php foreach ($list as $e): ?>
            <div class="col-md-6">
                <div class="card card-lk h-100">
                    <?php $img = $e['image'] ? asset_url($e['image']) : 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&w=900&q=80'; ?>
                    <img src="<?= e($img) ?>" class="w-100" style="height:200px;object-fit:cover;" alt="">
                    <div class="card-body">
                        <div class="text-muted small"><?= e($e['event_date']) ?> <?= $e['event_time'] ? '· ' . e(substr((string) $e['event_time'], 0, 5)) : '' ?></div>
                        <h2 class="h5"><?= e($e['title']) ?></h2>
                        <p><?= nl2br(e((string) $e['description'])) ?></p>
                        <p class="small mb-0"><i class="bi bi-geo-alt"></i> <?= e($e['location'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
