<?php

declare(strict_types=1);

$pageTitle = 'Cultural info';
$activeAdmin = 'cul';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

$categories = ['history', 'culture', 'tradition', 'festival', 'heritage', 'livelihood'];
$validStatuses = ['published', 'draft'];
$emptyRow = [
    'id' => 0,
    'title' => '',
    'content' => '',
    'category' => 'history',
    'status' => 'published',
];

function admin_cultural_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM cultural_information WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token. Please try again.');
        redirect(ADMIN_URL . 'cultural-info.php');
    }

    $action = (string) ($_POST['action'] ?? 'create');
    $rowId = (int) ($_POST['entry_id'] ?? 0);

    if ($action === 'delete') {
        if ($rowId > 0) {
            db()->prepare('DELETE FROM cultural_information WHERE id = ?')->execute([$rowId]);
            set_flash('success', 'Entry deleted.');
        }
        redirect(ADMIN_URL . 'cultural-info.php');
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $category = in_array(($_POST['category'] ?? ''), $categories, true) ? (string) $_POST['category'] : 'history';
    $status = in_array(($_POST['status'] ?? ''), $validStatuses, true) ? (string) $_POST['status'] : 'draft';

    if ($title === '' || $content === '') {
        set_flash('error', 'Title and content are required.');
        $target = $rowId > 0 ? ADMIN_URL . 'cultural-info.php?edit=' . $rowId : ADMIN_URL . 'cultural-info.php';
        redirect($target);
    }

    if ($action === 'update' && $rowId > 0 && admin_cultural_by_id($rowId)) {
        db()->prepare(
            'UPDATE cultural_information SET title = ?, content = ?, category = ?, status = ?, updated_at = NOW() WHERE id = ?'
        )->execute([$title, $content, $category, $status, $rowId]);
        set_flash('success', 'Entry updated.');
        redirect(ADMIN_URL . 'cultural-info.php');
    }

    db()->prepare(
        'INSERT INTO cultural_information (admin_id, title, content, category, status, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())'
    )->execute([current_user_id(), $title, $content, $category, $status]);
    set_flash('success', 'Entry saved.');
    redirect(ADMIN_URL . 'cultural-info.php');
}

$editId = (int) ($_GET['edit'] ?? 0);
$formRow = $editId > 0 ? (admin_cultural_by_id($editId) ?: $emptyRow) : $emptyRow;
$isEdit = $editId > 0 && (int) ($formRow['id'] ?? 0) === $editId;

$list = db()->query('SELECT * FROM cultural_information ORDER BY id DESC')->fetchAll();
require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Cultural information</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Manage heritage stories and cultural content for tourism pages.</p>
</div>
<div class="lk-panel mb-4">
    <div class="lk-panel-header">
        <h2><i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2 text-warning"></i><?= $isEdit ? 'Edit entry' : 'Add entry' ?></h2>
        <?php if ($isEdit): ?>
            <a href="<?= e(ADMIN_URL) ?>cultural-info.php" class="btn btn-sm btn-outline-secondary">Cancel edit</a>
        <?php endif; ?>
    </div>
    <div class="lk-panel-body">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="entry_id" value="<?= (int) $formRow['id'] ?>">
            <?php endif; ?>
            <label class="form-label">Title</label>
            <input class="form-control mb-2" name="title" value="<?= e((string) ($formRow['title'] ?? '')) ?>" required>
            <label class="form-label">Content</label>
            <textarea class="form-control mb-2" name="content" rows="4" required><?= e((string) ($formRow['content'] ?? '')) ?></textarea>
            <label class="form-label">Category</label>
            <select class="form-select mb-2" name="category">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c) ?>" <?= ($formRow['category'] ?? '') === $c ? 'selected' : '' ?>><?= e(ucfirst($c)) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="form-label">Status</label>
            <select class="form-select mb-3" name="status">
                <option value="published" <?= ($formRow['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($formRow['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
            <button class="btn btn-lk-orange btn-sm" type="submit"><?= $isEdit ? 'Save changes' : 'Save' ?></button>
        </form>
    </div>
</div>
<div class="lk-panel">
    <div class="lk-panel-header"><h2>All entries</h2></div>
    <div class="lk-dash-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($list)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No entries yet.</td></tr>
            <?php else: ?>
                <?php foreach ($list as $r): ?>
                <tr>
                    <td><strong><?= e($r['title']) ?></strong></td>
                    <td><span class="badge bg-info text-dark"><?= e($r['category']) ?></span></td>
                    <td><span class="badge bg-<?= ($r['status'] ?? '') === 'published' ? 'success' : 'secondary' ?>"><?= e($r['status']) ?></span></td>
                    <td class="text-end">
                        <a href="<?= e(ADMIN_URL) ?>cultural-info.php?edit=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this entry?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="entry_id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
