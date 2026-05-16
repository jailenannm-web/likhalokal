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

<style>
    @import url('https://fonts.googleapis.com/css2?family=Lisu+Bosa:wght@400;500;600&display=swap');
    
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
    }/* Base Container Styles (for alignment) */
.container-fluid {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* --- Hero Section with Background Banner --- */
.biz-hero {
    position: relative;
    min-height: 450px; /* Adjust height as needed */
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #ffffff;
    
    /* Placeholder Background Image Setup */
    /* REPLACE the placeholder URL with your actual image path later */
    background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), 
                      url('https://via.placeholder.com/1920x600/2c3e50/ffffff?text=Local+Business+Banner');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: scroll; /* Change to 'fixed' for a parallax effect */
}

.biz-hero-content {
    z-index: 2; /* Ensures text stays above any backgrounds */
    padding: 30px;
}

.biz-hero-title {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 15px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.biz-hero-tagline {
    font-size: 1.25rem;
    font-weight: 400;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
}

/* --- CTA / Description Section --- */
.biz-cta-section {
    padding: 60px 0;
    text-align: center;
    background-color: #f9f9f9;
}

.biz-keywords {
    font-size: 1rem;
    color: #007bff; /* Adjust theme color here */
    letter-spacing: 3px;
    margin-bottom: 10px;
    font-weight: 700;
}

.biz-main-heading {
    font-size: 2rem;
    color: #333333;
    margin-bottom: 20px;
    font-weight: 600;
}

.biz-description {
    font-size: 1.1rem;
    color: #666666;
    line-height: 1.6;
    max-width: 800px;
    margin: 0 auto;
}

/* Business Directory Categories */
.directory-section {
    padding: 60px 10%;
    background-color: #fffaf5; /* Very light cream background */
}

.section-title {
    font-family: 'Bungee';
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
    font-family: 'Bungee';
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
    font-family: 'Bungee';
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
    font-family: 'Bungee';
    font-size: 4rem;
    line-height: 1.1;
    text-transform: uppercase;
}

.biz-hero-tagline {
    font-family: 'Bilbo Swash Caps';
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
    font-family: 'Bungee';
    color: var(--amber-orange);
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.biz-main-heading {
    font-family: 'Abril Fatface', serif;
    font-size: 2.5rem;
    margin-bottom: 25px;
    color: White;
}

.biz-description {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.15rem;
    line-height: 1.6;
    max-width: 850px;
    margin: 0 auto;
    color: var(--amber-orange);
}

/* --- SECTION 2: DIRECTORY & FEATURED --- */
.directory-wrapper {
    background-color: var(--section-bg);
    padding: 80px 10%;
}

.section-label {
    font-family: 'Bungee';
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
    font-family: 'Bungee';
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
.support-title .large { font-family: 'Bungee'; font-size: 3.5rem; color: var(--amber-orange); line-height: 0.8; margin: 0; }

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

.biz-footer h4 { font-family: 'Bungee'; font-size: 0.9rem; margin: 0; }
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
    font-family: 'Bungee';
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
/* Registration Container */
    .reg-container {
        background-color: var(--light-blue-bg);
        padding-bottom: 80px;
        margin-top: -1px; /* Prevents white line gaps between sections */
    }

    .reg-banner {
        background-color: var(--dark-navy);
        padding: 20px 0;
        text-align: center;
    }

    .reg-banner h2 {
        font-family: 'Bungee';
        color: white;
        margin: 0;
        font-size: 1.8rem;
        letter-spacing: 2px;
    }

    /* The Registration Card */
    .registration-card {
        max-width: 900px;
        margin: 50px auto;
        padding: 45px 70px;
        background: rgba(255, 255, 255, 0.35); /* Glassmorphism effect */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 35px;
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .reg-title {
        font-family: 'Bungee';
        color: var(--amber-orange);
        font-size: 2.5rem;
        margin-bottom: 12px;
    }

    /* Lisu Bosa Styling with Tight Spacing */
    .lisu-description {
        font-family: 'Lisu Bosa', serif !important;
        font-size: 1.15rem;
        font-weight: 500;
        line-height: 1.35; /* Tight line spacing */
        color: var(--dark-navy);
        margin-bottom: 20px;
    }

    .step-box {
        margin-bottom: 22px; /* Compact gap between steps */
    }

    .step-label {
        font-family: 'Inter', sans-serif;
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--dark-navy);
        display: block;
        margin-bottom: 5px;
        text-transform: uppercase;
        border-left: 4px solid var(--amber-orange);
        padding-left: 12px;
    }

    .step-details {
        list-style: none;
        padding-left: 35px;
        margin-top: 8px;
    }

    .step-details li {
        font-family: 'Lisu Bosa', serif !important;
        font-size: 1.1rem;
        line-height: 1.25; /* Tightened line spacing for lists */
        margin-bottom: 6px;
        position: relative;
        color: var(--dark-navy);
    }

    .step-details li::before {
        content: "→";
        color: var(--amber-orange);
        position: absolute;
        left: -22px;
        font-weight: bold;
    }

    /* Footer Button Styling */
    .apply-button-wrap {
        text-align: center;
        margin-top: 30px;
    }

    .apply-button {
        background-color: var(--amber-orange);
        color: var(--dark-navy);
        font-family: 'Bungee';
        padding: 15px 80px;
        border: none;
        border-radius: 12px;
        font-size: 1.3rem;
        cursor: pointer;
        box-shadow: 0 5px 0px #c98220;
        transition: all 0.2s ease;
        display: inline-block;
        text-decoration: none;
    }

    .apply-button:hover {
        transform: translateY(2px);
        box-shadow: 0 3px 0px #c98220;
        color: var(--dark-navy);
    }

    /* Mobile Tweaks */
    @media (max-width: 768px) {
        .registration-card {
            margin: 20px;
            padding: 30px;
        }
        .reg-title { font-size: 1.8rem; }
    }
    
<<<<<<< HEAD
=======
    .biz-hero {
    /* This adds a dark overlay gradient so your white text stays perfectly readable over the image */
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../images/local-business-hero.jpg');
    
    /* This forces the image to scale beautifully and stay centered without stretching */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    
    /* Gives the hero section its spacious height */
    padding: 100px 0; 
    min-height: 60vh;
    
    /* Flexbox setup to center the text elements vertically and horizontally */
    display: flex;
    align-items: center;
    color: #ffffff; /* Forces text to be crisp white over the background image */
}

.biz-hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    
    /* Smooth slide-in-left animation for the main title */
    animation: slideInLeft 1.2s ease-out forwards;
    opacity: 0; /* Starts hidden and fades in via keyframes */
}

.biz-hero-tagline {
    font-size: 1.5rem;
    font-weight: 400;
    opacity: 0.9;
    
    /* Clean fade-in that starts slightly after the title moves */
    animation: fadeIn 1s ease-out 0.5s forwards;
    opacity: 0;
}

/* --- Animation Keyframes --- */
@keyframes slideInLeft {
    0% {
        opacity: 0;
        transform: translateX(-30px); /* Slides rightward into place */
    }
    100% {
        opacity: 1;
        transform: translateX(0); /* Settles at your original left alignment */
    }
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
>>>>>>> 10e0a95d6bad607f7f0e46b40a099e84fbe00752
</style> 

<section class="hero position-relative" 
    style="min-height: 65vh; 
           background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('<?= asset_url('images/tourismbackground.png') ?>'); 
           background-position: center; 
           background-size: cover; 
           background-repeat: no-repeat;
           display: flex;
           align-items: flex-end; /* Keeps content at the bottom */
           padding-bottom: 50px; /* Space from the bottom edge */"> 
    
    <!-- Changed margin-left to 0 and added minimal padding-left to push it to the far left safely -->
    <div class="container position-relative hero-text-animate" 
         style="text-align: left; margin-left: 0; padding-left: 25px; max-width: 100%;">
         
        <!-- Heading using Bungee Font -->
        <h1 class="display-3 fw-bold text-white mb-3" style="font-family: 'Bungee', sans-serif; letter-spacing: 1px; line-height: 1.1; text-shadow: 3px 3px 10px rgba(0,0,0,0.6);">
            LOKAL NA NEGOSYO,<br><span style="color: #ffda79;">LOKAL NA ASENSO</span>
        </h1>
        
        <!-- Tagline using Abril Fatface Font -->
        <p class="text-white mb-0" style="font-family: 'Abril Fatface', serif; font-size: 2.2rem; line-height: 1.2; text-shadow: 2px 2px 6px rgba(0,0,0,0.6); max-width: 750px;">
            Supporting entrepreneurs, <br>Building community livelihoods.
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
                <img src="assets/images/fruitstand.png" alt="Fruit Stand">
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
                <img src="assets/images/coastalcraft.png" alt="Coastal Crafts">
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
                <img src="assets/images/nativetouch.png" alt="Native Touch">
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
                <img src="assets/images/sweettreats.png" alt="Sweet Treats">
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

<<<<<<< HEAD
=======

>>>>>>> 10e0a95d6bad607f7f0e46b40a099e84fbe00752
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
            <span class="step-label">Step 1 – Prepare Your Information</span>
            <ul class="step-details">
                <li>Business Name & Category</li>
                <li>Address / Barangay</li>
                <li>Contact Number & Short Description</li>
                <li>Logo or Photo</li>
            </ul>
        </div>

        <div class="step-box">
            <span class="step-label">Step 2 – Submit Your Details</span>
            <ul class="step-details">
                <li><strong>Website:</strong> likhalokal.com</li>
                <li><strong>Email:</strong> talisayvinzons.directory@gmail.com</li>
                <li><strong>FB:</strong> LikhaLokal: Tuklas, Kultura, Kabuhayan</li>
            </ul>
        </div>

        <div class="step-box">
            <span class="step-label">Step 3 – Verification & Listing</span>
            <p class="lisu-description">
                Our team will review your submission. Approved businesses are added within 1–2 days.
            </p>
        </div>

        <div style="text-align: center;">
            <a href="register-business.php" button class="apply-button">APPLY BUSINESS</a>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>