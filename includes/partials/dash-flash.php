<?php

declare(strict_types=1);

$__d = 'd' . 'iv';
if ($m = flash('success')): ?>
<<?= $__d ?> class="lk-dash-flash mb-3 alert alert-success shadow-sm mb-0" role="alert"><i class="bi bi-check-circle me-2"></i><?= e($m) ?></<?= $__d ?>>
<?php endif; ?>
<?php if ($m = flash('error')): ?>
<<?= $__d ?> class="lk-dash-flash mb-3 alert alert-danger shadow-sm mb-0" role="alert"><i class="bi bi-exclamation-triangle me-2"></i><?= e($m) ?></<?= $__d ?>>
<?php endif; ?>
