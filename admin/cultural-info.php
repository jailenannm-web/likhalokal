<?php

declare(strict_types=1);

$pageTitle = 'Cultural info';
$activeAdmin = 'cul';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $stmt = db()->prepare(
        'INSERT INTO cultural_information (admin_id, title, content, category, status, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())'
    );
    $stmt->execute([
        current_user_id(),
        trim($_POST['title'] ?? ''),
        trim($_POST['content'] ?? ''),
        $_POST['category'] ?? 'history',
        $_POST['status'] ?? 'published',
    ]);
    set_flash('success', 'Saved');
    redirect(ADMIN_URL . 'cultural-info.php');
}

$list = db()->query('SELECT * FROM cultural_information ORDER BY id DESC')->fetchAll();
require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Cultural information</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<div class="card card-lk mb-4"><div class="card-body">
<form method="post"><?= csrf_field() ?>
<input class="form-control mb-2" name="title" required>
<textarea class="form-control mb-2" name="content" rows="3" required></textarea>
<select class="form-select mb-2" name="category"><?php foreach (['history','culture','tradition','festival','heritage','livelihood'] as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select>
<select class="form-select mb-2" name="status"><option value="published">Published</option><option value="draft">Draft</option></select>
<button class="btn btn-primary btn-sm" type="submit">Save</button>
</form>
</div></div>
<table class="table table-sm"><tbody><?php foreach ($list as $r): ?><tr><td><?= e($r['title']) ?></td><td><?= e($r['category']) ?></td></tr><?php endforeach; ?></tbody></table>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
