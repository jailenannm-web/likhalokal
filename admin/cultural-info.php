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
require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head"><h1 class="lk-dash-page-title mb-1">Cultural information</h1><p class="lk-dash-page-lead text-muted mb-0">Manage heritage stories and cultural content for tourism pages.</p></div>
<div class="lk-panel mb-4"><div class="lk-panel-header"><h2><i class="bi bi-plus-circle me-2 text-warning"></i>Add entry</h2></div><div class="lk-panel-body">
<form method="post"><?= csrf_field() ?>
<label class="form-label">Title</label><input class="form-control mb-2" name="title" required>
<textarea class="form-control mb-2" name="content" rows="3" required></textarea>
<select class="form-select mb-2" name="category"><?php foreach (['history','culture','tradition','festival','heritage','livelihood'] as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select>
<select class="form-select mb-2" name="status"><option value="published">Published</option><option value="draft">Draft</option></select>
<button class="btn btn-lk-orange btn-sm" type="submit">Save</button>
</form></div></div>
<div class="lk-panel"><div class="lk-dash-table-wrap"><table class="table table-hover mb-0"><thead><tr><th>Title</th><th>Category</th><th>Status</th></tr></thead><tbody>
<?php foreach ($list as $r): ?><tr><td><?= e($r['title']) ?></td><td><?= e($r['category']) ?></td><td><span class="badge bg-<?= ($r['status'] ?? '') === 'published' ? 'success' : 'secondary' ?>"><?= e($r['status']) ?></span></td></tr><?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="3" class="text-center text-muted py-4">No entries yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
