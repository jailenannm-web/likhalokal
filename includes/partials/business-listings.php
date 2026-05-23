<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $allApproved */
/** @var array<int, array{title: string, icon: string, types: array<int, string>, reveal: string}> $listingColumns */
?>
<div class="listings-grid">
    <?php foreach ($listingColumns as $col): ?>
        <?php
        $items = array_values(array_filter(
            $allApproved,
            static fn(array $b): bool => in_array($b['business_type'], $col['types'], true)
        ));
        $items = array_slice($items, 0, 4);
        ?>
        <div class="listing-col">
            <h3 class="col-header reveal <?= e($col['reveal']) ?>"><i class="<?= e($col['icon']) ?> me-2" style="color: var(--lk-amber);"></i><?= e($col['title']) ?></h3>
            <?php if (empty($items)): ?>
                <p class="small text-muted px-2 mb-0">No listings in this category yet.</p>
            <?php else: ?>
                <?php foreach ($items as $itemIdx => $biz): ?>
                    <?php
                    $profileUrl = vendor_profile_url((int) $biz['id'], current_request_return_url());
                    $msgUrl = BASE_URL . 'message.php?business_id=' . (int) $biz['id'] . '&return=' . rawurlencode(current_request_return_url());
                    ?>
                    <div class="list-item reveal <?= e($col['reveal']) ?>"<?= $itemIdx > 0 ? ' style="transition-delay:' . ($itemIdx * 0.1) . 's;"' : '' ?>>
                        <div class="list-icon"><i class="<?= e($col['icon']) ?>"></i></div>
                        <div class="list-info">
                            <h5><a href="<?= e($profileUrl) ?>" class="text-decoration-none text-dark hover-orange" style="transition: color 0.2s;" onmouseover="this.style.color='var(--lk-amber)'" onmouseout="this.style.color='var(--lk-navy)'"><?= e($biz['business_name']) ?></a></h5>
                            <p><i class="fa-solid fa-location-dot me-1.5 text-muted" style="font-size:0.75rem;"></i><?= e($biz['address'] ?? $biz['barangay'] ?? 'Vinzons, Camarines Norte') ?></p>
                            <p><i class="fa-solid fa-phone me-1.5 text-muted" style="font-size:0.75rem;"></i><?= e($biz['contact_number'] ?? 'Contact not provided') ?></p>
                            <?php if (is_logged_in()): ?>
                                <a href="<?= e($msgUrl) ?>" class="contact-btn">Message now <i class="fa-solid fa-arrow-right"></i></a>
                            <?php else: ?>
                                <button type="button" class="contact-btn border-0 bg-transparent" data-require-auth>Message now <i class="fa-solid fa-arrow-right"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
