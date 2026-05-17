<?php

declare(strict_types=1);

$pageTitle = 'Business Applications';
$activeAdmin = 'apps';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token');
        redirect(ADMIN_URL . 'business-applications.php');
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
            log_activity(current_user_id(), 'admin_business', 'approve #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business approved successfully.');
        } elseif ($act === 'reject') {
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
            db()->prepare("UPDATE businesses SET status='rejected', rejection_reason=? WHERE id=?")->execute([$reason, $id]);
            notify_user((int) $biz['user_id'], 'Business rejected', 'Your business application was rejected.', 'error');
            log_activity(current_user_id(), 'admin_business', 'reject #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Business rejected successfully.');
        }
    }
    redirect(ADMIN_URL . 'business-applications.php');
}

$list = db()->query(
    "SELECT b.*, u.email AS owner_email, u.full_name AS owner_name FROM businesses b JOIN users u ON u.id = b.user_id WHERE b.status='pending' ORDER BY b.created_at DESC"
)->fetchAll();

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Business Applications</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Review new seller-submitted business registrations.</p>
</div>
<div class="lk-panel">
<div class="lk-dash-table-wrap">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>ID</th><th>Business Name</th><th>Owner</th><th>Type</th><th>Category</th><th>Contact</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($list as $b): ?>
            <tr>
                <td><?= (int) $b['id'] ?></td>
                <td><?= e($b['business_name']) ?></td>
                <td class="small">
                    <div><?= e($b['owner_name']) ?></div>
                    <div class="text-muted"><?= e($b['owner_email']) ?></div>
                </td>
                <td><?= e(business_type_label((string) $b['business_type'])) ?></td>
                <td><?= e($b['business_category'] ?: '-') ?></td>
                <td><?= e($b['contact_number'] ?: '-') ?></td>
                <td><span class="badge bg-<?= business_status_badge_class((string) $b['status']) ?>"><?= e($b['status']) ?></span></td>
                <td>
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
                        <input type="text" name="rejection_reason" class="form-control form-control-sm d-inline-block" style="width:140px" placeholder="Reason" required>
                        <button class="btn btn-sm btn-warning" type="submit">Reject</button>
                    </form>
                    <a class="btn btn-sm btn-outline-primary" href="<?= e(vendor_profile_url((int) $b['id'], current_request_return_url())) ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($list)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No pending business applications.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
