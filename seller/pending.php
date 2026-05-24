<?php

declare(strict_types=1);

$pageTitle = 'Business under review';
$activeSeller = '';
require_once __DIR__ . '/_init.php';

$business = seller_business_for_user();
if (!$business) {
    redirect(BASE_URL . 'register-business.php');
}
if ($business['status'] === 'approved') {
    redirect(SELLER_URL . 'dashboard.php');
}
if ($business['status'] === 'rejected') {
    redirect(SELLER_URL . 'rejected.php');
}

require __DIR__ . '/partials/layout-start.php';
require BASE_PATH . '/includes/partials/dash-flash.php';
?>
<div class="lk-panel">
    <div class="lk-panel-body text-center py-5">
        <i class="bi bi-hourglass-split display-5 text-warning d-block mb-3"></i>
        <h1 class="h4 fw-bold mb-2">Your business registration is under review</h1>
        <p class="text-muted mx-auto mb-4" style="max-width: 680px;">
            Please wait for admin approval before accessing seller features. We will unlock your seller dashboard,
            products, messages, reviews, and promotions after your business is approved.
        </p>
        <div class="border rounded-3 p-3 bg-light mx-auto text-start mb-4" style="max-width: 640px;">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div>
                    <div class="small text-muted">Business</div>
                    <div class="fw-bold"><?= e((string) $business['business_name']) ?></div>
                </div>
                <div>
                    <div class="small text-muted">Status</div>
                    <span class="badge bg-warning text-dark text-uppercase"><?= e((string) $business['status']) ?></span>
                </div>
                <div>
                    <div class="small text-muted">Submitted</div>
                    <div><?= e(format_datetime_short((string) ($business['created_at'] ?? ''))) ?></div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= e(SELLER_URL) ?>business-profile.php" class="btn btn-lk-orange">Edit application</a>
            <a href="<?= e(BASE_URL) ?>index.php" class="btn btn-outline-secondary">Back to website</a>
            <a href="<?= e(BASE_URL) ?>logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</div>
<?php
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
