<?php

declare(strict_types=1);

$pageTitle = 'Manage businesses';
$activeAdmin = 'biz';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token');
        redirect(ADMIN_URL . 'businesses.php');
    }
    $act = $_POST['admin_action'] ?? '';
    $id = (int) ($_POST['business_id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ?');
    $stmt->execute([$id]);
    $biz = $stmt->fetch();
    if ($biz) {
        if ($act === 'approve') {
            db()->prepare("UPDATE businesses SET status='approved', approved_by=?, approved_at=NOW(), rejection_reason=NULL WHERE id=?")->execute([current_user_id(), $id]);
            notify_user((int) $biz['user_id'], 'Business approved', 'Your listing is now public.', 'success');
        } elseif ($act === 'reject') {
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
            db()->prepare("UPDATE businesses SET status='rejected', rejection_reason=? WHERE id=?")->execute([$reason, $id]);
        } elseif ($act === 'suspend') {
            db()->prepare("UPDATE businesses SET status='suspended' WHERE id=?")->execute([$id]);
        }
        log_activity(current_user_id(), 'admin_business', $act . ' #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
        set_flash('success', 'Business updated.');
    }
    redirect(ADMIN_URL . 'businesses.php');
}

$tab = $_GET['tab'] ?? 'pending';
if ($tab === 'pending') {
    $list = db()->query(
        "SELECT b.*, u.email AS owner_email FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.status='pending' ORDER BY b.created_at DESC"
    )->fetchAll();
} elseif ($tab === 'approved') {
    $list = db()->query(
        "SELECT b.*, u.email AS owner_email FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.status='approved' ORDER BY b.created_at DESC"
    )->fetchAll();
} else {
    $list = db()->query(
        'SELECT b.*, u.email AS owner_email FROM businesses b JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC'
    )->fetchAll();
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<h1 class="h4 mb-3">Businesses</h1>
<div class="btn-group mb-3">
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'pending' ? 'active' : '' ?>" href="?tab=pending">Pending</a>
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'approved' ? 'active' : '' ?>" href="?tab=approved">Approved</a>
    <a class="btn btn-sm btn-outline-secondary <?= $tab === 'all' ? 'active' : '' ?>" href="?tab=all">All</a>
</div>
<div class="table-responsive">
    <table class="table table-admin table-striped align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Owner</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($list as $b): ?>
            <tr>
                <td><?= (int) $b['id'] ?></td>
                <td><?= e($b['business_name']) ?></td>
                <td class="small"><?= e($b['owner_email']) ?></td>
                <td><span class="badge bg-secondary"><?= e($b['status']) ?></span></td>
                <td>
                    <?php if ($b['status'] === 'pending'): ?>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="approve">
                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                        </form>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="reject">
                            <input type="text" name="rejection_reason" class="form-control form-control-sm d-inline-block" style="width:160px" placeholder="Reason" required>
                            <button class="btn btn-sm btn-warning" type="submit">Reject</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($b['status'] === 'approved'): ?>
                        <form method="post" class="d-inline" onsubmit="return confirm('Suspend?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="suspend">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Suspend</button>
                        </form>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= e(BASE_URL) ?>vendor-profile.php?id=<?= (int) $b['id'] ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
