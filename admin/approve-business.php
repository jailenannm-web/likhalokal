<?php

declare(strict_types=1);

$pageTitle = 'Approve business';
require_once __DIR__ . '/_init.php';
$id = (int) ($_GET['id'] ?? 0);
header('Location: ' . ADMIN_URL . 'businesses.php?tab=pending');
exit;
