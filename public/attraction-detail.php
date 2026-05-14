<?php

declare(strict_types=1);

$pageTitle = 'Attraction';
$activeNav = 'tourism';
require_once dirname(__DIR__) . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM tourist_attractions WHERE id = ? AND status = 'published' LIMIT 1");
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) {
    http_response_code(404);
    echo 'Attraction not found.';
    exit;
}

$rstmt = db()->prepare(
    "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.attraction_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20"
);
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll();

require_once BASE_PATH . '/middleware/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    require_once BASE_PATH . '/middleware/auth.php';
    require_login();
    if (current_user_role() !== 'local_user') {
        set_flash('error', 'Only local users may submit reviews.');
        redirect(BASE_URL . 'attraction-detail.php?id=' . $id);
    }
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token.');
        redirect(BASE_URL . 'attraction-detail.php?id=' . $id);
    }
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    if ($rating >= 1 && $rating <= 5) {
        $ins = db()->prepare(
            'INSERT INTO reviews (user_id, business_id, attraction_id, rating, comment, status, created_at, updated_at) VALUES (?,NULL,?,?,?,\'pending\',NOW(),NOW())'
        );
        $ins->execute([current_user_id(), $id, $rating, $comment]);
        set_flash('success', 'Review submitted for moderation.');
    }
    redirect(BASE_URL . 'attraction-detail.php?id=' . $id);
}

$pageTitle = $a['attraction_name'];
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$dir = ($a['latitude'] && $a['longitude'])
    ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($a['latitude'] . ',' . $a['longitude'])
    : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($a['address'] ?: $a['attraction_name']);
?>
<div class="container py-4">
    <?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-6">
            <?php $im = $a['image'] ? asset_url($a['image']) : 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80'; ?>
            <img src="<?= e($im) ?>" class="img-fluid rounded shadow" alt="">
        </div>
        <div class="col-lg-6">
            <span class="badge bg-secondary mb-2"><?= e($a['category']) ?></span>
            <h1 class="h3"><?= e($a['attraction_name']) ?></h1>
            <p><?= nl2br(e((string) $a['description'])) ?></p>
            <h2 class="h6">History</h2>
            <p class="small text-muted"><?= nl2br(e((string) ($a['history'] ?? ''))) ?></p>
            <h2 class="h6">Travel guide</h2>
            <p class="small"><?= nl2br(e((string) ($a['travel_guide'] ?? ''))) ?></p>
            <p><strong>Entrance fee:</strong> <?= e($a['entrance_fee'] ?? '') ?></p>
            <p><strong>Best time to visit:</strong> <?= e($a['best_time_to_visit'] ?? '') ?></p>
            <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="<?= e($dir) ?>">Get directions</a>
            <div id="attMap" class="mt-3 rounded" style="height:240px;background:#e9ecef;"></div>
        </div>
    </div>

    <h2 class="h5 mt-5">Reviews</h2>
    <?php foreach ($reviews as $r): ?>
        <div class="border rounded p-3 mb-2"><?= e($r['reviewer_name']) ?> — <?= (int) $r['rating'] ?>★<div class="small text-muted"><?= e($r['comment'] ?? '') ?></div></div>
    <?php endforeach; ?>

    <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
        <form method="post" class="mt-3 col-lg-6">
            <?= csrf_field() ?>
            <input type="hidden" name="review_submit" value="1">
            <label class="form-label">Your rating</label>
            <select name="rating" class="form-select mb-2" required><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?></select>
            <textarea name="comment" class="form-control mb-2" rows="3" required></textarea>
            <button class="btn btn-primary" type="submit">Submit review</button>
        </form>
    <?php endif; ?>
</div>
<?php
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/maps.js"></script><script>
document.addEventListener("DOMContentLoaded", function () {
  likhaInitMap(document.getElementById("attMap"), ' . json_encode($a['latitude']) . ', ' . json_encode($a['longitude']) . ', ' . json_encode($a['attraction_name']) . ', ' . json_encode($a['address'] ?? '') . ');
});
</script>';
require BASE_PATH . '/includes/footer.php';
