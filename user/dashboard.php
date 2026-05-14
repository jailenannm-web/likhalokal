<?php

declare(strict_types=1);

$pageTitle = 'My dashboard';
$activeUser = 'dash';
require_once __DIR__ . '/_init.php';

$unread = unread_messages_count(current_user_id());

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-3">Dashboard</h1>
<p>Unread messages: <strong><?= (int) $unread ?></strong></p>
<p class="small text-muted">Browse <a href="<?= e(BASE_URL) ?>products.php">products</a> and message sellers when you need more details.</p>
<?php require __DIR__ . '/partials/layout-end.php'; require BASE_PATH . '/includes/footer.php';
