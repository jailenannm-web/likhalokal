<?php
declare(strict_types=1);

$pageTitle = 'Local Business';
$activeNav = 'business';
require_once dirname(__DIR__) . '/bootstrap.php';

$featured = db()->query(
    "SELECT * FROM businesses WHERE status='approved' ORDER BY id ASC LIMIT 4"
)->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Import Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Bungee&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --vinzons-blue: #0077C2;
        --vinzons-dark-blue: #050A30;
        --vinzons-amber: #FFBF00;
        --vinzons-orange: #FF9800;
        --vinzons-white: #ffffff;
        --body-font: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Normal Text Stylings (Matches About Page) */
    body, p, span, li, small, a:not(.font-bungee) {
        font-family: var(--body-font) !important;
        font-style: normal !important;
        line-height: 1.6;
    }
/* Business Portal Hero Section */
.biz-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('assets/images/local-workers-bg.jpg'); /* Replace with your image path */
    background-size: cover;
    background-position: center;
    padding: 100px 10%;
    color: white;
    text-align: left;
}

.biz-hero-title {
    font-family: 'Bungee', cursive;
    font-size: 4.5rem;
    line-height: 1.1;
    margin-bottom: 20px;
    text-transform: uppercase;
}

.biz-hero-tagline {
    font-family: 'Bilbo Swash Caps', cursive;
    font-size: 2.2rem;
    max-width: 500px;
}

/* Call to Action Section */
.biz-cta-section {
    background-color: #051024; /* Dark navy background from image */
    padding: 80px 15%;
    text-align: center;
    color: white;
}

