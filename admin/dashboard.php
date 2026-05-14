<?php

declare(strict_types=1);

$pageTitle = 'Admin Dashboard';
$activeAdmin = 'dash';
require_once __DIR__ . '/_init.php';

$counts = [
    'users' => (int) db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'],
    'sellers' => (int) db()->query("SELECT COUNT(*) c FROM users WHERE role='seller'")->fetch()['c'],
    'businesses_approved' => (int) db()->query("SELECT COUNT(*) c FROM businesses WHERE status='approved'")->fetch()['c'],
    'businesses_pending' => (int) db()->query("SELECT COUNT(*) c FROM businesses WHERE status='pending'")->fetch()['c'],
    'products' => (int) db()->query('SELECT COUNT(*) c FROM products')->fetch()['c'],
    'attractions' => (int) db()->query('SELECT COUNT(*) c FROM tourist_attractions')->fetch()['c'],
    'events' => (int) db()->query('SELECT COUNT(*) c FROM events')->fetch()['c'],
    'reviews' => (int) db()->query('SELECT COUNT(*) c FROM reviews')->fetch()['c'],
    'messages' => (int) db()->query('SELECT COUNT(*) c FROM messages')->fetch()['c'],
];

require BASE_PATH . '/includes/header.php';
require __DIR__ . '/partials/layout-start.php';
?>
<h1 class="h4 mb-4">Dashboard</h1>
<div class="row g-3 mb-4">
    <?php foreach ($counts as $k => $v): ?>
        <div class="col-md-4 col-lg-3">
            <div class="card card-lk border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted text-uppercase small"><?= e(str_replace('_', ' ', $k)) ?></div>
                    <div class="display-6 fw-bold"><?= (int) $v ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<canvas id="chartCounts" height="120"></canvas>
<?php
$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script><script>
const ctx = document.getElementById("chartCounts");
const labels = ' . json_encode(array_keys($counts)) . ';
const data = ' . json_encode(array_values($counts)) . ';
new Chart(ctx, { type: "bar", data: { labels, datasets: [{ label: "Counts", data, backgroundColor: "#ff8c00" }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
</script>';
require __DIR__ . '/partials/layout-end.php';
require BASE_PATH . '/includes/footer.php';
