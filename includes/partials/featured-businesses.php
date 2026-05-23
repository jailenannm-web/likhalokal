<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $featured */
$placeholder = asset_url('images/fruitstand.png');
?>
<div class="featured-grid stagger-children">
    <?php if (empty($featured)): ?>
        <p class="text-center text-muted w-100 py-4">No featured businesses yet. Import <code>database/restore_static_content.sql</code> in phpMyAdmin.</p>
    <?php else: ?>
        <?php foreach ($featured as $biz): ?>
            <?php
            $coverImg = media_url($biz['cover_image'] ?: $biz['logo'], $placeholder);
            $returnTo = current_request_return_url();
            $profileUrl = vendor_profile_url((int) $biz['id'], $returnTo);
            $msgUrl = BASE_URL . 'message.php?business_id=' . (int) $biz['id'] . '&return=' . rawurlencode($returnTo);
            ?>
            <div class="business-card reveal reveal-scale">
                <div class="biz-image-area">
                    <img src="<?= e($coverImg) ?>" alt="<?= e($biz['business_name']) ?>">
                </div>
                <div class="biz-info-bar">
                    <div class="biz-details">
                        <h4><a href="<?= e($profileUrl) ?>"><?= e($biz['business_name']) ?></a></h4>
                        <p><?= e(str_limit((string) ($biz['description'] ?? ''), 120)) ?></p>
                    </div>
                    <div class="biz-action-row">
                        <a href="<?= e($profileUrl) ?>" class="visit-btn">Visit Shop <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= e($msgUrl) ?>" class="mail-btn" title="Message"><i class="fa-regular fa-envelope"></i></a>
                        <?php else: ?>
                            <button type="button" class="mail-btn border-0 bg-transparent" data-require-auth title="Message"><i class="fa-regular fa-envelope"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
