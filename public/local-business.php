<?php
declare(strict_types=1);

$pageTitle = 'Local Business Directory';
$activeNav = 'business';
require_once dirname(__DIR__) . '/bootstrap.php';

// Fetch featured businesses
$featured = db()->query(
    "SELECT * FROM businesses WHERE status='approved' AND id IN (10,11,12,13) ORDER BY FIELD(id,10,11,12,13)"
)->fetchAll();
if (count($featured) < 4) {
    $need = 4 - count($featured);
    $stmt = db()->prepare(
        "SELECT * FROM businesses WHERE status='approved' AND id NOT IN (10,11,12,13) ORDER BY id ASC LIMIT " . (int) $need
    );
    $stmt->execute();
    $featured = array_merge($featured, $stmt->fetchAll());
}

$allApproved = db()->query(
    "SELECT * FROM businesses WHERE status='approved' ORDER BY business_type, business_name"
)->fetchAll();

$listingColumns = [
    ['title' => 'Featured Restaurants', 'icon' => 'fa-solid fa-utensils', 'types' => ['restaurant', 'food_vendor'], 'reveal' => 'reveal-left'],
    ['title' => 'Resorts & Stays', 'icon' => 'fa-solid fa-hotel', 'types' => ['resort'], 'reveal' => ''],
    ['title' => 'Local Services', 'icon' => 'fa-solid fa-screwdriver-wrench', 'types' => ['travel_agency', 'recreation', 'service', 'craft_business'], 'reveal' => 'reveal-right'],
];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<style>
/* Custom style variables for modern premium aesthetic */
:root {
    --lk-navy: #001F3F;
    --lk-navy-dark: #00152b;
    --lk-amber: #f39200;
    --lk-gold: #ffb347;
    --lk-soft-bg: #fffdf9;
    --lk-light-blue: #a8e0ff;
    --lk-card-shadow: 0 10px 30px rgba(0, 31, 63, 0.06);
    --lk-hover-shadow: 0 20px 40px rgba(0, 31, 63, 0.12);
}

body {
    background-color: #fcfdfd;
    font-family: 'Montserrat', sans-serif;
    overflow-x: hidden;
    padding-top: 0 !important; /* Eliminate white line space under the navbar */
}

/* Hero Section Style */
.local-hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    background-image: linear-gradient(rgba(0, 31, 63, 0.5), rgba(0, 31, 63, 0.65)), url('<?= asset_url("images/landing-picture.png") ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #fff;
    padding-top: 100px;
    padding-bottom: 80px;
}
.hero-title {
    font-family: Impact, sans-serif;
    font-size: calc(1.475rem + 2.7vw); /* Matches display-3 scale perfectly */
    line-height: 1.05;
    letter-spacing: 2px;
    text-shadow: 3px 3px 15px rgba(0,0,0,0.6);
}
.hero-tagline {
    font-family: 'Dancing Script', cursive !important;
    font-size: 2.5rem; /* Matches products.php hero tagline scale */
    color: #fff;
    text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
    margin-top: 15px;
    font-weight: 700;
}

/* CTA Intro Section */
.cta-sec {
    background-color: var(--lk-navy);
    padding: 70px 0;
    color: #fff;
    text-align: center;
}
.cta-title {
    font-family: Impact, sans-serif;
    color: var(--lk-amber);
    font-size: 2rem; /* Reduced from 2.2rem for styling consistency */
    letter-spacing: 3px;
    margin-bottom: 12px;
}
.cta-subtitle {
    font-size: 1.35rem; /* Reduced from 1.5rem */
    font-weight: 800;
    margin-bottom: 25px;
    color: #fff;
}
.cta-desc {
    font-size: 1.08rem;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.8;
    max-width: 900px;
    margin: 0 auto;
}

