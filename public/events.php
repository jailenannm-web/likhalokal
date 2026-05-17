<?php

declare(strict_types=1);

$pageTitle = 'Events';
$activeNav = 'home';
$bodyClass = trim(($bodyClass ?? '') . ' events-page lk-internal-workspace');
$isDashboardLayout = true;
require_once dirname(__DIR__) . '/bootstrap.php';

$stmt = db()->prepare(
    "SELECT * FROM events
     WHERE status = 'published' AND event_date >= CURDATE()
     ORDER BY event_date ASC, event_time ASC
     LIMIT 50"
);
$stmt->execute();
$list = $stmt->fetchAll();

// Get current month for calendar
$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$monthName = date('F', mktime(0, 0, 0, $currentMonth, 1));

// Get events for current month
$monthEvents = db()->prepare(
    "SELECT * FROM events
     WHERE status = 'published' 
     AND YEAR(event_date) = ? 
     AND MONTH(event_date) = ?
     ORDER BY event_date ASC, event_time ASC"
);
$monthEvents->execute([$currentYear, $currentMonth]);
$monthEventsList = $monthEvents->fetchAll();

// Build array of event dates for highlighting
$eventDates = [];
foreach ($monthEventsList as $evt) {
    $eventDates[] = date('j', strtotime((string) $evt['event_date']));
}

require BASE_PATH . '/includes/header.php';
?>
<div class="vendor-profile-subnav">
    <div class="container">
        <a href="<?= e(BASE_URL) ?>index.php" aria-label="Go back"><i class="fa-solid fa-arrow-left fs-5"></i> Back</a>
        <span class="fw-bold text-uppercase small" style="letter-spacing: 1px; font-family: 'Montserrat', sans-serif;">EVENTS</span>
    </div>
</div>
<section class="lk-events-hero">
    <div class="container">
        <span class="lk-section-kicker">Vinzons Calendar</span>
        <h1>Upcoming Events</h1>
        <p>Festivals, cultural programs, community fairs, and local gatherings posted by the LikhaLokal admin team.</p>
    </div>
</section>

<main class="lk-events-page py-5">
    <div class="container">
        <!-- Calendar Section -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="lk-calendar-card">
                    <div class="lk-calendar-header">
                        <h3><?= e($monthName) ?> <?= e((string) $currentYear) ?></h3>
                    </div>
                    <div class="lk-calendar-grid">
                        <?php
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
                        $firstDayOfWeek = date('N', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
                        
                        // Day headers
                        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        foreach ($dayNames as $day): ?>
                            <div class="lk-calendar-day-header"><?= e($day) ?></div>
                        <?php endforeach;
                        
                        // Empty cells before first day
                        for ($i = 1; $i < $firstDayOfWeek; $i++): ?>
                            <div class="lk-calendar-day empty"></div>
                        <?php endfor;
                        
                        // Days of month
                        for ($day = 1; $day <= $daysInMonth; $day++):
                            $hasEvent = in_array($day, $eventDates);
                            $isToday = ($day === (int) date('j') && $currentMonth === (int) date('n'));
                            ?>
                            <div class="lk-calendar-day <?= $hasEvent ? 'has-event' : '' ?> <?= $isToday ? 'today' : '' ?>">
                                <?= e((string) $day) ?>
                                <?php if ($hasEvent): ?>
                                    <span class="event-dot"></span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="lk-calendar-events">
                    <h3>This Month's Events</h3>
                    <?php if (empty($monthEventsList)): ?>
                        <p class="text-muted">No events scheduled for this month.</p>
                    <?php else: ?>
                        <div class="lk-monthly-events-list">
                            <?php foreach ($monthEventsList as $evt): ?>
                                <?php
                                $ts = strtotime((string) $evt['event_date']);
                                $evtDay = $ts ? (string) date('j', $ts) : '';
                                $evtMonth = $ts ? date('M', $ts) : '';
                                $evtTime = !empty($evt['event_time']) ? date('g:i A', strtotime((string) $evt['event_time'])) : 'Time TBA';
                                ?>
                                <div class="lk-monthly-event-item">
                                    <div class="lk-monthly-event-date">
                                        <span><?= e($evtMonth) ?></span>
                                        <strong><?= e($evtDay) ?></strong>
                                    </div>
                                    <div class="lk-monthly-event-details">
                                        <h4><?= e($evt['title']) ?></h4>
                                        <p class="mb-0"><i class="bi bi-clock"></i> <?= e($evtTime) ?> <i class="bi bi-geo-alt-fill ms-2"></i> <?= e($evt['location'] ?: 'Location TBA') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- All Events Section -->
        <div class="text-center mb-5">
            <h2 class="lk-section-title">All Upcoming Events</h2>
        </div>

        <?php if (empty($list)): ?>
            <div class="lk-events-empty">
                <i class="bi bi-calendar-heart"></i>
                <h2>No published events yet</h2>
                <p>Check back soon for upcoming festivals and community activities in Vinzons.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($list as $event): ?>
                    <?php
                    $ts = strtotime((string) $event['event_date']);
                    $month = $ts ? strtoupper(date('M', $ts)) : '';
                    $day = $ts ? (string) date('d', $ts) : '';
                    $weekday = $ts ? date('l', $ts) : '';
                    $time = !empty($event['event_time']) ? date('g:i A', strtotime((string) $event['event_time'])) : 'Time TBA';
                    $img = media_url($event['image'] ?? null, asset_url('images/tacboanfes1.png'));
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="lk-event-card h-100">
                            <div class="lk-event-image">
                                <img src="<?= e($img) ?>" alt="<?= e($event['title']) ?>">
                                <div class="lk-event-date">
                                    <span><?= e($month) ?></span>
                                    <strong><?= e($day) ?></strong>
                                </div>
                                <span class="lk-event-status">Published</span>
                            </div>
                            <div class="lk-event-body">
                                <div class="lk-event-meta">
                                    <span><i class="bi bi-calendar-event"></i><?= e($weekday) ?></span>
                                    <span><i class="bi bi-clock"></i><?= e($time) ?></span>
                                </div>
                                <h2><?= e($event['title']) ?></h2>
                                <p><?= e(str_limit((string) $event['description'], 170)) ?></p>
                                <div class="lk-event-location">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span><?= e($event['location'] ?: 'Location to be announced') ?></span>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require BASE_PATH . '/includes/footer.php'; ?>
