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

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Recent messages</h1>
<table class="table table-sm"><thead><tr><th>When</th><th>Business</th><th>From</th><th>To</th><th>Message</th></tr></thead><tbody>
<?php foreach ($list as $m): ?>
<tr>
<td class="small"><?= e($m['created_at']) ?></td>
<td><?= e($m['business_name'] ?? '') ?></td>
<td><?= e($m['sender']) ?></td>
<td><?= e($m['receiver']) ?></td>
<td><?= e(str_limit((string) $m['message_content'], 60)) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
