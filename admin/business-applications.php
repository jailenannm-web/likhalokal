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
    $stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? AND status = \'pending\' LIMIT 1');
    $stmt->execute([$id]);
    $biz = $stmt->fetch();
    if ($biz) {
        if ($act === 'approve') {
            db()->prepare(
                "UPDATE businesses SET status='approved', approved_by=?, approved_at=NOW(), rejection_reason=NULL, updated_at=NOW() WHERE id=?"
            )->execute([current_user_id(), $id]);
            db()->prepare("UPDATE users SET role='seller', updated_at=NOW() WHERE id=? AND role='local_user'")
                ->execute([(int) $biz['user_id']]);
            notify_user((int) $biz['user_id'], 'Business approved', 'Your listing is now public.', 'success');
            log_activity(current_user_id(), 'admin_business', 'approve #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Application approved. The business now appears under Manage Businesses.');
        } elseif ($act === 'reject') {
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
            if ($reason === '') {
                set_flash('error', 'Please provide a rejection reason.');
                redirect(ADMIN_URL . 'business-applications.php');
            }
            db()->prepare(
                "UPDATE businesses SET status='rejected', rejection_reason=?, updated_at=NOW() WHERE id=?"
            )->execute([$reason, $id]);
            notify_user((int) $biz['user_id'], 'Business rejected', 'Your business application was rejected: ' . $reason, 'error');
            log_activity(current_user_id(), 'admin_business', 'reject #' . $id, $_SERVER['REMOTE_ADDR'] ?? null);
            set_flash('success', 'Application rejected.');
        }
    } else {
        set_flash('error', 'Application not found or no longer pending.');
    }
    redirect(ADMIN_URL . 'business-applications.php');
}

$stmt = db()->prepare(
    "SELECT b.*, u.email AS owner_email, u.full_name AS owner_name, u.contact_number AS owner_contact
     FROM businesses b
     INNER JOIN users u ON u.id = b.user_id
     WHERE b.status = 'pending'
     ORDER BY b.created_at DESC"
);
$stmt->execute();
$list = $stmt->fetchAll();
$pendingCount = count($list);

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head lk-admin-apps-head">
    <div>
        <span class="badge bg-warning text-dark mb-2">Registration queue</span>
        <h1 class="lk-dash-page-title mb-1">Business Applications</h1>
        <p class="lk-dash-page-lead text-muted mb-0">
            Review <strong>new seller-submitted registrations only</strong>. Approve to publish on Local Business / Marketplace, or reject with a reason.
            Approved and rejected listings are managed under <a href="<?= e(ADMIN_URL) ?>businesses.php">Manage Businesses</a>.
        </p>
    </div>
    <div class="text-lg-end">
        <div class="lk-stat-pill"><i class="bi bi-hourglass-split me-1"></i> <?= (int) $pendingCount ?> pending</div>
    </div>
</div>

<?php if (empty($list)): ?>
<div class="lk-panel">
    <div class="lk-empty-state py-5">
        <i class="bi bi-inbox"></i>
        <h2 class="h5 mb-2">No pending applications</h2>
        <p class="text-muted mb-3">When sellers register a business, it will appear here for approval.</p>
        <a href="<?= e(ADMIN_URL) ?>businesses.php" class="btn btn-outline-secondary btn-sm">Go to Manage Businesses</a>
    </div>