/* Category Grid & Cards */
.cat-sec {
    background-color: var(--lk-soft-bg);
    padding: 80px 0;
}
.cat-heading {
    font-family: Impact, sans-serif;
    color: var(--lk-navy);
    font-size: 2rem; /* Reduced from 2.5rem */
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 40px;
}
.cat-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 60px;
}
.cat-btn-card {
    background-color: var(--lk-amber);
    border-radius: 16px;
    padding: 35px 20px;
    text-align: center;
    color: var(--lk-navy) !important;
    text-decoration: none;
    font-weight: 800;
    font-size: 1.15rem;
    text-transform: uppercase;
    box-shadow: 0 8px 24px rgba(243, 146, 0, 0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid transparent;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.cat-btn-card i {
    font-size: 2.8rem;
    margin-bottom: 15px;
    color: var(--lk-navy);
    transition: transform 0.4s ease;
}
.cat-btn-card:hover {
    transform: translateY(-8px) scale(1.03);
    background-color: #e8952a;
    box-shadow: 0 16px 32px rgba(243, 146, 0, 0.35);
    border-color: var(--lk-navy);
}
.cat-btn-card:hover i {
    transform: scale(1.2) rotate(-5deg);
}

/* Support Local Divider */
.support-local-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 25px;
    margin: 60px 0 80px;
}
.divider-bar {
    height: 3px;
    background-color: var(--lk-navy);
    flex-grow: 1;
    opacity: 0.3;
}
.support-local-text {
    text-align: center;
}
.support-local-text .lbl-small {
    font-family: 'Dancing Script', cursive !important;
    font-size: 2.2rem;
    color: var(--lk-navy);
    margin: 0;
    line-height: 0.8;
}
.support-local-text .lbl-large {
    font-family: Impact, sans-serif;
    font-size: 4rem;
    color: var(--lk-amber);
    line-height: 0.9;
    margin: 0;
    letter-spacing: 2px;
}

/* Featured Business Grid */
.featured-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
    margin-bottom: 20px;
}
.featured-grid-wrap {
    margin-bottom: 40px;
}
.featured-sec-title {
    font-family: Impact, sans-serif;
    color: var(--lk-navy);
    font-size: 2rem; /* Reduced from 2.5rem */
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 40px;
}

/* Improved Premium Featured Business Cards style */
.business-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 10px 30px rgba(0, 31, 63, 0.05);
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.business-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 31, 63, 0.12);
    border-color: rgba(243, 146, 0, 0.25);
}
.biz-image-area {
    width: 100%;
    aspect-ratio: 16 / 10;
    position: relative;
    overflow: hidden;
    background-color: #fcfcfc;
    border-bottom: 4px solid var(--lk-amber);
    padding: 0;
}
.biz-image-area img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.business-card:hover .biz-image-area img {
    transform: scale(1.06);
}
.biz-info-bar {
    padding: 24px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    flex-grow: 1;
}
.biz-details {
    flex-grow: 1;
    width: 100%;
    text-align: left;
    padding-right: 0;
}
.biz-details h4 {
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 8px;
    font-family: 'Montserrat', sans-serif;
}
.biz-details h4 a {
    color: var(--lk-navy) !important;
    text-decoration: none;
    transition: color 0.2s ease;
}
.business-card:hover .biz-details h4 a {
    color: var(--lk-amber) !important;
}
.biz-details p {
    font-size: 0.85rem;
    color: #555;
    line-height: 1.6;
    margin-bottom: 20px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.biz-action-row {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid rgba(0,0,0,0.06);
}
.biz-action-row .visit-btn {
    font-size: 0.82rem;
    font-weight: 800;
    color: var(--lk-navy);
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.2s ease;
}
.biz-action-row .visit-btn:hover {
    color: var(--lk-amber);
}
.biz-action-row .mail-btn {
    background: var(--lk-soft-bg);
    border: 1px solid rgba(243, 146, 0, 0.2);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--lk-amber);
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
}
.biz-action-row .mail-btn:hover {
    background: var(--lk-amber);
    color: var(--lk-navy);
    transform: scale(1.1);
}

