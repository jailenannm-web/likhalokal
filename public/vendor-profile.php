<?php

declare(strict_types=1);

$pageTitle = 'Vendor';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT b.*, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1');
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b || $b['status'] !== 'approved') {
    http_response_code(404);
    echo 'Business not found.';
    exit;
}

$pstmt = db()->prepare('SELECT * FROM products WHERE business_id = ? ORDER BY is_featured DESC, product_name ASC');
$pstmt->execute([$id]);
$products = $pstmt->fetchAll();

$rstmt = db()->prepare(
    "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.business_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20"
);
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll();
$avg = business_avg_rating($id);

require_once BASE_PATH . '/middleware/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    require_once BASE_PATH . '/middleware/auth.php';
    require_login();
    if (current_user_role() !== 'local_user') {
        set_flash('error', 'Only local users may submit reviews.');
        redirect(BASE_URL . 'vendor-profile.php?id=' . $id);
    }
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token.');
        redirect(BASE_URL . 'vendor-profile.php?id=' . $id);
    }
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    if ($rating >= 1 && $rating <= 5) {
        $ins = db()->prepare(
            'INSERT INTO reviews (user_id, business_id, attraction_id, rating, comment, status, created_at, updated_at) VALUES (?,?,NULL,?,?,\'pending\',NOW(),NOW())'
        );
        $ins->execute([current_user_id(), $id, $rating, $comment]);
        set_flash('success', 'Thank you! Your review is pending moderation.');
    }
    redirect(BASE_URL . 'vendor-profile.php?id=' . $id);
}

$pageTitle = $b['business_name'];
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

if ($m = flash('success')) {
    echo '<div class="container mt-3"><div class="alert alert-success">' . e($m) . '</div></div>';
}
if ($m = flash('error')) {
    echo '<div class="container mt-3"><div class="alert alert-danger">' . e($m) . '</div></div>';
}

$payments = json_decode((string) ($b['accepted_payments'] ?? '[]'), true) ?: [];
?>
<section class="py-4 bg-dark text-white">
    <div class="container">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="<?= e(BASE_URL) ?>products.php" class="text-white-50 text-decoration-none"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
        <h1 class="h3 mb-0"><?= e($b['business_name']) ?></h1>
        <p class="mb-0 small text-white-50"><?= e($b['business_type']) ?> · <?= e($b['barangay'] ?? '') ?></p>
    </div>
</section>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-5">
            <?php $cover = $b['cover_image'] ? asset_url($b['cover_image']) : 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1200&q=80'; ?>
            <div class="ratio ratio-4x3 rounded overflow-hidden shadow mb-3">
                <img src="<?= e($cover) ?>" class="object-fit-cover" alt="">
            </div>
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php $logo = $b['logo'] ? asset_url($b['logo']) : 'https://ui-avatars.com/api/?name=' . urlencode($b['business_name']); ?>
                <img src="<?= e($logo) ?>" width="72" height="72" class="rounded-circle border" alt="">
                <div>
                    <div class="fw-bold"><i class="bi bi-star-fill text-warning"></i> <?= e((string) $avg) ?> rating</div>
                    <div class="small text-muted"><?= e($b['address'] ?? '') ?></div>
                </div>
            </div>
            <p><?= nl2br(e((string) $b['description'])) ?></p>
            <p class="mb-1"><i class="bi bi-telephone"></i> <?= e($b['contact_number'] ?? '') ?></p>
            <p class="mb-1"><i class="bi bi-clock"></i> <?= e($b['operating_hours'] ?? '') ?></p>
            <p class="mb-2"><strong>Payments:</strong> <?= e(implode(', ', $payments)) ?></p>
            <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
                <a class="btn btn-lk-orange" href="<?= e(BASE_URL) ?>message.php?business_id=<?= $id ?>"><i class="bi bi-chat-dots"></i> Chat seller</a>
            <?php else: ?>
                <a class="btn btn-lk-orange" href="#" data-require-auth><i class="bi bi-chat-dots"></i> Chat seller</a>
            <?php endif; ?>
            <div id="bizMap" class="mt-3 rounded" style="height:220px;background:#e9ecef;"></div>
        </div>
        <div class="col-lg-7">
            <h2 class="h5 mb-3">Products &amp; services</h2>
            <div class="row g-3">
                <?php foreach ($products as $p): ?>
                    <div class="col-md-6">
                        <div class="card card-lk h-100">
                            <?php $pi = $p['image'] ? asset_url($p['image']) : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'; ?>
                            <img src="<?= e($pi) ?>" class="w-100" style="height:120px;object-fit:cover;" alt="">
                            <div class="card-body">
                                <h3 class="h6"><?= e($p['product_name']) ?></h3>
                                <p class="small mb-1">₱<?= e(number_format((float) $p['price'], 2)) ?></p>
                                <p class="small text-muted"><?= e(str_limit((string) $p['description'], 70)) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="h5 mt-4 mb-3">Reviews</h2>
            <?php foreach ($reviews as $r): ?>
                <div class="border rounded p-3 mb-2 bg-white">
                    <div class="fw-semibold"><?= e($r['reviewer_name']) ?> · <?= (int) $r['rating'] ?>★</div>
                    <div class="small text-muted"><?= e($r['comment'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>

            <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
                <form method="post" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="review_submit" value="1">
                    <label class="form-label">Rate your experience</label>
                    <select name="rating" class="form-select mb-2" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?> stars</option>
                        <?php endfor; ?>
                    </select>
                    <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Comment" required></textarea>
                    <button class="btn btn-primary btn-sm" type="submit">Submit review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/maps.js"></script><script>
document.addEventListener("DOMContentLoaded", function () {
  likhaInitMap(document.getElementById("bizMap"), ' . json_encode($b['latitude']) . ', ' . json_encode($b['longitude']) . ', ' . json_encode($b['business_name']) . ', ' . json_encode($b['address'] ?? '') . ');
});
</script>';
require BASE_PATH . '/includes/footer.php';
