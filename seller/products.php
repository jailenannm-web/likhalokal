<?php

declare(strict_types=1);

$pageTitle = 'My products';
$activeSeller = 'prod';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$stmt = db()->prepare("SELECT id FROM businesses WHERE user_id=? AND status='approved' LIMIT 1");
$stmt->execute([$uid]);
$row = $stmt->fetch();
$bid = $row ? (int) $row['id'] : 0;

if ($bid && $_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    if (isset($_POST['delete_id'])) {
        $pid = (int) $_POST['delete_id'];
        db()->prepare('DELETE FROM products WHERE id=? AND business_id=?')->execute([$pid, $bid]);
    } else {
        $stmt = db()->prepare(
            'INSERT INTO products (business_id, product_name, category, description, price, availability, is_featured, created_at, updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())'
        );
        $stmt->execute([
            $bid,
            trim($_POST['product_name'] ?? ''),
            $_POST['category'] ?? 'other',
            trim($_POST['description'] ?? ''),
            (float) ($_POST['price'] ?? 0),
            $_POST['availability'] ?? 'available',
            isset($_POST['is_featured']) ? 1 : 0,
        ]);
        $newId = (int) db()->lastInsertId();
        if (!empty($_FILES['image']['tmp_name'])) {
            $up = save_upload($_FILES['image'], 'products');
            if ($up) {
                db()->prepare('UPDATE products SET image=? WHERE id=?')->execute([$up, $newId]);
            }
        }
    }
    redirect(SELLER_URL . 'products.php');
}

$products = [];
if ($bid) {
    $p = db()->prepare('SELECT * FROM products WHERE business_id=? ORDER BY product_name');
    $p->execute([$bid]);
    $products = $p->fetchAll();
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Products</h1>
<?php if (!$bid): ?>
    <div class="alert alert-warning">You need an approved business to add products.</div>
<?php else: ?>
<div class="row g-4">
<div class="col-lg-5">
<div class="card card-lk"><div class="card-body">
<h2 class="h6">Add product</h2>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<input class="form-control mb-2" name="product_name" required>
<select class="form-select mb-2" name="category"><?php foreach (['local_delicacy','handicraft','fresh_produce','service','tour_package','food','other'] as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select>
<textarea class="form-control mb-2" name="description" rows="2"></textarea>
<input class="form-control mb-2" type="number" step="0.01" name="price" placeholder="Price" required>
<input class="form-control mb-2" type="file" name="image" accept="image/*">
<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_featured" id="f"><label class="form-check-label" for="f">Featured</label></div>
<button class="btn btn-primary btn-sm" type="submit">Add</button>
</form>
</div></div>
</div>
<div class="col-lg-7">
<table class="table table-sm"><thead><tr><th>Name</th><th>Price</th><th></th></tr></thead><tbody>
<?php foreach ($products as $pr): ?>
<tr><td><?= e($pr['product_name']) ?></td><td><?= e(number_format((float) $pr['price'], 2)) ?></td>
<td><form method="post" onsubmit="return confirm('Delete?');"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int) $pr['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
<?php endforeach; ?>
</tbody></table>
</div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
