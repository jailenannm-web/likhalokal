<?php

declare(strict_types=1);

$pageTitle = 'Messages';
$activeAdmin = 'msg';
require_once __DIR__ . '/_init.php';

$list = db()->query(
    'SELECT m.*, us.full_name AS sender, ur.full_name AS receiver, b.business_name FROM messages m
     JOIN users us ON us.id = m.sender_id JOIN users ur ON ur.id = m.receiver_id
     LEFT JOIN businesses b ON b.id = m.business_id ORDER BY m.created_at DESC LIMIT 100'
)->fetchAll();

require __DIR__ . '/partials/layout-start.php';
?>
<div class="lk-dash-inner-head"><h1 class="lk-dash-page-title mb-1">Recent messages</h1><p class="lk-dash-page-lead text-muted mb-0">Latest inquiries between users and sellers across the platform.</p></div>
<div class="lk-panel"><div class="lk-dash-table-wrap"><table class="table table-hover align-middle mb-0"><thead><tr><th>When</th><th>Business</th><th>From</th><th>To</th><th>Message</th></tr></thead><tbody>
<?php foreach ($list as $m): ?>
<tr>
<td class="small"><?= e($m['created_at']) ?></td>
<td><?= e($m['business_name'] ?? '') ?></td>
<td><?= e($m['sender']) ?></td>
<td><?= e($m['receiver']) ?></td>
<td><?= e(str_limit((string) $m['message_content'], 60)) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-muted py-4">No messages yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
