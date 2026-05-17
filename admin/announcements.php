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
require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head"><h1 class="lk-dash-page-title mb-1">Announcements</h1><p class="lk-dash-page-lead text-muted mb-0">Publish news and updates for the community homepage.</p></div>
<div class="lk-panel mb-4"><div class="lk-panel-header"><h2><i class="bi bi-plus-circle me-2 text-warning"></i>New announcement</h2></div><div class="lk-panel-body">
<form method="post"><?= csrf_field() ?>
<label class="form-label">Title</label><input class="form-control mb-2" name="title" required>
<label class="form-label">Content</label><textarea class="form-control mb-2" name="content" rows="3" required></textarea>
<label class="form-label">Status</label><select class="form-select mb-3" name="status"><option value="published">Published</option><option value="draft">Draft</option></select>
<button class="btn btn-lk-orange btn-sm" type="submit">Publish</button>
</form></div></div>
<div class="lk-panel"><div class="lk-panel-header"><h2>All announcements</h2></div>
<?php if (empty($list)): ?><div class="lk-empty-state"><i class="bi bi-megaphone"></i><p class="mb-0">No announcements yet.</p></div>
<?php else: foreach ($list as $a): ?><div class="lk-msg-row d-flex justify-content-between"><strong><?= e($a['title']) ?></strong><span class="badge bg-<?= ($a['status'] ?? '') === 'published' ? 'success' : 'secondary' ?>"><?= e($a['status']) ?></span></div><?php endforeach; endif; ?>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