</div>
<?php else: ?>
<div class="lk-panel lk-admin-apps-panel">
    <div class="lk-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="mb-0"><i class="bi bi-file-earmark-check me-2 text-warning"></i> Pending applications</h2>
        <span class="small text-muted">Submitted dates use server time</span>
    </div>
    <div class="lk-dash-table-wrap">
        <table class="table table-hover align-middle mb-0 lk-admin-apps-table">
            <thead>
                <tr>
                    <th>Business</th>
                    <th>Owner</th>
                    <th>Type / Category</th>
                    <th>Contact</th>
                    <th>Location</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $b): ?>
                <tr>
                    <td>
                        <strong class="d-block"><?= e($b['business_name']) ?></strong>
                        <span class="small text-muted">#<?= (int) $b['id'] ?></span>
                    </td>
                    <td class="small">
                        <div><?= e($b['owner_name']) ?></div>
                        <div class="text-muted"><?= e($b['owner_email']) ?></div>
                    </td>
                    <td class="small">
                        <div><?= e(business_type_label((string) $b['business_type'])) ?></div>
                        <div class="text-muted"><?= e($b['business_category'] ?: '—') ?></div>
                    </td>
                    <td class="small">
                        <div><?= e($b['contact_number'] ?: ($b['owner_contact'] ?? '—')) ?></div>
                        <?php if (!empty($b['email'])): ?><div class="text-muted"><?= e($b['email']) ?></div><?php endif; ?>
                    </td>
                    <td class="small">
                        <?= e(trim(($b['address'] ?? '') . ($b['barangay'] ? ', ' . $b['barangay'] : '')) ?: '—') ?>
                    </td>
                    <td class="small text-nowrap"><?= e(format_datetime_short((string) ($b['created_at'] ?? '')) ?: (string) ($b['created_at'] ?? '')) ?></td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appDetailModal" data-app="<?= htmlspecialchars(json_encode([
                            'id' => (int) $b['id'],
                            'business_name' => $b['business_name'],
                            'owner_name' => $b['owner_name'],
                            'owner_email' => $b['owner_email'],
                            'business_type' => business_type_label((string) $b['business_type']),
                            'business_category' => $b['business_category'] ?? '',
                            'description' => $b['description'] ?? '',
                            'contact_number' => $b['contact_number'] ?? '',
                            'email' => $b['email'] ?? '',
                            'address' => $b['address'] ?? '',
                            'barangay' => $b['barangay'] ?? '',
                            'operating_hours' => $b['operating_hours'] ?? '',
                            'created_at' => $b['created_at'] ?? '',
                            'logo' => !empty($b['logo']) ? media_url($b['logo']) : '',
                            'cover' => !empty($b['cover_image']) ? media_url($b['cover_image']) : '',
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">View</button>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="approve">
                            <button class="btn btn-sm btn-success" type="submit" onclick="return confirm('Approve this business application?');">Approve</button>
                        </form>
                        <form method="post" class="d-inline-flex flex-wrap gap-1 justify-content-end mt-1" style="max-width:220px;margin-left:auto;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="business_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="admin_action" value="reject">
                            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Rejection reason" required style="min-width:120px;">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="appDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Application details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appDetailBody">
                <p class="text-muted mb-0">Select View on an application.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('appDetailModal')?.addEventListener('show.bs.modal', function (ev) {
    const btn = ev.relatedTarget;
    const body = document.getElementById('appDetailBody');
    if (!btn || !body) return;
    try {
        const d = JSON.parse(btn.getAttribute('data-app') || '{}');
        body.innerHTML = '<div class="row g-3">' +
            '<div class="col-md-8"><h4 class="h5 mb-1">' + (d.business_name || '') + '</h4>' +
            '<p class="text-muted small mb-2">Submitted: ' + (d.created_at || '—') + '</p>' +
            '<p>' + (d.description || '<em>No description</em>') + '</p></div>' +
            (d.logo ? '<div class="col-md-4 text-center"><img src="' + d.logo + '" class="img-fluid rounded" alt=""></div>' : '') +
            '<div class="col-md-6"><strong>Owner</strong><br>' + (d.owner_name || '') + '<br><span class="text-muted">' + (d.owner_email || '') + '</span></div>' +
            '<div class="col-md-6"><strong>Type</strong><br>' + (d.business_type || '') + '<br><span class="text-muted">' + (d.business_category || '—') + '</span></div>' +
            '<div class="col-md-6"><strong>Contact</strong><br>' + (d.contact_number || '—') + '</div>' +
            '<div class="col-md-6"><strong>Email</strong><br>' + (d.email || '—') + '</div>' +
            '<div class="col-12"><strong>Address</strong><br>' + (d.address || '—') + (d.barangay ? ', ' + d.barangay : '') + '</div>' +
            '<div class="col-md-6"><strong>Hours</strong><br>' + (d.operating_hours || '—') + '</div>' +
            (d.cover ? '<div class="col-12"><strong>Cover</strong><br><img src="' + d.cover + '" class="img-fluid rounded mt-1" alt=""></div>' : '') +
            '</div>';
    } catch (e) {
        body.innerHTML = '<p class="text-danger">Could not load details.</p>';
    }
});
</script>
<?php endif; ?>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
