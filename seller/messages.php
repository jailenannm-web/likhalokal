<?php

declare(strict_types=1);

$pageTitle = 'Seller messages';
$activeSeller = 'msg';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$stmt = db()->prepare("SELECT id FROM businesses WHERE user_id=? AND status='approved' LIMIT 1");
$stmt->execute([$uid]);
$b = $stmt->fetch();
$bid = $b ? (int) $b['id'] : 0;

$threads = [];
if ($bid) {
    $q = db()->prepare('SELECT sender_id, receiver_id FROM messages WHERE business_id = ?');
    $q->execute([$bid]);
    $seen = [];
    foreach ($q->fetchAll() as $row) {
        $o = ((int) $row['sender_id'] === $uid) ? (int) $row['receiver_id'] : (int) $row['sender_id'];
        if ($o !== $uid) {
            $seen[$o] = true;
        }
    }
    foreach (array_keys($seen) as $oid) {
        $threads[] = ['other' => $oid];
    }
}

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Messages</h1>
<?php if (!$bid): ?><p>No business.</p><?php else: ?>
<p><a class="btn btn-lk-orange" href="<?= e(BASE_URL) ?>message.php?business_id=<?= $bid ?>">Open chat workspace</a></p>
<ul class="list-group"><?php foreach ($threads as $t): ?><li class="list-group-item">Customer user #<?= (int) $t['other'] ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