/* Listings Grid Styling */
.listings-grid-wrapper {
    background-color: #fff;
    padding: 80px 0;
}
.listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 40px;
}
.listing-col {
    background: var(--lk-soft-bg);
    border-radius: 24px;
    padding: 35px 25px;
    border: 1px solid rgba(0,0,0,0.03);
    box-shadow: 0 5px 20px rgba(0,0,0,0.02);
}
.col-header {
    font-family: Impact, sans-serif;
    color: var(--lk-navy);
    font-size: 1.5rem; /* Reduced from 1.8rem */
    letter-spacing: 1px;
    border-bottom: 3px solid var(--lk-amber);
    padding-bottom: 12px;
    margin-bottom: 30px;
    text-transform: uppercase;
}
.list-item {
    display: flex;
    gap: 18px;
    margin-bottom: 25px;
    align-items: flex-start;
    transition: all 0.3s ease;
    padding: 15px;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    border: 1px solid rgba(0,0,0,0.03);
    text-align: left;
}
.list-item:hover {
    transform: translateX(8px);
    box-shadow: 0 8px 20px rgba(0, 31, 63, 0.06);
    border-color: rgba(243, 146, 0, 0.2);
}
.list-icon {
    min-width: 54px;
    height: 54px;
    background: rgba(243, 146, 0, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--lk-amber);
    font-size: 1.5rem;
    transition: all 0.3s ease;
}
.list-item:hover .list-icon {
    background: var(--lk-amber);
    color: var(--lk-navy);
    transform: scale(1.08) rotate(-5deg);
}
.list-info {
    flex-grow: 1;
}
.list-info h5 {
    font-size: 1.05rem;
    font-weight: 800;
    margin-bottom: 6px;
}
.list-info h5 a {
    color: var(--lk-navy);
    transition: color 0.2s ease;
}
.list-info h5 a:hover {
    color: var(--lk-amber);
}
.list-info p {
    font-size: 0.82rem;
    color: #666;
    margin: 2px 0;
    line-height: 1.4;
}
.list-info .contact-btn {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--lk-amber);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
    transition: color 0.2s ease, transform 0.2s ease;
}
.list-info .contact-btn:hover {
    color: var(--lk-navy);
    transform: translateX(3px);
}

/* Glass Registration Container */
.reg-sec {
    background: linear-gradient(135deg, #e0f2fe 0%, var(--lk-light-blue) 100%);
    padding: 90px 0;
    position: relative;
    overflow: hidden;
}
.reg-sec::before {
    content: '';
    position: absolute;
    width: 350px;
    height: 350px;
    border-radius: 50%;
    background: rgba(0, 31, 63, 0.05);
    top: -100px;
    left: -100px;
    pointer-events: none;
}
.reg-sec::after {
    content: '';
    position: absolute;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: rgba(243, 146, 0, 0.08);
    bottom: -80px;
    right: -80px;
    pointer-events: none;
}
.reg-card {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.45);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 15px 35px rgba(0, 31, 63, 0.05);
    padding: 50px 60px;
    position: relative;
    z-index: 1;
}
.reg-header {
    font-family: Impact, sans-serif;
    color: var(--lk-amber);
    font-size: 2.2rem; /* Reduced from 2.6rem */
    letter-spacing: 1px;
    margin-bottom: 20px;
    text-transform: uppercase;
}
.reg-intro {
    font-size: 1.12rem;
    font-weight: 500;
    color: var(--lk-navy);
    line-height: 1.6;
    margin-bottom: 35px;
}
/* Futuristic Registration Steps Layout */
/* Futuristic Registration Steps Layout - Vertically Stacked */
.steps-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
    margin: 40px auto;
    max-width: 800px;
    position: relative;
}
.step-card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 20px;
    padding: 30px 35px;
    text-align: left;
    box-shadow: 0 8px 32px rgba(0, 31, 63, 0.02);
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    gap: 30px;
    position: relative;
    overflow: visible; /* Modified to allow sequence line to extend outside the card */
}
.step-card:hover {
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 16px 40px rgba(0, 31, 63, 0.08);
    border-color: rgba(243, 146, 0, 0.3);
}

