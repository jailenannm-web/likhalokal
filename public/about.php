<?php
declare(strict_types=1);

$pageTitle = 'About Vinzons';
$activeNav = 'about';
require_once dirname(__DIR__) . '/bootstrap.php';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Google Fonts & Animate.css -->
<link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Bungee&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --vinzons-blue: #0077C2;
        --vinzons-dark-blue: #004A7C;
        --vinzons-amber: #FFBF00; /* Warm yellow-orange */
        --vinzons-white: #FFFFFF;
        --vinzons-black: #000000;
        --light-sky: #B3E5FC;
        
        /* Consistency Standards */
        --card-radius: 40px;
        --fs-h1: clamp(2.5rem, 8vw, 4.5rem);
        --fs-h2: 2.8rem;
        --fs-h3: 1.6rem;
        --fs-body: 1.05rem;
    }

    body {
        background: radial-gradient(circle, #E3F2FD 0%, var(--light-sky) 100%);
        background-attachment: fixed;
        color: var(--vinzons-black);
        font-size: var(--fs-body);
        line-height: 1.8;
    }

    /* Section Divider Design (Reference: image_93eaae.png) */
    .section-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 5rem 0;
        text-align: center;
    }

    .divider-line {
        flex-grow: 1;
        height: 10px;
        background-color: var(--vinzons-blue);
        border-radius: 3px;
        max-width: 480px;
    }

    .divider-content {
        padding: 0 2.5rem;
    }

    .divider-label {
        font-family: 'Bungee', cursive;
        font-size: 0.9rem;
        color: var(--vinzons-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: -5px;
    }

    /* Hero Banner */
    .hero-banner {
        background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('assets/images/aboutpic.png');
        background-size: cover;
        background-position: center;
        height: 65vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: var(--vinzons-white);
        margin-bottom: 4rem;
    }

    .hero-title { font-family: 'Abril Fatface', cursive; color: var(--vinzons-amber); font-size: var(--fs-h1); text-shadow: 4px 4px 8px rgba(0,0,0,0.6); }
    .hero-subtitle { font-family: 'Bungee', cursive; font-size: 1.2rem; letter-spacing: 4px; max-width: 700px; line-height: 1.4; }

    /* Heading & Text Standards */
    h1, .section-h1 { font-family: 'Abril Fatface', cursive; font-size: var(--fs-h1); }
    h2, .section-h2 { font-family: 'Abril Fatface', cursive; font-size: var(--fs-h2); line-height: 1.2; }
    h3, .section-h3 { font-family: 'Bungee', cursive; font-size: var(--fs-h3); }

    /* Consistent Card Logic */
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

    /* Governance Components */
    .gov-header-amber {
        background-color: var(--vinzons-amber);
        color: var(--vinzons-black);
        padding: 20px 35px;
        font-family: 'Bungee', cursive;
        border-radius: var(--card-radius) var(--card-radius) 0 0;
    }
    .gov-content {
        background-color: var(--vinzons-dark-blue);
        padding: 40px;
        border-radius: 0 0 var(--card-radius) var(--card-radius);
    }

    .leader-profile-img { 
        width: 100px; height: 100px; 
        border-radius: 50%; 
        border: 4px solid var(--vinzons-amber); 
        object-fit: cover; 
    }

    .badge-pill-custom {
        background: rgba(0, 119, 194, 0.12);
        border: 1.5px solid var(--vinzons-blue);
        color: var(--vinzons-blue);
        font-family: 'Bungee', cursive;
        padding: 12px 25px;
        border-radius: 50px;
        font-size: 0.8rem;
    }

    .text-amber { color: var(--vinzons-amber) !important; }
    .text-blue { color: var(--vinzons-blue) !important; }
    
    /* Scroll Animation Utilities */
    .reveal-on-scroll {
        opacity: 0;
        transition: all 0.8s ease;
    }
    .reveal-on-scroll.animated {
        opacity: 1;
    }
    
    /* Subtle Hover Upgrades */
    .unified-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
    }
</style>

