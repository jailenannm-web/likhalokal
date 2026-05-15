<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'LikhaLokal';
$bodyClass = $bodyClass ?? '';
$extraHead = $extraHead ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Montserrat:ital,wght@0,400;0,600;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= e(ASSET_URL) ?>css/style.css?v=<?= time() ?>">
    <script>window.LIKHA_GOOGLE_KEY = <?= json_encode(GOOGLE_MAPS_API_KEY, JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
    <?= $extraHead ?>
</head>
<body class="<?= e($bodyClass) ?>" data-logged-in="<?= is_logged_in() ? '1' : '0' ?>">
