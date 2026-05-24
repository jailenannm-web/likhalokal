<?php

declare(strict_types=1);

$pageTitle = 'Attraction';
$activeNav = 'tourism';
require_once dirname(__DIR__) . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM tourist_attractions WHERE id = ? AND status = 'published' LIMIT 1");
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) {
    http_response_code(404);
    $pageTitle = 'Attraction Not Found';
    $bodyClass = trim(($bodyClass ?? '') . ' attraction-detail-page lk-internal-workspace');
    $isDashboardLayout = true;
    require BASE_PATH . '/includes/header.php';
    ?>
    <div class="vendor-profile-subnav">
        <div class="container">
            <a href="<?= e(BASE_URL) ?>tourism.php" aria-label="Go back"><i class="fa-solid fa-arrow-left fs-5"></i> Back</a>
            <span class="fw-bold text-uppercase small" style="letter-spacing: 1px; font-family: 'Montserrat', sans-serif;">Attraction Detail</span>
        </div>
    </div>
    <div class="container py-5">
        <div class="alert alert-light border shadow-sm text-center rounded-4 p-5">
            <h1 class="h4 fw-bold text-dark mb-2">Attraction not found</h1>
            <p class="text-muted mb-4">This attraction may be unpublished or no longer available.</p>
            <a href="<?= e(BASE_URL) ?>tourism.php" class="btn btn-lk-orange">Back to tourism</a>
        </div>
    </div>
    <?php
    require BASE_PATH . '/includes/footer.php';
    exit;
}

$rstmt = db()->prepare(
    "SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.attraction_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20"
);
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll();

require_once BASE_PATH . '/middleware/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    require_once BASE_PATH . '/middleware/auth.php';
    require_login();
    if (current_user_role() !== 'local_user') {
        set_flash('error', 'Only local users may submit reviews.');
        redirect(BASE_URL . 'attraction-detail.php?id=' . $id);
    }
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Invalid token.');
        redirect(BASE_URL . 'attraction-detail.php?id=' . $id);
    }
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    if ($rating >= 1 && $rating <= 5) {
        $ins = db()->prepare(
            'INSERT INTO reviews (user_id, business_id, attraction_id, rating, comment, status, created_at, updated_at) VALUES (?,NULL,?,?,?,\'pending\',NOW(),NOW())'
        );
        $ins->execute([current_user_id(), $id, $rating, $comment]);
        set_flash('success', 'Review submitted for moderation.');
    }
    redirect(BASE_URL . 'attraction-detail.php?id=' . $id);
}

$pageTitle = $a['attraction_name'];
$bodyClass = trim(($bodyClass ?? '') . ' attraction-detail-page lk-internal-workspace');
$isDashboardLayout = true;
require BASE_PATH . '/includes/header.php';

$hasCoords = isset($a['latitude'], $a['longitude'])
    && $a['latitude'] !== null
    && $a['longitude'] !== null
    && $a['latitude'] !== ''
    && $a['longitude'] !== ''
    && is_numeric($a['latitude'])
    && is_numeric($a['longitude']);
$display = static fn($value, string $placeholder = 'Not provided'): string => trim((string) $value) !== '' ? trim((string) $value) : $placeholder;
$googleMapsUrl = $hasCoords
    ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($a['latitude'] . ',' . $a['longitude'])
    : '';
?>