<section class="hero position-relative" 
    style="min-height: 65vh; 
           background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?= asset_url('images/aboutpic.png') ?>'); 
           background-position: center; 
           background-size: cover; 
           background-repeat: no-repeat;
           display: flex;
           align-items: center;
           padding-bottom: 100px;"> 
    <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5 hero-text-animate">
        <h1 class="display-3 fw-bold text-white mb-2 animate__animated animate__fadeInDown" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4);">
            TUKLAS LAKBAY LOKAL </h1>
        <p class="text-white animate__animated animate__fadeInUp animate__delay-1s" style="font-family: 'Bungee', sans-serif; font-size: 1rem; color: #FFBF00 !important; text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7); letter-spacing: 1px; line-height: 1.3;">
            WHERE ANCIENT SPIRITS OF INDAN
            MEET THE PRISTINE WATERS OF THE PACIFIC.
        </p>
    </div>
</section>

<div class="container py-5">
    <!-- Comprehensive Story Section -->
    <div class="row align-items-center mb-5 pb-5">
        <div class="col-lg-6 pe-lg-5">
            <span class="badge bg-primary text-white mb-3 bungee-font px-4 py-2 reveal-on-scroll" data-animation="animate__fadeInDown" style="border-radius: 12px; background-color: var(--vinzons-blue) !important;">ESTABLISHED 1611</span>
            <h2 class="section-h2 text-blue mb-4 reveal-on-scroll" data-animation="animate__fadeInLeft">The Golden Legacy of <span class="text-amber">Indan</span></h2>
            <p class="mb-4 reveal-on-scroll" data-animation="animate__fadeInUp">Vinzons, formerly known as <strong>Indan</strong>, is more than just a dot on the map of Camarines Norte; it is a living chronicle of Filipino resilience and heritage. As one of the oldest towns in the province, its streets tell stories of Spanish friars, revolutionary heroes, and a community that has guarded its traditions for over four centuries.</p>
            <p class="mb-4 reveal-on-scroll" data-animation="animate__fadeInUp">Named after the legendary Wenceslao Q. Vinzons—the "Father of the Resistance" in the Philippines—our town embodies a spirit of bravery. From the intricate stone carvings of the St. Peter the Apostle Parish to the bustling local markets filled with the aroma of <em>Angol</em> and fresh seafood, every corner offers an invitation to slow down and rediscover the soul of Bicolano culture.</p>
        </div>
        <div class="col-lg-6 text-center">
            <img src="assets/images/vinzonschurch.png" alt="Heritage Church" class="img-fluid unified-card shadow-lg border border-5 border-white reveal-on-scroll" data-animation="animate__zoomIn">
        </div>
    </div>

    <!-- Longer Facts Section -->
    <div class="row g-4 mb-5 pb-5 text-center">
        <div class="col-md-4 reveal-on-scroll" data-animation="animate__fadeInUp" style="transition-delay: 0.1s;">
            <div class="unified-card glass-blue p-5 h-100 shadow">
                <i class="bi bi-shield-check mb-3 d-block" style="font-size: 2.5rem; color: var(--vinzons-amber);"></i>
                <h3 class="text-amber mb-3">HEROIC ROOTS</h3>
                <p>Honor the memory of Wenceslao Q. Vinzons, the youngest delegate to the 1935 Constitutional Convention and a fearless guerrilla leader whose sacrifice remains our town's greatest moral compass.</p>
            </div>
        </div>
        <div class="col-md-4 reveal-on-scroll" data-animation="animate__fadeInUp" style="transition-delay: 0.3s;">
            <div class="unified-card glass-blue p-5 h-100 shadow">
                <i class="bi bi-water mb-3 d-block" style="font-size: 2.5rem; color: var(--vinzons-amber);"></i>
                <h3 class="text-amber mb-3">CALAGUAS GATEWAY</h3>
                <p>Beyond the mainland lies the Calaguas Group of Islands. Home to the legendary "Mahabang Buhangin," our town manages these pristine ecosystems to ensure their beauty lasts for generations to come.</p>
            </div>
        </div>
        <div class="col-md-4 reveal-on-scroll" data-animation="animate__fadeInUp" style="transition-delay: 0.5s;">
            <div class="unified-card glass-blue p-5 h-100 shadow">
                <i class="bi bi-tree-fill mb-3 d-block" style="font-size: 2.5rem; color: var(--vinzons-amber);"></i>
                <h3 class="text-amber mb-3">GREEN HEART</h3>
                <p>Our expansive mangrove forests in the Vinzons Marshland serve as a vital nursery for marine life and a natural barrier against the tides, proving that progress and nature can coexist beautifully.</p>
            </div>
        </div>
    </div>

    <!-- Governance Section -->
    <div class="section-divider">
        <div class="divider-line reveal-on-scroll" data-animation="animate__fadeInLeft"></div>
        <div class="divider-content reveal-on-scroll" data-animation="animate__zoomIn">
            <div class="section-h2">Leadership & Governance</div>
            <h3 class="section-h3 text-amber mb-0">COMMITTED TO TRANSPARENCY, SUSTAINABILITY, AND GROWTH.</h3>
        </div>
        <div class="divider-line reveal-on-scroll" data-animation="animate__fadeInRight"></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6 reveal-on-scroll" data-animation="animate__fadeInLeft">
            <div class="unified-card">
                <div class="gov-header-amber">MUNICIPAL OFFICE</div>
                <div class="gov-content">
                    <div class="d-flex align-items-center mb-4">
                        <img src="mayor.jpg" class="leader-profile-img me-4" alt="Mayor">
                        <div>
                            <h3 class="text-amber h4 mb-1">HON. ELEANOR F. PAJARILLO</h3>
                            <p class="small text-white-50 mb-0">Local Chief Executive</p>
                        </div>
                    </div>
                    <p class="text-white small opacity-75">Leading with a vision of "Uswag Vinzons," our office focuses on community-driven development, social welfare programs, and the modernization of local infrastructure while preserving our unique cultural identity.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 reveal-on-scroll" data-animation="animate__fadeInRight">
            <div class="unified-card">
                <div class="gov-header-amber">TOURISM HUB</div>
                <div class="gov-content">
                    <div class="d-flex align-items-center mb-4">
                        <img src="tourismofficer.jpg" class="leader-profile-img me-4" alt="Tourism Officer">
                        <div>
                            <h3 class="text-amber h4 mb-1">GARY L. PAJARILLO</h3>
                            <p class="small text-white-50 mb-0">Municipal Tourism Officer</p>
                        </div>
                    </div>
                    <p class="text-white small opacity-75">Dedicated to promoting sustainable eco-tourism, our directorate ensures that every traveler leaves with a deep respect for our environment and a heart full of Bicolano hospitality.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Travel with Purpose Card - Aligned with Governance Card Margins -->