/* Vertical Process Connection Timeline */
.step-card:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 57px; /* Center of the badge: padding-left (35px) + badge-radius (22px) */
    top: 74px; /* Bottom of the badge: padding-top (30px) + badge-height (44px) */
    width: 3px;
    height: calc(100% - 14px); /* Math-derived length to cross the gap and touch the next badge perfectly */
    background: linear-gradient(180deg, var(--lk-amber) 0%, var(--lk-gold) 60%, rgba(243, 146, 0, 0.15) 100%);
    z-index: 2;
    border-radius: 3px;
    box-shadow: 0 0 8px rgba(243, 146, 0, 0.3);
    pointer-events: none;
}

@media (max-width: 576px) {
    .step-card {
        padding: 24px 20px;
        gap: 20px;
    }
    .step-card:not(:last-child)::after {
        left: 42px; /* Recalculated for mobile padding: padding-left (20px) + 22px */
        top: 68px;  /* Recalculated for mobile padding: padding-top (24px) + 44px */
        height: calc(100% - 14px);
    }
}

.step-card-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--lk-amber), var(--lk-gold));
    color: var(--lk-navy);
    font-family: Impact, sans-serif;
    font-size: 1.25rem;
    border-radius: 50%;
    box-shadow: 0 6px 16px rgba(243, 146, 0, 0.25);
    font-weight: bold;
    flex-shrink: 0;
}
.step-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--lk-navy);
    margin-bottom: 12px;
    text-transform: uppercase;
    line-height: 1.2;
}
.step-card-body {
    font-size: 0.88rem;
    color: #4b5563;
    line-height: 1.55;
    flex-grow: 1;
}
.step-card-list {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
}
.step-card-list li {
    font-size: 0.84rem;
    margin-bottom: 8px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    color: #374151;
}
.step-card-list li i {
    color: var(--lk-amber);
    margin-top: 3px;
    font-size: 0.85rem;
}
.step-link-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(0, 31, 63, 0.08);
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--lk-navy);
    text-decoration: none;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0, 31, 63, 0.02);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.step-link-btn i {
    color: var(--lk-amber);
    font-size: 1rem;
    transition: transform 0.3s ease;
}
.step-link-btn:hover {
    background: var(--lk-navy);
    color: #ffffff;
    border-color: var(--lk-navy);
    transform: translateX(6px) scale(1.02);
    box-shadow: 0 10px 20px rgba(0, 31, 63, 0.15);
}
.step-link-btn:hover i {
    color: var(--lk-gold);
    transform: scale(1.2) rotate(10deg);
}
.apply-btn {
    position: relative;
    background: linear-gradient(135deg, var(--lk-amber), #ffb347);
    color: var(--lk-navy) !important;
    font-weight: 800;
    font-family: 'Poppins', sans-serif;
    font-size: 1.2rem;
    letter-spacing: 1.5px;
    padding: 16px 48px;
    border: 2px solid var(--lk-navy);
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 8px 24px rgba(243, 146, 0, 0.3), 4px 4px 0px var(--lk-navy);
    margin-top: 25px;
    text-transform: uppercase;
    overflow: hidden;
    z-index: 1;
}
.apply-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: all 0.6s ease;
    z-index: -1;
}
.apply-btn:hover::before {
    left: 100%;
}
.apply-btn:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 16px 32px rgba(243, 146, 0, 0.45), 2px 2px 0px var(--lk-navy);
    background: linear-gradient(135deg, #ffb347, var(--lk-amber));
}
.apply-btn i {
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}
.apply-btn:hover i {
    transform: translateX(6px);
}
.help-link {
    display: block;
    margin-top: 20px;
    color: var(--lk-navy);
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: underline;
}
.help-link:hover {
    color: var(--lk-amber);
}

@media (max-width: 768px) {
    .hero-title { font-size: 3rem; }
    .hero-tagline { font-size: 2.2rem; }
    .reg-card { padding: 30px; }
    .reg-header { font-size: 2rem; }
}

