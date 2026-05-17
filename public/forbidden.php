<?php

declare(strict_types=1);

$pageTitle = 'Access denied';
$activeNav = '';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once BASE_PATH . '/middleware/auth.php';

http_response_code(403);
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card card-lk shadow border-0 p-5">
                <i class="fa-solid fa-shield-halved text-warning display-1 mb-3"></i>
                <h1 class="h3 mb-3">403 — Access denied</h1>
                <p class="text-muted mb-4">You do not have permission to view this page.</p>
                <?php if ($m = flash('error')): ?>
                    <div class="alert alert-warning"><?= e($m) ?></div>
                <?php endif; ?>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?= e(BASE_URL) ?>index.php" class="btn btn-lk-orange">Go home</a>
                    <?php if (is_logged_in()): ?>
                        <a href="<?= e(public_home_url()) ?>" class="btn btn-outline-secondary">Back to website</a>
                        <?php if (is_logged_in()): ?>
                        <a href="<?= e(dashboard_url_for_role(current_user_role())) ?>" class="btn btn-outline-primary">Open dashboard</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= e(BASE_URL) ?>login.php" class="btn btn-outline-secondary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