<div class="container reveal-on-scroll" data-animation="animate__fadeInUp">
    <!-- Travel with Purpose Card (Aligned with upper cards per image_930197.jpg) -->
    <div class="unified-card glass-effect p-4 mb-5 shadow-sm border-0">
        <div class="row align-items-center position-relative">
            <div class="col-md-9 px-4">
                <h2 class="section-h3 text-blue mb-2 reveal-on-scroll" data-animation="animate__fadeInLeft" style="font-family: 'Abril Fatface'">Travel with Purpose</h2>
                <p class="small mb-4 reveal-on-scroll" data-animation="animate__fadeInLeft">Vinzons is a fragile paradise. We invite you to discover our beauty responsibly—respect local customs, minimize waste, and support our community initiatives for a sustainable future.</p>
                
                <div class="d-flex flex-wrap gap-4 text-uppercase reveal-on-scroll" data-animation="animate__fadeInUp" style="font-size: 0.7rem; font-family: 'Bungee';">
                    <span><i class="bi bi-gear-fill me-1"></i> Eco-Friendly Tours</span>
                    <span><i class="bi bi-recycle me-1"></i> Zero Waste</span>
                    <span><i class="bi bi-person-fill me-1"></i> Local Empowerment</span>
                </div>
            </div>
            <!-- Heart background element from image_930197.jpg -->
            <div class="col-md-3 text-end d-none d-md-block" style="opacity: 0.1;">
                 <i class="bi bi-heart-fill" style="font-size: 6rem; color: var(--vinzons-blue);"></i>
            </div>
        </div>
    </div>
</div>

<!-- Intersection Observer Script for triggering animations on scroll -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.10
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const animationClass = element.getAttribute('data-animation');
                element.classList.add('animate__animated', animationClass, 'animated');
                observer.unobserve(element);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll').forEach(el => {
        observer.observe(el);
    });
});
</script>

<?php require BASE_PATH . '/includes/footer.php'; ?>