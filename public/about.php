<?php

declare(strict_types=1);

$pageTitle = 'About';
$activeNav = 'about';
require_once dirname(__DIR__) . '/bootstrap.php';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<div class="container py-5 col-lg-8">
    <h1 class="h3 mb-3">About LikhaLokal</h1>
    <p class="lead">Vinzons LikhaLokal: <em>Tuklas, Kultura, Kabuhayan</em> is a community platform for discovering tourism sites, supporting local products, and connecting with entrepreneurs in Vinzons, Camarines Norte.</p>
    <p>This student-led initiative highlights heritage, festivals, and livelihoods while giving MSMEs a simple digital presence.</p>
    <h2 id="team" class="h5 mt-4">Our Team</h2>
    <p class="small text-muted">Talisay-Vinzons Team, BSIT 2B — AY 25-26.</p>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
