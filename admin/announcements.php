<?php

declare(strict_types=1);

$pageTitle = 'Announcements';
$activeAdmin = 'ann';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $stmt = db()->prepare(
        'INSERT INTO announcements (admin_id, title, content, status, created_at, updated_at) VALUES (?,?,?,?,NOW(),NOW())'
    );
    $stmt->execute([current_user_id(), trim($_POST['title'] ?? ''), trim($_POST['content'] ?? ''), $_POST['status'] ?? 'published']);
    set_flash('success', 'Announcement saved');
    redirect(ADMIN_URL . 'announcements.php');
}

$list = db()->query('SELECT * FROM announcements ORDER BY id DESC')->fetchAll();
require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Announcements</h1>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<div class="card card-lk mb-4"><div class="card-body">
<form method="post"><?= csrf_field() ?>
<input class="form-control mb-2" name="title" placeholder="Title" required>
<textarea class="form-control mb-2" name="content" rows="3" required></textarea>
<select class="form-select mb-2" name="status"><option value="published">Published</option><option value="draft">Draft</option></select>
<button class="btn btn-primary btn-sm" type="submit">Publish</button>
</form>
</div></div>
<ul class="list-group"><?php foreach ($list as $a): ?><li class="list-group-item"><strong><?= e($a['title']) ?></strong> — <?= e($a['status']) ?></li><?php endforeach; ?></ul>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