/* Animations */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}
</style>

<!-- HERO SECTION -->
<section class="local-hero">
    <div class="container text-start">
        <div class="row">
            <div class="col-lg-8 col-xl-7">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 1.5px; font-size: 0.8rem;">SUPPORT ENTREPRENEURS</span>
                <h1 class="hero-title animate__animated animate__fadeInDown">LOKAL NA NEGOSYO,<br><span style="color: var(--lk-amber);">LOKAL NA ASENSO</span></h1>
                <p class="hero-tagline animate__animated animate__fadeInUp">Supporting entrepreneurs, building community resilience.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA / INTRO SECTION -->
<section class="cta-sec">
    <div class="container">
        <h2 class="cta-title reveal">CONNECT. SHOWCASE. SELL. GROW.</h2>
        <h3 class="cta-subtitle reveal">Bring your business closer to the community.</h3>
        <p class="cta-desc reveal">
            Discover the vibrant local businesses of Vinzons and Talisay! Support homegrown 
            entrepreneurs, explore unique products, and book services directly through our platform. 
            Local business owners can register to showcase their products and services, reaching 
            more customers in just a few clicks.
        </p>
    </div>
</section>

<!-- DIRECTORY SECTION -->
<section class="cat-sec">
    <div class="container">
        <h2 class="cat-heading reveal">BUSINESS DIRECTORY</h2>
        
        <div class="cat-card-grid">
            <a href="#featured-restaurants" class="cat-btn-card reveal">
                <i class="fa-solid fa-utensils"></i>
                <span>Food & Restaurants</span>
            </a>
            <a href="#resorts-stays" class="cat-btn-card reveal">
                <i class="fa-solid fa-hotel"></i>
                <span>Resorts & Homestays</span>
            </a>
            <a href="#local-listings" class="cat-btn-card reveal">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>Pasalubongs</span>
            </a>
            <a href="#local-listings" class="cat-btn-card reveal">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span>Services</span>
            </a>
        </div>

        <div class="support-local-divider reveal">
            <div class="divider-bar"></div>
            <div class="support-local-text">
                <p class="lbl-small">Support</p>
                <h2 class="lbl-large">LOCAL</h2>
            </div>
            <div class="divider-bar"></div>
        </div>

        <h2 class="featured-sec-title reveal">FEATURED BUSINESS</h2>
        <div class="featured-grid-wrap reveal">
            <?php require BASE_PATH . '/includes/partials/featured-businesses.php'; ?>
        </div>
    </div>
</section>

<!-- LISTINGS GRID SECTION -->
<div class="listings-grid-wrapper" id="local-listings">
    <div class="container">
        <?php require BASE_PATH . '/includes/partials/business-listings.php'; ?>
    </div>
</div>

