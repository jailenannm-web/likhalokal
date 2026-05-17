<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $allApproved */
/** @var array<int, array{title: string, icon: string, types: array<int, string>, reveal: string}> $listingColumns */
?>
<section class="listings-wrapper">
    <div class="listings-grid">
        <?php foreach ($listingColumns as $col): ?>
            <?php
            $items = array_values(array_filter(
                $allApproved,
                static fn(array $b): bool => in_array($b['business_type'], $col['types'], true)
            ));
            ?>
            <div class="listing-col">
                <h3 class="col-header reveal"><?= e($col['title']) ?></h3>
                <?php if (empty($items)): ?>
                    <p class="small text-muted px-2 mb-0">No listings in this category yet.</p>
                <?php else: ?>
                    <?php foreach ($items as $itemIdx => $biz): ?>
                        <div class="list-item reveal <?= e($col['reveal']) ?>"<?= $itemIdx > 0 ? ' style="transition-delay:' . ($itemIdx * 0.1) . 's;"' : '' ?>>
                            <div class="list-icon"><i class="bi <?= e($col['icon']) ?>"></i></div>
                            <div class="list-info">
                                <h5><?= e($biz['business_name']) ?></h5>
                                <p><?= e($biz['address'] ?? $biz['barangay'] ?? 'Vinzons') ?></p>
                                <p><?= e($biz['contact_number'] ?? 'Contact not provided') ?></p>
                                <?php if (is_logged_in()): ?>
                                    <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $biz['id'] ?>&return=<?= rawurlencode(current_request_return_url()) ?>" class="contact-btn">Message now</a>
                                <?php else: ?>
                                    <button type="button" class="contact-btn border-0" data-require-auth>Message now</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
