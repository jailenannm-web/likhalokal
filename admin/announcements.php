<?php

declare(strict_types=1);

$pageTitle = 'Announcements';
$activeAdmin = 'ann';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$validStatuses = ['published', 'draft'];
$emptyRow = [
    'id' => 0,
    'title' => '',
    'content' => '',
    'status' => 'published',
];

function admin_announcement_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM announcements WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(ADMIN_URL . 'announcements.php');
    }

    $action = (string) ($_POST['action'] ?? 'create');
    $rowId = (int) ($_POST['announcement_id'] ?? 0);

    if ($action === 'delete') {
        if ($rowId > 0) {
            db()->prepare('DELETE FROM announcements WHERE id = ?')->execute([$rowId]);
            set_flash('success', 'Announcement deleted.');
        }
        redirect(ADMIN_URL . 'announcements.php');
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $status = in_array(($_POST['status'] ?? ''), $validStatuses, true) ? (string) $_POST['status'] : 'draft';

    if ($title === '' || $content === '') {
        set_flash('error', 'Title and content are required.');
        $target = $rowId > 0 ? ADMIN_URL . 'announcements.php?edit=' . $rowId : ADMIN_URL . 'announcements.php';
        redirect($target);
    }

    if ($action === 'update' && $rowId > 0 && admin_announcement_by_id($rowId)) {
        db()->prepare(
            'UPDATE announcements SET title = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?'
        )->execute([$title, $content, $status, $rowId]);
        set_flash('success', 'Announcement updated.');
        redirect(ADMIN_URL . 'announcements.php');
    }

    db()->prepare(
        'INSERT INTO announcements (admin_id, title, content, status, created_at, updated_at) VALUES (?,?,?,?,NOW(),NOW())'
    )->execute([current_user_id(), $title, $content, $status]);
    set_flash('success', 'Announcement published.');
    redirect(ADMIN_URL . 'announcements.php');
}

$editId = (int) ($_GET['edit'] ?? 0);
$formRow = $editId > 0 ? (admin_announcement_by_id($editId) ?: $emptyRow) : $emptyRow;
$isEdit = $editId > 0 && (int) ($formRow['id'] ?? 0) === $editId;

$list = db()->query('SELECT * FROM announcements ORDER BY id DESC')->fetchAll();
require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Announcements</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Publish news and updates for the community homepage.</p>
</div>
<div class="lk-panel mb-4">
    <div class="lk-panel-header">
        <h2><i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2 text-warning"></i><?= $isEdit ? 'Edit announcement' : 'New announcement' ?></h2>
        <?php if ($isEdit): ?>
            <a href="<?= e(ADMIN_URL) ?>announcements.php" class="btn btn-sm btn-outline-secondary">Cancel edit</a>
        <?php endif; ?>
    </div>
    <div class="lk-panel-body">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="announcement_id" value="<?= (int) $formRow['id'] ?>">
            <?php endif; ?>
            <label class="form-label">Title</label>
            <input class="form-control mb-2" name="title" value="<?= e((string) ($formRow['title'] ?? '')) ?>" required>
            <label class="form-label">Content</label>
            <textarea class="form-control mb-2" name="content" rows="4" required><?= e((string) ($formRow['content'] ?? '')) ?></textarea>
            <label class="form-label">Status</label>
            <select class="form-select mb-3" name="status">
                <option value="published" <?= ($formRow['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($formRow['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
            <button class="btn btn-lk-orange btn-sm" type="submit"><?= $isEdit ? 'Save changes' : 'Publish' ?></button>
        </form>
    </div>
</div>
<div class="lk-panel">
    <div class="lk-panel-header"><h2>All announcements</h2></div>
    <?php if (empty($list)): ?>
        <div class="lk-empty-state"><i class="bi bi-megaphone"></i><p class="mb-0">No announcements yet.</p></div>
    <?php else: ?>
        <div class="lk-dash-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $a): ?>
                    <tr>
                        <td><strong><?= e($a['title']) ?></strong></td>
                        <td>
                            <span class="badge bg-<?= ($a['status'] ?? '') === 'published' ? 'success' : 'secondary' ?>"><?= e($a['status']) ?></span>
                        </td>
                        <td class="small text-muted"><?= e(format_datetime_short($a['created_at'] ?? '')) ?></td>
                        <td class="text-end">
                            <a href="<?= e(ADMIN_URL) ?>announcements.php?edit=<?= (int) $a['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this announcement?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="announcement_id" value="<?= (int) $a['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
