<?php

declare(strict_types=1);

$pageTitle = 'My messages';
$activeUser = 'msg';
require_once __DIR__ . '/_init.php';

$uid = current_user_id();
$q = db()->prepare(
    'SELECT DISTINCT business_id FROM messages WHERE sender_id=? OR receiver_id=?'
);
$q->execute([$uid, $uid]);
$ids = $q->fetchAll(PDO::FETCH_COLUMN);

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Conversations</h1>
<ul class="list-group"><?php foreach ($ids as $bid): if (!$bid) {
    continue;
} $b = db()->prepare('SELECT business_name FROM businesses WHERE id=?');
    $b->execute([(int) $bid]);
    $name = $b->fetchColumn(); ?>
<li class="list-group-item d-flex justify-content-between align-items-center">
    <span><?= e((string) $name) ?></span>
    <a class="btn btn-sm btn-primary" href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $bid ?>">Open</a>
</li>
<?php endforeach; ?></ul>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