<!-- Google Fonts & Animate.css -->
<link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Bungee&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --vinzons-blue: #0077C2;
        --vinzons-dark-blue: #004A7C;
        --vinzons-amber: #FFBF00;
        --vinzons-white: #FFFFFF;
        --vinzons-black: #000000;
        --light-sky: #B3E5FC;
        --card-radius: 40px;
    }

    body {
        background: radial-gradient(circle, #E3F2FD 0%, var(--light-sky) 100%);
        background-attachment: fixed;
        color: var(--vinzons-black);
    }

    .page-main-container {
        padding-top: 2rem !important;
    }

    /* Consistent Card Logic & Aesthetics */
    .unified-card {
        border-radius: var(--card-radius);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.35);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
    }

    .glass-blue {
        background: rgba(0, 119, 194, 0.88);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        color: var(--vinzons-white);
    }

    /* Typography Overrides */
    .attraction-title {
        font-family: 'Abril Fatface', cursive;
        color: var(--vinzons-dark-blue);
    }

    .section-heading {
        font-family: 'Bungee', cursive;
        color: var(--vinzons-blue);
        font-size: 1.1rem;
        letter-spacing: 1px;
    }

    .badge-pill-custom {
        background: rgba(0, 119, 194, 0.12);
        border: 1.5px solid var(--vinzons-blue);
        color: var(--vinzons-blue);
        font-family: 'Bungee', cursive;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.75rem;
        display: inline-block;
    }

    /* Form and Review Aesthetics */
    .gov-header-amber {
        background-color: var(--vinzons-amber);
        color: var(--vinzons-black);
        padding: 15px 30px;
        font-family: 'Bungee', cursive;
        font-size: 1rem;
    }

    .gov-content {
        background-color: var(--vinzons-dark-blue);
        padding: 30px;
        color: var(--vinzons-white);
    }

    .custom-input {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: var(--vinzons-white) !important;
        border-radius: 12px;
    }

    .custom-input:focus {
        background: rgba(255, 255, 255, 0.3);
        border-color: var(--vinzons-amber);
        box-shadow: none;
    }

    .custom-input option {
        color: var(--vinzons-black);
    }

    .btn-amber {
        background-color: var(--vinzons-amber);
        border-color: var(--vinzons-amber);
        color: var(--vinzons-black);
        font-family: 'Bungee', cursive;
        border-radius: 50px;
        padding: 10px 25px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .btn-amber:hover {
        background-color: #e5ab00;
        border-color: #e5ab00;
        transform: translateY(-2px);
    }

    .btn-outline-custom {
        border: 2px solid var(--vinzons-blue);
        color: var(--vinzons-blue);
        font-family: 'Bungee', cursive;
        border-radius: 50px;
        padding: 8px 20px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-outline-custom:hover {
        background-color: var(--vinzons-blue);
        color: var(--vinzons-white);
    }

    /* Scroll Animation Utilities */
    .reveal-on-scroll {
        opacity: 0;
        transition: all 0.8s ease;
    }

    .reveal-on-scroll.animated {
        opacity: 1;
    }
</style>

<div class="vendor-profile-subnav">
    <div class="container">
        <a href="<?= e(BASE_URL) ?>tourism.php" aria-label="Go back"><i class="fa-solid fa-arrow-left fs-5"></i> Back</a>
        <span class="fw-bold text-uppercase small" style="letter-spacing: 1px; font-family: 'Montserrat', sans-serif;">Attraction Detail</span>
    </div>
</div>

<div class="container page-main-container py-4">
    <?php if ($m = flash('success')): ?><div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeIn" style="border-radius: 15px;"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('error')): ?><div class="alert alert-danger border-0 shadow-sm animate__animated animate__fadeIn" style="border-radius: 15px;"><?= e($m) ?></div><?php endif; ?>
    
    <div class="row g-5">
        <div class="col-lg-6 animate__animated animate__fadeInLeft">
            <?php $im = media_url($a['image'] ?? null, asset_url('images/placeholder.png')); ?>
            <div class="unified-card shadow-lg border border-5 border-white h-100">
                <img src="<?= e($im) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="<?= e($a['attraction_name']) ?>">
            </div>
        </div>
        
        <div class="col-lg-6 d-flex flex-column justify-content-between animate__animated animate__fadeInRight">
            <div>
                <span class="badge-pill-custom mb-3"><?= e(ucwords(str_replace('_', ' ', (string) $a['category']))) ?></span>
                <h1 class="display-5 fw-bold attraction-title mb-4"><?= e($a['attraction_name']) ?></h1>
                
                <!-- Organized structured details -->
                <div class="mb-4">
                    <h2 class="section-heading mb-2">Overview</h2>
                    <p class="lead mb-0" style="line-height: 1.8; opacity: 0.85; font-size: 1.15rem;"><?= nl2br(e($display($a['description'] ?? ''))) ?></p>
                </div>
                
                <div class="mb-4">
                    <h2 class="section-heading mb-2">History & Background</h2>
                    <p class="small text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem; text-align: justify;"><?= nl2br(e($display($a['history'] ?? ''))) ?></p>
                </div>
                
                <div class="mb-4">
                    <h2 class="section-heading mb-2">Travel Guide & Quick Tips</h2>
                    <p class="small mb-0" style="line-height: 1.7; opacity: 0.85; font-size: 0.95rem;"><?= nl2br(e($display($a['travel_guide'] ?? ''))) ?></p>
                </div>
                
                <div class="row g-3 mb-4 py-3 px-2 rounded-3 bg-white bg-opacity-20 backdrop-blur" style="border: 1px solid rgba(255,255,255,0.4);">
                    <div class="col-sm-4">
                        <p class="mb-0 small text-uppercase font-monospace text-muted fw-bold" style="letter-spacing: 0.5px;">Entrance Fee</p>
                        <p class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;"><?= e($display($a['entrance_fee'] ?? '')) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p class="mb-0 small text-uppercase font-monospace text-muted fw-bold" style="letter-spacing: 0.5px;">Best Time to Visit</p>
                        <p class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;"><?= e($display($a['best_time_to_visit'] ?? '')) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p class="mb-0 small text-uppercase font-monospace text-muted fw-bold" style="letter-spacing: 0.5px;">Location</p>
                        <p class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;"><?= e($display($a['address'] ?? '', 'Location unavailable')) ?></p>
                    </div>
                </div>
            </div>
            
            <div>
                <?php if ($hasCoords): ?>
                    <a class="btn btn-outline-custom w-100 mb-3 text-center d-block" target="_blank" rel="noopener" href="<?= e($googleMapsUrl) ?>">View on Google Maps</a>
                    <div id="attMap" class="unified-card shadow-sm lk-map-box" style="background:#e9ecef;"></div>
                <?php else: ?>
                    <div class="unified-card shadow-sm vendor-map-placeholder lk-map-box" style="background:#e9ecef;">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <strong>Location map is not yet available.</strong>
                        <span>Latitude and longitude have not been provided yet.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-5">
        <div class="col-lg-6 reveal-on-scroll" data-animation="animate__fadeInLeft">
            <h2 class="section-heading mb-4 text-uppercase">Reviews & Feedback</h2>
            <div style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
                <?php if (empty($reviews)): ?>
                    <div class="unified-card glass-effect p-4 text-center text-muted">No reviews approved yet.</div>
                <?php endif; ?>
                <?php foreach ($reviews as $r): ?>
                    <div class="unified-card glass-effect p-4 mb-3 shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 mb-0 fw-bold text-dark" style="font-family: 'Bungee', sans-serif; font-size:0.85rem; color: var(--vinzons-dark-blue) !important;"><?= e($r['reviewer_name']) ?></h3>
                            <span class="fw-bold" style="color: var(--vinzons-amber); font-size:1.1rem;"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></span>
                        </div>
                        <div class="small opacity-75 mt-2" style="line-height: 1.6; text-align: justify;"><?= e($r['comment'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-6 reveal-on-scroll" data-animation="animate__fadeInRight">
            <?php if (is_logged_in() && current_user_role() === 'local_user'): ?>
                <div class="unified-card shadow-lg" style="border:none;">
                    <div class="gov-header-amber text-uppercase">Share Your Experience</div>
                    <div class="gov-content">
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="review_submit" value="1">
                            <div class="mb-3">
                                <label class="form-label small text-uppercase tracking-wider font-monospace" style="color: var(--vinzons-amber);">Your Rating</label>
                                <select name="rating" class="form-select custom-input" required>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?> Stars</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small text-uppercase tracking-wider font-monospace" style="color: var(--vinzons-amber);">Your Review</label>
                                <textarea name="comment" class="form-control custom-input" rows="4" placeholder="Tell others about your visit..." required></textarea>
                            </div>
                            <button class="btn btn-amber w-100 text-uppercase" type="submit">Submit Review</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
if ($hasCoords) {
$extraScripts = '<script src="' . e(ASSET_URL) . 'js/maps.js"></script><script>
document.addEventListener("DOMContentLoaded", function () {
  if (typeof likhaInitMap === "function") {
    likhaInitMap(document.getElementById("attMap"), ' . json_encode($a['latitude']) . ', ' . json_encode($a['longitude']) . ', ' . json_encode($a['attraction_name']) . ', ' . json_encode($a['address'] ?? '') . ');
  } else if (typeof google !== "undefined" && google.maps) {
    const lat = parseFloat(' . json_encode($a['latitude']) . ');
    const lng = parseFloat(' . json_encode($a['longitude']) . ');
    if (!isNaN(lat) && !isNaN(lng)) {
      const coords = { lat: lat, lng: lng };
      const map = new google.maps.Map(document.getElementById("attMap"), {
        zoom: 15,
        center: coords,
        disableDefaultUI: true,
        zoomControl: true
      });
      new google.maps.Marker({
        position: coords,
        map: map,
        title: ' . json_encode($a['attraction_name']) . '
      });
    }
  } else {
    const lat = ' . json_encode($a['latitude']) . ';
    const lng = ' . json_encode($a['longitude']) . ';
    if (lat && lng) {
      document.getElementById("attMap").innerHTML = `<iframe width="100%" height="100%" style="border:0;" loading="lazy" allowfullscreen src="https://maps.google.com/maps?q=\${lat},\${lng}&z=15&output=embed"></iframe>`;
    } else {
      const addr = encodeURIComponent(' . json_encode($a['address'] ?: $a['attraction_name']) . ');
      document.getElementById("attMap").innerHTML = `<iframe width="100%" height="100%" style="border:0;" loading="lazy" allowfullscreen src="https://maps.google.com/maps?q=\${addr}&z=15&output=embed"></iframe>`;
    }
  }
  
  const observerOptions = {
      root: null,
      rootMargin: "0px",
      threshold: 0.10
  };

  const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
          if (entry.isIntersecting) {
              const element = entry.target;
              const animationClass = element.getAttribute("data-animation");
              element.classList.add("animate__animated", animationClass, "animated");
              observer.unobserve(element);
          }
      });
  }, observerOptions);

  document.querySelectorAll(".reveal-on-scroll").forEach(el => {
      observer.observe(el);
  });
});
</script>';
}
require BASE_PATH . '/includes/footer.php';
?>