<!-- HOW TO REGISTER / GLASS SECTION -->
<section class="reg-sec">
    <div class="container text-center">
        <div class="reg-card reveal">
            <h2 class="reg-header">REGISTER YOUR BUSINESS</h2>
            <h3 class="reg-intro">How to Register?</h3>
            <p class="text-secondary mb-4" style="font-size: 1.05rem; line-height: 1.6;">
                If you're a local business owner from Talisay or Vinzons, you can be part of our online directory! Follow these simple steps:
            </p>

            <div class="steps-container">
                <!-- STEP 1 -->
                <div class="step-card reveal">
                    <div class="step-card-num">01</div>
                    <div class="step-card-content flex-grow-1">
                        <h4 class="step-card-title"><i class="fa-solid fa-folder-open text-warning me-1.5" style="font-size: 0.95rem;"></i>Prepare Info</h4>
                        <div class="step-card-body">
                            <p class="small text-muted mb-3" style="font-size: 0.78rem;">Ensure you have the following ready before starting:</p>
                            <ul class="step-card-list">
                                <li><i class="fa-solid fa-circle-check"></i> Business Name</li>
                                <li><i class="fa-solid fa-circle-check"></i> Category &amp; Type</li>
                                <li><i class="fa-solid fa-circle-check"></i> Address / Barangay</li>
                                <li><i class="fa-solid fa-circle-check"></i> Contact &amp; Email</li>
                                <li><i class="fa-solid fa-circle-check"></i> Short Description</li>
                                <li><i class="fa-solid fa-circle-check"></i> Logo or Photo</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="step-card reveal" style="transition-delay: 0.15s;">
                    <div class="step-card-num">02</div>
                    <div class="step-card-content flex-grow-1">
                        <h4 class="step-card-title"><i class="fa-solid fa-paper-plane text-warning me-1.5" style="font-size: 0.95rem;"></i>Submit Details</h4>
                        <div class="step-card-body">
                            <p class="small text-muted mb-3" style="font-size: 0.78rem;">Send details through any of our channels:</p>
                            <a href="https://likhalokal.com" class="step-link-btn" target="_blank" title="Visit website">
                                <i class="fa-solid fa-globe"></i>
                                <span>likhalokal.com</span>
                            </a>
                            <a href="mailto:talisayvinzons.directory@gmail.com" class="step-link-btn" title="Email us">
                                <i class="fa-solid fa-envelope"></i>
                                <span>Email Directory</span>
                            </a>
                            <a href="#" class="step-link-btn" title="Visit Facebook Page">
                                <i class="fa-brands fa-facebook"></i>
                                <span>Official FB Page</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="step-card reveal" style="transition-delay: 0.3s;">
                    <div class="step-card-num">03</div>
                    <div class="step-card-content flex-grow-1">
                        <h4 class="step-card-title"><i class="fa-solid fa-shield-halved text-warning me-1.5" style="font-size: 0.95rem;"></i>Verification</h4>
                        <div class="step-card-body d-flex flex-column justify-content-between">
                            <p class="mb-0" style="font-size: 0.82rem; color: #475569;">
                                Our moderation board will carefully review your business details and credentials to guarantee directory authenticity.
                            </p>
                            <div class="mt-3 text-start"><span class="badge bg-warning text-dark px-2.5 py-1 fw-bold rounded" style="font-size: 0.65rem; letter-spacing: 0.5px;">TAKES 1-2 BUSINESS DAYS</span></div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="step-card reveal" style="transition-delay: 0.45s;">
                    <div class="step-card-num">04</div>
                    <div class="step-card-content flex-grow-1">
                        <h4 class="step-card-title"><i class="fa-solid fa-store text-warning me-1.5" style="font-size: 0.95rem;"></i>Live Listing</h4>
                        <div class="step-card-body d-flex flex-column justify-content-between">
                            <p class="mb-0" style="font-size: 0.82rem; color: #475569;">
                                Once verified, your shop profile is officially published to the directory! You can immediately list products and manage offers.
                            </p>
                            <div class="mt-3 text-start"><span class="badge bg-success text-white px-2.5 py-1 fw-bold rounded" style="font-size: 0.65rem; letter-spacing: 0.5px; background-color: #1b4332 !important;">DIRECTORY LIVE</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="<?= e(BASE_URL) ?>register-business.php" class="apply-btn">APPLY BUSINESS <i class="fa-solid fa-chevron-right"></i></a>
            <a href="<?= e(BASE_URL) ?>about.php" class="help-link">Need Help?</a>
        </div>
    </div>
</section>

<!-- JAVASCRIPT FOR INTERACTIVE SCROLL REVEAL & ANIMS -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    /* Intersection Observer for reveal items */
    const revealEls = document.querySelectorAll('.reveal');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px"
    };

    const revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    revealEls.forEach(function (el) {
        revealObserver.observe(el);
    });

    /* Add animate__fadeIn on hero elements after brief delay */
    setTimeout(() => {
        const heroEl = document.querySelector('.local-hero');
        if (heroEl) {
            heroEl.style.opacity = '1';
        }
    }, 100);
});
</script>

<?php require BASE_PATH . '/includes/footer.php'; ?>
