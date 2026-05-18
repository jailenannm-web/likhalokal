<?php

declare(strict_types=1);

$pageTitle = 'Users';
$activeAdmin = 'usr';
require_once __DIR__ . '/_init.php';
require_once BASE_PATH . '/middleware/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? null)) {
    $id = (int) ($_POST['user_id'] ?? 0);
    $st = $_POST['user_status'] ?? '';
    if ($id && $id !== current_user_id() && in_array($st, ['active', 'suspended'], true)) {
        db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$st, $id]);
        set_flash('success', $st === 'active' ? 'User reactivated.' : 'User suspended.');
    }
    redirect(ADMIN_URL . 'users.php');
}

$hasLastSeen = db_column_exists('users', 'last_seen_at');
$sql = $hasLastSeen
    ? 'SELECT id, full_name, email, role, status, last_seen_at FROM users ORDER BY id ASC'
    : 'SELECT id, full_name, email, role, status, NULL AS last_seen_at FROM users ORDER BY id ASC';
$list = db()->query($sql)->fetchAll();

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-dash-inner-head">
    <h1 class="lk-dash-page-title mb-1">Users</h1>
    <p class="lk-dash-page-lead text-muted mb-0">Manage local users, sellers, and account status. Activity status updates when users browse the site (refresh to see latest).</p>
</div>
<div class="lk-panel">
    <div class="lk-dash-table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Account</th>
                    <th>Activity</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $u):
                $accountStatus = (string) ($u['status'] ?? 'active');
                $activityLabel = user_activity_status($u['last_seen_at'] ?? null);
                $accountBadge = $accountStatus === 'active' ? 'success' : ($accountStatus === 'suspended' ? 'danger' : 'secondary');
                $activityBadge = $activityLabel === 'Active now' ? 'success' : (str_starts_with($activityLabel, 'Last seen') ? 'warning' : 'secondary');
                ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= e($u['full_name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['role']) ?></td>
                <td>
                    <span class="badge bg-<?= $accountBadge ?>"><?= e(ucfirst($accountStatus)) ?></span>
                </td>
                <td>
                    <span class="badge bg-<?= $activityBadge ?>">
                        <?php if ($activityLabel === 'Active now'): ?>
                            <span class="d-inline-block rounded-circle bg-success me-1" style="width:8px;height:8px;"></span>
                        <?php endif; ?>
                        <?= e($activityLabel) ?>
                    </span>
                </td>
                <td>
                <?php if ((int) $u['id'] !== current_user_id()): ?>
                <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                <?php if ($accountStatus === 'active'): ?>
                <input type="hidden" name="user_status" value="suspended"><button class="btn btn-sm btn-outline-danger" type="submit">Suspend</button>
                <?php else: ?>
                <input type="hidden" name="user_status" value="active"><button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                <?php endif; ?>
                </form>
                <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
