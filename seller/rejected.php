<?php

declare(strict_types=1);

$pageTitle = 'Business application rejected';
$activeSeller = '';
require_once __DIR__ . '/_init.php';

$business = seller_business_for_user();
if (!$business) {
    redirect(BASE_URL . 'register-business.php');
}
if ($business['status'] === 'approved') {
    redirect(SELLER_URL . 'dashboard.php');
}
if ($business['status'] === 'pending') {
    redirect(SELLER_URL . 'pending.php');
}

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-panel">
    <div class="lk-panel-body text-center py-5">
        <i class="bi bi-exclamation-triangle display-5 text-danger d-block mb-3"></i>
        <h1 class="h4 fw-bold mb-2">Your business application needs changes</h1>
        <p class="text-muted mx-auto mb-4" style="max-width: 680px;">
            The admin reviewed your application and requested updates. You can edit your business information and resubmit it for another review.
        </p>
        <div class="alert alert-danger mx-auto text-start" style="max-width: 680px;">
            <strong>Rejection reason:</strong><br>
            <?= e((string) ($business['rejection_reason'] ?: 'No reason was provided. Please review your business details and resubmit.')) ?>
        </div>
        <div class="border rounded-3 p-3 bg-light mx-auto text-start mb-4" style="max-width: 640px;">
            <div class="small text-muted">Business</div>
            <div class="fw-bold"><?= e((string) $business['business_name']) ?></div>
            <span class="badge bg-danger text-uppercase mt-2"><?= e((string) $business['status']) ?></span>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= e(SELLER_URL) ?>business-profile.php" class="btn btn-lk-orange">Edit and resubmit</a>
            <a href="<?= e(BASE_URL) ?>index.php" class="btn btn-outline-secondary">Back to website</a>
            <a href="<?= e(BASE_URL) ?>logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</div>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
