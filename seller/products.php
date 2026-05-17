<?php

declare(strict_types=1);

$pageTitle = 'My products';
$activeSeller = 'prod';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$uid = current_user_id();
$stmt = db()->prepare("SELECT id, business_name FROM businesses WHERE user_id=? AND status='approved' LIMIT 1");
$stmt->execute([$uid]);
$business = $stmt->fetch();
$bid = $business ? (int) $business['id'] : 0;
$hasProductType = db_column_exists('products', 'product_type');

$categories = ['local_delicacy', 'handicraft', 'fresh_produce', 'service', 'tour_package', 'food', 'other'];
$productTypes = ['product', 'service', 'tour_package', 'accommodation', 'food', 'other'];
$availabilityOptions = ['available', 'unavailable'];

function seller_product_by_id(int $productId, int $businessId): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND business_id = ? LIMIT 1');
    $stmt->execute([$productId, $businessId]);
    $product = $stmt->fetch();
    return $product ?: null;
}

if ($bid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(SELLER_URL . 'products.php');
    }

    $action = (string) ($_POST['action'] ?? 'create');
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($action === 'delete') {
        db()->prepare('DELETE FROM products WHERE id = ? AND business_id = ?')->execute([$productId, $bid]);
        set_flash('success', 'Product removed.');
        redirect(SELLER_URL . 'products.php');
    }

    $existing = $productId > 0 ? seller_product_by_id($productId, $bid) : null;
    $name = trim((string) ($_POST['product_name'] ?? ''));
    $productType = in_array(($_POST['product_type'] ?? ''), $productTypes, true) ? (string) $_POST['product_type'] : 'product';
    $category = in_array(($_POST['category'] ?? ''), $categories, true) ? (string) $_POST['category'] : 'other';
    $description = trim((string) ($_POST['description'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $availability = in_array(($_POST['availability'] ?? ''), $availabilityOptions, true) ? (string) $_POST['availability'] : 'available';
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $errors = [];

    if ($name === '' || strlen($name) > 200) {
        $errors[] = 'Name is required and must be 200 characters or fewer.';
    }
    if ($price < 0) {
        $errors[] = 'Price cannot be negative.';
    }

    $image = $existing['image'] ?? null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload = save_upload($_FILES['image'], 'products');
        if ($upload) {
            $image = $upload;
        } else {
            $errors[] = 'Image must be a JPG, PNG, or WEBP file under the upload limit.';
        }
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', $errors));
        $target = $action === 'update' && $productId > 0 ? SELLER_URL . 'products.php?edit=' . $productId : SELLER_URL . 'products.php';
        redirect($target);
    }

    if ($action === 'update' && $existing) {
        $sql = 'UPDATE products SET product_name = ?, category = ?, description = ?, price = ?, image = ?, availability = ?, is_featured = ?';
        $params = [$name, $category, $description, $price, $image, $availability, $featured];
        if ($hasProductType) {
            $sql .= ', product_type = ?';
            $params[] = $productType;
        }
        $sql .= ', updated_at = NOW() WHERE id = ? AND business_id = ?';
        $params[] = $productId;
        $params[] = $bid;
        db()->prepare($sql)->execute($params);
        set_flash('success', 'Product updated.');
    } else {
        $columns = 'business_id, product_name, category, description, price, image, availability, is_featured';
        $marks = '?,?,?,?,?,?,?,?';
        $params = [$bid, $name, $category, $description, $price, $image, $availability, $featured];
        if ($hasProductType) {
            $columns .= ', product_type';
            $marks .= ',?';
            $params[] = $productType;
        }
        $stmt = db()->prepare(
            'INSERT INTO products (' . $columns . ', created_at, updated_at)
             VALUES (' . $marks . ',NOW(),NOW())'
        );
        $stmt->execute($params);
        set_flash('success', 'Product added successfully.');
    }
    redirect(SELLER_URL . 'products.php');
}

$products = [];
$editProduct = null;
if ($bid) {
    $p = db()->prepare('SELECT * FROM products WHERE business_id=? ORDER BY product_name');
    $p->execute([$bid]);
    $products = $p->fetchAll();
    $editId = (int) ($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $editProduct = seller_product_by_id($editId, $bid);
    }
}

$formProduct = $editProduct ?: [
    'id' => 0,
    'product_name' => '',
    'product_type' => 'product',
    'category' => 'local_delicacy',
    'description' => '',
    'price' => '',
    'image' => '',
    'availability' => 'available',
    'is_featured' => 0,
];

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="lk-dash-page-title mb-1">Products &amp; services</h1>
        <p class="lk-dash-page-lead text-muted mb-0">Add, manage, and showcase what your business offers on the marketplace.</p>
    </div>
    <?php if ($editProduct): ?>
        <a href="<?= e(SELLER_URL) ?>products.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i> Cancel edit</a>
    <?php endif; ?>
</div>

<?php if (!$bid): ?>
<div class="lk-panel">
    <div class="lk-empty-state">
        <i class="bi bi-shop"></i>
        <p class="mb-2 fw-semibold">Approved business required</p>
        <p class="small mb-3">You need an approved business profile before you can list products.</p>
        <a href="<?= e(SELLER_URL) ?>business-profile.php" class="btn btn-lk-orange btn-sm">Complete business profile</a>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="lk-panel h-100">
            <div class="lk-panel-header">
                <h2><i class="bi <?= $editProduct ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2 text-warning"></i><?= $editProduct ? 'Edit item' : 'Add item' ?></h2>
            </div>
            <div class="lk-panel-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $editProduct ? 'update' : 'create' ?>">
                    <input type="hidden" name="product_id" value="<?= (int) ($formProduct['id'] ?? 0) ?>">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input class="form-control" name="product_name" required maxlength="200" value="<?= e((string) $formProduct['product_name']) ?>">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="product_type">
                                <?php foreach ($productTypes as $type): ?>
                                    <option value="<?= e($type) ?>" <?= (($formProduct['product_type'] ?? 'product') === $type) ? 'selected' : '' ?>><?= e(product_type_label($type)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e($category) ?>" <?= (($formProduct['category'] ?? '') === $category) ? 'selected' : '' ?>><?= e(product_category_label($category)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= e((string) $formProduct['description']) ?></textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Price (PHP)</label>
                            <input class="form-control" type="number" step="0.01" min="0" name="price" required value="<?= e((string) $formProduct['price']) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Availability</label>
                            <select class="form-select" name="availability">
                                <?php foreach ($availabilityOptions as $option): ?>
                                    <option value="<?= e($option) ?>" <?= (($formProduct['availability'] ?? '') === $option) ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Image</label>
                        <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                        <?php if (!empty($formProduct['image'])): ?>
                            <img src="<?= e(media_url($formProduct['image'])) ?>" alt="" class="rounded mt-2 shadow-sm" style="width:120px;height:90px;object-fit:cover;">
                            <div class="form-text">Current image is kept when no new image is uploaded.</div>
                        <?php endif; ?>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="featuredProd" <?= (int) ($formProduct['is_featured'] ?? 0) === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featuredProd">Featured on marketplace</label>
                    </div>
                    <button class="btn btn-lk-orange" type="submit"><i class="bi bi-save me-1"></i><?= $editProduct ? ' Save changes' : ' Add item' ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="lk-panel h-100">
            <div class="lk-panel-header">
                <h2><i class="bi bi-box-seam me-2 text-warning"></i>Your catalog</h2>
                <span class="badge bg-light text-dark"><?= count($products) ?> item(s)</span>
            </div>
            <?php if (empty($products)): ?>
                <div class="lk-empty-state">
                    <i class="bi bi-basket"></i>
                    <p class="mb-0">No products yet. Add your first item using the form.</p>
                </div>
            <?php else: ?>
            <div class="lk-dash-table-wrap">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type / Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= e(media_url($product['image'] ?? null, asset_url('images/products-hero.png'))) ?>" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                                    <div>
                                        <strong><?= e($product['product_name']) ?></strong>
                                        <?php if ((int) ($product['is_featured'] ?? 0) === 1): ?>
                                            <span class="badge bg-warning text-dark ms-1">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted">
                                <?= e(product_type_label($product['product_type'] ?? null)) ?><br>
                                <?= e(product_category_label($product['category'] ?? null)) ?>
                            </td>
                            <td>PHP <?= e(number_format((float) $product['price'], 2)) ?></td>
                            <td>
                                <span class="badge bg-<?= ($product['availability'] ?? '') === 'available' ? 'success' : 'secondary' ?>">
                                    <?= e(ucfirst((string) ($product['availability'] ?? 'available'))) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= e(SELLER_URL) ?>products.php?edit=<?= (int) $product['id'] ?>"><i class="bi bi-pencil"></i></a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
