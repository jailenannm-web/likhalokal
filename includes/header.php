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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(ASSET_URL) ?>css/style.css">
    <script>window.LIKHA_GOOGLE_KEY = <?= json_encode(GOOGLE_MAPS_API_KEY, JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
    <?= $extraHead ?>
</head>
<body class="<?= e($bodyClass) ?>" data-logged-in="<?= is_logged_in() ? '1' : '0' ?>">