.biz-keywords {
    font-family: 'Bungee', cursive;
    color: #FFB300; /* Yellow/Amber color */
    font-size: 2.5rem;
    letter-spacing: 2px;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.biz-main-heading {
    font-family: 'Abril Fatface', serif;
    font-size: 2.8rem;
    margin-bottom: 30px;
}

.biz-description {
    font-family: 'serif'; /* Lisu Bosa fallback */
    font-size: 1.1rem;
    line-height: 1.6;
    max-width: 900px;
    margin: 0 auto;
    opacity: 0.9;
}


/* Business Directory Categories */
.directory-section {
    padding: 60px 10%;
    background-color: #fffaf5; /* Very light cream background */
}

.section-title {
    font-family: 'Bungee', cursive;
    color: var(--dark-navy);
    font-size: 2rem;
    margin-bottom: 30px;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 80px;
}

.category-card {
    background-color: var(--amber-orange);
    border-radius: 15px;
    padding: 30px 10px;
    text-align: center;
    color: var(--dark-navy);
    text-decoration: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.category-card i {
    font-size: 2.5rem;
    margin-bottom: 15px;
    display: block;
}

.category-card span {
    font-family: 'Bungee', cursive;
    font-size: 1.1rem;
}

/* Support Local Divider */
.support-local-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin: 50px 0;
}

.support-line {
    height: 3px;
    background-color: var(--dark-navy);
    flex-grow: 1;
}

.support-text {
    text-align: center;
}

.support-text .small-text {
    font-family: 'Abril Fatface', serif;
    font-size: 1.8rem;
    color: var(--dark-navy);
    margin: 0;
}

.support-text .large-text {
    font-family: 'Bungee', cursive;
    font-size: 3.5rem;
    color: var(--amber-orange);
    line-height: 0.8;
    margin: 0;
}

/* Featured Business Cards */
.featured-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.business-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.biz-image-area {
    padding: 30px;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.biz-image-area img {
    max-width: 80%;
    max-height: 80%;
    object-fit: contain;
}

.biz-info-bar {
    background-color: var(--amber-orange);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.biz-details h4 {
    font-family: 'Bungee', cursive;
    font-size: 0.9rem;
    margin: 0;
    color: var(--dark-navy);
}

.biz-details p {
    font-size: 0.75rem;
    margin: 3px 0 0;
    line-height: 1.2;
    color: #222;
}

.mail-btn {
    background: white;
    border-radius: 8px;
    padding: 5px 8px;
    color: var(--dark-navy);
    text-decoration: none;
}

:root {
    --amber-orange: #f2a63d;
    --dark-navy: #051024;
    --section-bg: #fffaf5; /* Consistent background color */
}

/* --- SECTION 1: HERO & CTA --- */
.biz-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('assets/images/local-workers-bg.jpg'); 
    background-size: cover;
    background-position: center;
    padding: 120px 10%;
    color: white;
}

.biz-hero-title {
    font-family: 'Bungee', cursive;
    font-size: 4rem;
    line-height: 1.1;
    text-transform: uppercase;
}

.biz-hero-tagline {
    font-family: 'Bilbo Swash Caps', cursive;
    font-size: 2.2rem;
    margin-top: 15px;
}

.biz-cta-section {
    background-color: var(--dark-navy);
    padding: 80px 15%;
    text-align: center;
    color: white;
}

.biz-keywords {
    font-family: 'Bungee', cursive;
    color: var(--amber-orange);
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.biz-main-heading {
    font-family: 'Abril Fatface', serif;
    font-size: 2.5rem;
    margin-bottom: 25px;
}

.biz-description {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.15rem;
    line-height: 1.6;
    max-width: 850px;
    margin: 0 auto;
}

/* --- SECTION 2: DIRECTORY & FEATURED --- */
.directory-wrapper {
    background-color: var(--section-bg);
    padding: 80px 10%;
}

.section-label {
    font-family: 'Bungee', cursive;
    color: var(--dark-navy);
    font-size: 2rem;
    margin-bottom: 40px;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 100px;
}

.category-card {
    background-color: var(--amber-orange);
    border-radius: 15px;
    padding: 40px 20px;
    text-align: center;
    color: var(--dark-navy);
    text-decoration: none;
    font-family: 'Bungee', cursive;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Support Local Divider */
.support-local-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;
    margin-bottom: 80px;
}

.divider-line {
    height: 3px;
    background-color: var(--dark-navy);
    flex-grow: 1;
}

.support-title { text-align: center; }
.support-title .small { font-family: 'Abril Fatface', serif; font-size: 1.8rem; color: var(--dark-navy); margin: 0; }
.support-title .large { font-family: 'Bungee', cursive; font-size: 3.5rem; color: var(--amber-orange); line-height: 0.8; margin: 0; }

/* Featured Business Cards */
.featured-biz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 100px;
}

.biz-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.biz-img-box { height: 200px; display: flex; align-items: center; justify-content: center; padding: 20px; }
.biz-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

.biz-footer {
    background-color: var(--amber-orange);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.biz-footer h4 { font-family: 'Bungee', cursive; font-size: 0.9rem; margin: 0; }
.biz-footer p { font-size: 0.75rem; margin: 5px 0 0; line-height: 1.3; }

/* --- SECTION 3: THREE-COLUMN LISTINGS --- */
.listings-wrapper {
    background-color: var(--section-bg);
    padding: 0 10% 100px; /* Reduced top padding as it follows Section 2 */
}

.listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 50px;
}

.col-header {
    font-family: 'Bungee', cursive;
    font-size: 1.5rem;
    color: var(--dark-navy);
    border-bottom: 4px solid var(--amber-orange);
    padding-bottom: 10px;
    margin-bottom: 35px;
}

.list-item {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    align-items: flex-start;
}

.list-icon {
    min-width: 80px;
    height: 80px;
    background: white;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border: 1px solid #eee;
}

.list-icon i { font-size: 1.8rem; color: var(--dark-navy); }

.list-info h5 { font-family: 'Inter', sans-serif; font-weight: 800; margin: 0; color: var(--dark-navy); }
.list-info p { font-size: 0.85rem; color: #555; margin: 2px 0; }
.list-info .contact-btn { font-size: 0.85rem; font-weight: 700; color: var(--dark-navy); text-decoration: underline; display: block; margin-top: 5px; }

:root {
    --amber-orange: #f2a63d;
    --dark-navy: #051024;
    --light-blue-bg: #a8e0ff;
}

/* Container Reset to remove white lines */
.reg-container {
    background-color: var(--light-blue-bg);
    padding-bottom: 100px;
    margin: 0;
    margin-top: -1px;
}

.reg-banner {
    background-color: var(--dark-navy);
    padding: 25px 0;
    text-align: center;
}

.reg-banner h2 {
    font-family: 'Bungee', cursive;
    color: white;
    margin: 0;
    font-size: 2.2rem;
    letter-spacing: 2px;
}

/* Glassmorphism Card for Alignment */
.registration-card {
    max-width: 1000px;
    margin: 60px auto;
    padding: 60px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 40px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
}

.reg-title {
    font-family: 'Bungee', cursive;
    color: var(--amber-orange);
    font-size: 3rem;
    margin-bottom: 25px;
}

/* Lisu Bosa Emphasis */
.lisu-info {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.45rem;
    font-weight: 500;
    line-height: 1.7;
    color: var(--dark-navy);
}

.step-header {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 1.5rem;
    color: var(--dark-navy);
    display: block;
    margin-top: 40px;
    margin-bottom: 10px;
    text-transform: uppercase;
    border-left: 5px solid var(--amber-orange);
    padding-left: 15px;
}

.step-details {
    list-style: none;
    padding-left: 40px;
    margin-top: 15px;
}

.step-details li {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.3rem;
    margin-bottom: 12px;
    position: relative;
}

.step-details li::before {
    content: "→"; /* Stylized arrow for emphasis */
    color: var(--amber-orange);
    position: absolute;
    left: -25px;
    font-weight: bold;
}

.apply-button {
    background-color: var(--amber-orange);
    color: var(--dark-navy);
    font-family: 'Bungee', cursive;
    padding: 20px 100px;
    border: none;
    border-radius: 15px;
    font-size: 1.4rem;
    cursor: pointer;
    box-shadow: 0 6px 0px #c98220;
    transition: all 0.2s ease;
    display: inline-block;
}

.apply-button:hover {
    transform: translateY(3px);
    box-shadow: 0 3px 0px #c98220;
}
/* Mobile Responsiveness */
@media (max-width: 768px) {
    .biz-hero-title { font-size: 2.5rem; }
    .biz-keywords { font-size: 1.5rem; }
    .biz-main-heading { font-size: 1.8rem; }
}
</style> 

<section class="biz-hero">
    <div class="container-fluid">
        <h1 class="biz-hero-title">
            LOKAL NA NEGOSYO,<br>
            LOKAL NA ASENSO
        </h1>
        <p class="biz-hero-tagline">
            Supporting entrepreneurs, building community livelihoods.
        </p>
    </div>
</section>

<section class="biz-cta-section">
    <div class="container">
        <h2 class="biz-keywords">CONNECT. SHOWCASE. SELL. GROW.</h2>
        <h3 class="biz-main-heading">Bring your business closer to the community.</h3>
        
        <p class="biz-description">
            Discover the vibrant local businesses of Vinzons and Talisay! Support homegrown 
            entrepreneurs, explore unique products, and book services directly through our platform. 
            Local business owners can register to showcase their products and services, reaching 
            more customers in just a few clicks.
        </p>
    </div>
</section>

<section class="directory-section">
    <h2 class="section-title">BUSINESS DIRECTORY</h2>
    
    <div class="category-grid">
        <a href="#" class="category-card">
            <i class="bi bi-egg-fried"></i>
            <span>Food & Restaurants</span>
        </a>
        <a href="#" class="category-card">
            <i class="bi bi-house-heart"></i>
            <span>Resorts & Homestays</span>
        </a>
        <a href="#" class="category-card">
            <i class="bi bi-bag-check"></i>
            <span>Pasalubongs</span>
        </a>
        <a href="#" class="category-card">
            <i class="bi bi-gear-wide-connected"></i>
            <span>Services</span>
        </a>
    </div>

    <div class="support-local-wrap">
        <div class="support-line"></div>
        <div class="support-text">
            <p class="small-text">Support</p>
            <h2 class="large-text">LOCAL</h2>
        </div>
        <div class="support-line"></div>
    </div>

    <h2 class="section-title">FEATURED BUSINESS</h2>

    <div class="featured-grid">
        <div class="business-card">
            <div class="biz-image-area">
                <img src="assets/images/fruit-logo.png" alt="Fruit Stand">
            </div>
            <div class="biz-info-bar">
                <div class="biz-details">
                    <h4>Vinzons Fruit Stand</h4>
                    <p>Fresh tropical fruits like pineapple, mangoes, and bananas from local farms.</p>
                </div>
                <a href="#" class="mail-btn"><i class="bi bi-envelope"></i></a>
            </div>
        </div>

        <div class="business-card">
            <div class="biz-image-area">
                <img src="assets/images/crafts-logo.png" alt="Coastal Crafts">
            </div>
            <div class="biz-info-bar">
                <div class="biz-details">
                    <h4>Coastal Crafts Vinzons</h4>
                    <p>Driftwood art, shell ornaments, and miniature boats handcrafted locally.</p>
                </div>
                <a href="#" class="mail-btn"><i class="bi bi-envelope"></i></a>
            </div>
        </div>

        <div class="business-card">
            <div class="biz-image-area">
                <img src="assets/images/native-logo.png" alt="Native Touch">
            </div>
            <div class="biz-info-bar">
                <div class="biz-details">
                    <h4>Native Touch Souvenirs</h4>
                    <p>Coconut shell crafts, miniature bahay kubo, and decorative ornaments.</p>
                </div>
                <a href="#" class="mail-btn"><i class="bi bi-envelope"></i></a>
            </div>
        </div>

        <div class="business-card">
            <div class="biz-image-area">
                <img src="assets/images/sweets-logo.png" alt="Sweet Treats">
            </div>
            <div class="biz-info-bar">
                <div class="biz-details">
                    <h4>Sweet Treats Vinzons</h4>
                    <p>Pandecillos, pili tart, angko, sapin-sapin, and local delicacies.</p>
                </div>
                <a href="#" class="mail-btn"><i class="bi bi-envelope"></i></a>
            </div>
        </div>
    </div>
</section>

<div class="directory-wrapper">
    <h2 class="section-label">BUSINESS DIRECTORY</h2>
    <div class="category-grid">
        <a href="#" class="category-card"><i class="bi bi-egg-fried d-block mb-2 fs-1"></i><span>Food & Restaurants</span></a>
        <a href="#" class="category-card"><i class="bi bi-house-heart d-block mb-2 fs-1"></i><span>Resorts & Homestays</span></a>
        <a href="#" class="category-card"><i class="bi bi-bag-check d-block mb-2 fs-1"></i><span>Pasalubongs</span></a>
        <a href="#" class="category-card"><i class="bi bi-gear-wide-connected d-block mb-2 fs-1"></i><span>Services</span></a>
    </div>

    <div class="support-local-divider">
        <div class="divider-line"></div>
        <div class="support-title">
            <p class="small">Support</p>
            <h2 class="large">LOCAL</h2>
        </div>
        <div class="divider-line"></div>
    </div>

    <h2 class="section-label">FEATURED BUSINESS</h2>
    <div class="featured-biz-grid">
        <div class="biz-card">
            <div class="biz-img-box"><img src="assets/images/fruit-stand.png" alt="Fruit Stand"></div>
            <div class="biz-footer">
                <div><h4>Vinzons Fruit Stand</h4><p>Fresh tropical fruits from local farms.</p></div>
                <a href="#" class="text-dark"><i class="bi bi-envelope-fill fs-4"></i></a>
            </div>
        </div>
        <div class="biz-card">
            <div class="biz-img-box"><img src="assets/images/crafts.png" alt="Crafts"></div>
            <div class="biz-footer">
                <div><h4>Coastal Crafts Vinzons</h4><p>Driftwood art and shell ornaments.</p></div>
                <a href="#" class="text-dark"><i class="bi bi-envelope-fill fs-4"></i></a>
            </div>
        </div>
        <div class="biz-card">
            <div class="biz-img-box"><img src="assets/images/souvenirs.png" alt="Souvenirs"></div>
            <div class="biz-footer">
                <div><h4>Native Touch Souvenirs</h4><p>Coconut shell crafts and decor.</p></div>
                <a href="#" class="text-dark"><i class="bi bi-envelope-fill fs-4"></i></a>
            </div>
        </div>
    </div>
</div>

<section class="listings-wrapper">
    <div class="listings-grid">
        
        <div class="listing-col">
            <h3 class="col-header">Restaurants & Cafes</h3>
            
            <div class="list-item">
                <div class="list-icon"><i class="bi bi-cup-hot"></i></div>
                <div class="list-info">
                    <h5>Liham Cafe</h5>
                    <p>Barangay Poblacion</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item">
                <div class="list-icon"><i class="bi bi-cake2"></i></div>
                <div class="list-info">
                    <h5>Cakefrost Vinzons</h5>
                    <p>Near Town Plaza</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item">
                <div class="list-icon"><i class="bi bi-cup-straw"></i></div>
                <div class="list-info">
                    <h5>Maxicup Vinzons</h5>
                    <p>Barangay San Isidro</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>
        </div>

        <div class="listing-col">
            <h3 class="col-header">Resorts & Stays</h3>
            
            <div class="list-item">
                <div class="list-icon"><i class="bi bi-building"></i></div>
                <div class="list-info">
                    <h5>Erica Resort</h5>
                    <p>Coastal Area, Vinzons</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item">
                <div class="list-icon"><i class="bi bi-house-door"></i></div>
                <div class="list-info">
                    <h5>Casa Indan Resort</h5>
                    <p>Barangay Sabang</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item">
                <div class="list-icon"><i class="bi bi-sun"></i></div>
                <div class="list-info">
                    <h5>Calaguas Paradise Resort</h5>
                    <p>Mahabang Buhangin</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>
        </div>

        <div class="listing-col">
            <h3 class="col-header">Local Services</h3>
            
            <div class="list-item">
                <div class="list-icon"><i class="bi bi-tsunami"></i></div>
                <div class="list-info">
                    <h5>Calaguas Island Trips</h5>
                    <p>Vinzons Port</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item">
                <div class="list-icon"><i class="bi bi-bank"></i></div>
                <div class="list-info">
                    <h5>Museum Tour</h5>
                    <p>W. Vinzons Shrine</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item">
                <div class="list-icon"><i class="bi bi-tools"></i></div>
                <div class="list-info">
                    <h5>Pili Artisan Workshop</h5>
                    <p>Barangay Minaogan</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>
        </div>

    </div>
</section>

<section class="reg-container">
    <div class="reg-banner">
        <h2>REGISTER YOUR BUSINESS</h2>
    </div>

    <div class="registration-card">
        <h3 class="reg-title">How to Register?</h3>
        
        <p class="lisu-description">
            If you're a local business owner from Talisay or Vinzons, you can be part of our online directory! Follow these steps:
        </p>

        <div class="step-box">
            <span class="step-label">Step 1 – Prepare Your Business Information</span>
            <p class="lisu-description" style="font-size: 1.2rem; margin-bottom: 15px;">Gather the following:</p>
            <ul class="step-details">
                <li>Business Name</li>
                <li>Business Category (e.g., Food, Services, Local Products)</li>
                <li>Address / Barangay</li>
                <li>Contact Number</li>
                <li>Short Business Description</li>
                <li>Logo or Photo</li>
            </ul>
        </div>

        <div class="step-box">
            <span class="step-label">Step 2 – Submit Your Details</span>
            <p class="lisu-description" style="font-size: 1.2rem; margin-bottom: 15px;">Send your information through:</p>
            <ul class="step-details">
                <li><strong>Website</strong> - LikhaLokal: Tuklas, Kultura, Kabuhayan (likhalokal.com)</li>
                <li><strong>Email</strong>: talisayvinzons.directory@gmail.com</li>
                <li style="list-style: none; margin: 5px 0;">or</li>
                <li><strong>Facebook Page</strong>: LikhaLokal: Tuklas, Kultura, Kabuhayan</li>
            </ul>
        </div>

        <div class="step-box">
            <span class="step-label">Step 3 – Verification</span>
            <p class="lisu-description">Our team will review your submission to ensure all information is correct.</p>
        </div>

        <div class="step-box">
            <span class="step-label">Step 4 – Listing</span>
            <p class="lisu-description">Once approved, your business will be added to the Business Directory page within 1–2 days.</p>
        </div>

        <div style="text-align: center;">
            <button class="apply-button">APPLY BUSINESS</button>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>