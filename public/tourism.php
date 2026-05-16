<?php
declare(strict_types=1);

$pageTitle = 'Tourism';
$activeNav = 'tourism';
require_once dirname(__DIR__) . '/bootstrap.php';

// Database query logic (kept from your base code)
$cat = $_GET['category'] ?? '';
$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM tourist_attractions WHERE status = 'published'";
$params = [];
if ($cat !== '') {
    $sql .= ' AND category = ?';
    $params[] = $cat;
}
if ($q !== '') {
    $sql .= ' AND (attraction_name LIKE ? OR description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY attraction_name ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Bungee&family=Inter:wght@400;700&display=swap" rel="stylesheet">

<style>

    
    :root {
        --vinzons-blue: #00468C;
        --vinzons-amber: #FFB300;
        --sky-gradient: radial-gradient(circle, #FFFFFF 0%, #B3E5FC 100%);
    }

    body {
        background: var(--sky-gradient);
        font-family: 'Inter', sans-serif;
    }

    /* --- NEW ANIMATION KEYFRAMES --- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }

    /* Scroll Reveal Initial State */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    /* Hero Banner Section */
    .hero-container {
        position: relative;
        background: url('assets/images/mountains-bg.jpg') no-repeat center center;
        background-size: cover;
        padding-top: 80px;
        padding-bottom: 250px; /* Space for overlapping images */
        text-align: left;
        color: white;
        animation: fadeInUp 1s ease-out; /* Added Animation */
    }

    .hero-text-wrapper {
        padding-left: 10%;
    }

    .hero-main-title {
        font-family: 'Bungee', cursive;
        font-size: 5rem;
        line-height: 0.9;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .hero-sub-tagline {
        font-family: 'Abril Fatface', serif;
        font-size: 1.8rem;
        font-style: italic;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    /* Overlapping 3 Pictures */
    .image-overlap-grid {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: -200px; /* Pulls images up into the hero section */
        position: relative;
        z-index: 10;
        padding: 0 5%;
    }

    .overlap-img {
        width: 30%;
        aspect-ratio: 4/5;
        object-fit: cover;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        background-color: #eee;
        animation: float 4s ease-in-out infinite; /* Added Floating Animation */
    }

    /* Staggered float timing */
    .overlap-img:nth-child(2) { animation-delay: 0.5s; }
    .overlap-img:nth-child(3) { animation-delay: 1s; }

    /* Transition Text */
    .transition-text {
        text-align: center;
        max-width: 800px;
        margin: 60px auto 40px;
        font-size: 1.1rem;
        color: #333;
        line-height: 1.6;
    }

    /* Discover More Divider */
    .discover-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 30px;
        margin-bottom: 50px;
    }

    .divider-line {
        height: 3px;
        background-color: var(--vinzons-blue);
        flex-grow: 1;
        max-width: 300px;
    }

    .discover-title-wrap {
        text-align: center;
    }

    .discover-small {
        font-family: 'Abril Fatface', serif;
        font-size: 1.5rem;
        color: var(--vinzons-blue);
        margin: 0;
    }

    .more-large {
        font-family: 'Bungee', cursive;
        font-size: 4rem;
        color: var(--vinzons-amber);
        line-height: 0.8;
        margin: 0;
    }

     /* Styling for the Glassmorphism Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.45); /* Semi-transparent white */
        backdrop-filter: blur(15px); /* The "Frosted" effect */
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1); /* Soft shadow */
        transition: transform 0.3s ease; /* Added Hover Transition */
    }

    .glass-card:hover {
        transform: scale(1.02);
    }

    .about-image-container img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 20px;
    }

    /* Read More Button styling */
    .btn-read-more {
        display: inline-flex;
        align-items: center;
        background-color: #051937; /* Dark blue matching your footer/nav */
        color: #FFFFFF;
        padding: 12px 28px;
        border-radius: 8px;
        text-decoration: none;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-read-more:hover {
        background-color: #00468C;
        transform: translateX(5px);
        color: #FFFFFF;
    }

    .tracking-widest {
        letter-spacing: 0.15em;
    }


    /* Google Font Imports */
    @import url('https://fonts.googleapis.com/css2?family=Bilbo+Swash+Caps&display=swap');

.bilbo-title {
        /* font details as requested */
        font-family: 'Bilbo Swash Caps', cursive;
        color: white;
        text-shadow: 2px 4px 10px rgba(0,0,0,0.5); 

        /* New constraints for placement and size */
        position: absolute;
        bottom: 2rem;
        left: 2rem;
        font-size: 1.5rem; /* much smaller fixed size */
        margin: 0;
        z-index: 20;
    }

    /* Wide Heritage Banner */
    .heritage-banner {
        height: 350px;
        background: url('assets/images/vinzons-heritage-bg.jpg') no-repeat center center;
        background-size: cover;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }

    .heritage-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    /* Info Card Glassmorphism */
    .glass-info-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        transition: transform 0.4s ease;
    }

    .info-side-img {
        height: 100%;
        min-height: 400px;
        background-size: cover;
        background-position: center;
        transition: transform 0.5s ease;
    }

    .glass-info-card:hover .info-side-img {
        transform: scale(1.03);
    }

    /* Text Adjustments */
    .text-primary { color: #00468C !important; }
    .font-abril { font-family: 'Abril Fatface', cursive; }
    .font-bungee { font-family: 'Bungee', cursive; letter-spacing: 0.05em; }


    /* Hero Section: Ragged Bottom */
.ragged-hero {
    background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('vinzons-bg.jpg');
    background-size: cover;
    background-position: center;
    height: 400px;
    position: relative;
    /* Ragged bottom shape */
    clip-path: polygon(0 0, 100% 0, 100% 90%, 85% 98%, 70% 92%, 50% 100%, 30% 91%, 15% 99%, 0 90%);
}

/* White Stroke for the Ragged Edge */
.ragged-white-stroke {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-bottom: 6px solid white;
    pointer-events: none;
    clip-path: polygon(0 89%, 15% 98%, 30% 90%, 50% 99%, 70% 91%, 85% 97%, 100% 89%, 100% 91%, 85% 99%, 70% 93%, 50% 100%, 30% 92%, 15% 100%, 0 91%);
}

/* Overlapping Image */
.overlap-adjustment {
    margin-top: -100px; /* Pulls image up */
    position: relative;
    z-index: 10;
}

.island-frame {
    background: white;
    padding: 10px;
    border-radius: 20px;
    max-width: 90%; /* Makes the picture smaller as requested */
}

.island-frame img {
    height: 450px;
    width: 100%;
    object-fit: cover;
    border-radius: 15px;
}

/* Hero Text Placement */
.hero-brand {
    position: absolute;
    top: 25%;
    left: 15px;
    font-family: 'Abril Fatface', serif;
    color: #FFC107;
    font-size: 3.5rem;
}

.hero-quote {
    position: absolute;
    bottom: 50px;
    right: 20px;
    color: white;
    max-width: 400px;
    text-align: right;
    font-size: 0.9rem;
}

/* Content Typography */
.island-title { font-family: 'Abril Fatface', serif; color: #053921; }
.font-bungee { font-family: 'Bungee', cursive; text-transform: uppercase; font-size: 0.8rem; }


/* Container section with the background image */
.hero-section {
  position: relative;
  width: 100%;
  min-height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 50px 20px;
  overflow: hidden;
  /* Replace with your actual background image */
  background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), 
              url('background-beach.jpg') no-repeat center center;
  background-size: cover;
}

/* Optional: Adding a slight blur to the background only */
.hero-section::before {
  content: "";
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  backdrop-filter: blur(8px); /* Adjust blur strength here */
  z-index: 1;
}

/* The container for the 4 images */
.cards-container {
  position: relative;
  z-index: 2;
  display: flex;
  gap: 45px; /* Space between cards */
  max-width: 1200px;
  width: 100%;
  justify-content: center;
}

/* Individual Card Styling */
.card {
  width: 250px;
  height: 350px;
  border-radius: 15px; /* Rounded corners */
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); /* The floating shadow effect */
  transition: transform 0.3s ease;
}

/* Hover effect to make it feel interactive */
.card:hover {
  transform: translateY(-10px);
}

/* Image inside the card */
.card img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ensures image fills the card without stretching */
  display: block;
}

.top-divider {
  width: 100%;
  height: 8px;              /* Thickness of the line */
  background-color: #5b9bd5; /* The blue color from the image */
  border-radius: 4px;       /* Makes the ends slightly rounded */
  margin-bottom: 30px;      /* Space between the line and the title */
  box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Optional: slight depth */
}

.title {
  color: #003300;           /* Dark green color for the text */
  font-weight: 900;
  font-size: 32px;
  margin-bottom: 30px;
  letter-spacing: 1px;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(to bottom, #86cfea, #bce7f8);
  margin: 0;

}


.title {
  color: var(--text-dark);
  font-weight: 900;
  font-size: 28px;
  margin-bottom: 5px;
}

.underline {
  height: 4px;
  width: 100%;
  background-color: var(--accent-blue);
  margin-bottom: 40px;
  border-radius: 2px;
}

/* Grid Layout */
.agencies-grid {
  display: grid;
  grid-template-columns: 1fr 1fr; /* Two equal columns */
  gap: 20px;
}

/* Card Styling */
.agency-card {
  background-color: var(--card-bg);
  border: 1px solid rgba(0,0,0,0.05);
  border-radius: 20px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  transition: all 0.3s ease; /* Added Transition */
}

.agency-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Logo container */
.logo-box {
  background-color: #d1d1d1;
  min-width: 80px;
  height: 80px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.agency-card:hover .logo-box {
    transform: rotate(-5deg);
}

.logo-box img {
  width: 50px;
  height: auto;
  opacity: 0.7;
}

/* Text Content */
.agency-info h3 {
  margin: 0 0 8px 0;
  color: var(--text-dark);
  font-size: 1.1rem;
}

.agency-info p {
  margin: 0 0 10px 0;
  font-size: 0.85rem;
  color: #333;
  line-height: 1.4;
}


.msg-btn {
  background: none;
  border: none;
  color: #444;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
  text-decoration: none;
}

.msg-btn:hover {
  text-decoration: underline;
  color: var(--text-dark);
}
.agency-section {
    width: 100%;
    /* Creates a safety buffer so cards never touch the footer */
    padding-bottom: 80px; 
    margin-top: 50px;
    position: relative;
    z-index: 1;
}

/* Updated Blue Divider */
.top-divider {
    width: 0%; /* Initial state for animation */
    height: 8px;
    background-color: #5b9bd5;
    border-radius: 4px;
    margin-bottom: 40px;
    transition: width 1.2s ease-in-out;
}

.reveal.active .top-divider {
    width: 100%;
}

.content-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40px;
}

.title {
    color: #003300;
    font-weight: 900;
    font-size: 28px;
    margin-bottom: 30px;
    text-transform: uppercase;
}

/* Fixed Grid: Prevents height collapse */
.agencies-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: stretch; /* Ensures cards in the same row have equal height */
}

/* Agency Card: Matched to your screenshot */
.agency-card {
    background: rgba(255, 255, 255, 0.4); /* Glass effect from image */
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 25px;
    display: flex;
    align-items: center; /* Vertically centers content - FIXES OVERLAP LOOK */
    gap: 20px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.agency-card:hover {
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.6);
}

.logo-box {
    background-color: #D9D9D9; /* Matching the grey box in your image */
    min-width: 100px;
    height: 100px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.agency-info h3 {
    margin: 0 0 5px 0;
    color: #1a1a1a;
    font-size: 1.2rem;
    font-weight: 700;
}

.agency-info p {
    margin: 0 0 10px 0;
    font-size: 0.9rem;
    color: #444;
    line-height: 1.4;
}

/* Contact Row matching your 0912... Message Now style */
.contact-row {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a1a;
}

.msg-btn {
    font-weight: 400;
    font-size: 0.85rem;
    color: #666;
    text-decoration: none;
    margin-left: 8px;
}

.msg-btn:hover {
    text-decoration: underline;
    color: var(--vinzons-blue);
}

</style>



<section class="hero position-relative" 
    style="min-height: 65vh; 
           background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?= asset_url('images/tourismbackground.png') ?>'); 
           background-position: center; 
           background-size: cover; 
           background-repeat: no-repeat;
           display: flex;
           align-items: center;
           padding-bottom: 100px;"> 
           <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5 hero-text-animate">
        <h1 class="display-3 fw-bold text-white mb-2" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4);">
            TUKLAS LAKBAY<br><span style="color: #ffda79;">LOKAL</span>
        </h1>
        <p class="text-white" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">
            Biyahe, Kwento, at Karanasan sa Vinzons
        </p>
    </div>
</section>

<section class="overlap-section container" style="margin-top: -100px; position: relative; z-index: 10;">
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <img src="assets/images/St. Peter Church.png" alt="St. Peter Church" style="width: 100%; height: 400px; object-fit: cover;">
            </div>
        </div>
        <div class="col-md-4">
            <div class="shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <img src="assets/images/calaguas.png" alt="Calaguas Islands" style="width: 100%; height: 400px; object-fit: cover;">
            </div>
        </div>
        <div class="col-md-4">
            <div class="shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <img src="assets/images/vinzonsriver.png" alt="Vinzons River" style="width: 100%; height: 400px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<div class="container">
    <p class="transition-text">
        From heritage trails to pristine islands and farm escapes—discover the beauty of Vinzons, crafted by nature and shaped by culture.
    </p>

    <div class="discover-divider">
        <div class="divider-line"></div>
        <div class="discover-title-wrap">
            <p class="discover-small">Discover</p>
            <h2 class="more-large">MORE</h2>
        </div>
        <div class="divider-line"></div>
    </div>
</div>

<div class="container my-5">
    <div class="glass-card">
        <div class="row align-items-center g-0">
            <div class="col-md-5 p-4">
                <div class="about-image-container">
                    <img src="assets/images/vinzons.png" alt="Vinzons, Camarines Norte" class="img-fluid rounded-4 shadow-sm">
                </div>
            </div>
            
            <div class="col-md-7 p-5">
                <div class="about-text-content">
                    <span class="text-uppercase tracking-widest text-muted small font-bungee">ABOUT US</span>
                    <h2 class="display-5 fw-bold font-abril mt-2 mb-4">Vinzons, <span style="color: #00468C;">Camarines Norte</span></h2>
                    
                    <p class="lead text-secondary mb-4" style="font-family: 'Inter', sans-serif; line-height: 1.8;">
                        Vinzons is well known for its historical importance, pristine islands, and eco-tourism. 
                        Formerly called <strong>Indan</strong>, it features a unique blend of history and adventure—from 
                        the home of the Vinzons’ Marsh and Mangrove Forest to gateways to the famous Calaguas Islands.
                    </p>
                    
                    <a href="about.php" class="btn-read-more">
                        Read more 
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="heritage-card shadow-lg">
        <div class="heritage-image-container">
            <img src="assets/images/kasaysayan.png"  class="heritage-img">
            
            <h2 class="heritage-bottom-left-text">Puso ng Kasaysayan, Likha ng Kalikasan</h2>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="glass-info-card p-0 overflow-hidden">
        <div class="row g-0">
            <div class="col-lg-7 p-5">
                <div class="mb-4">
                    <span class="font-bungee text-warning small">HISTORICAL LANDMARK</span>
                    <h2 class="font-abril display-6 mt-2">Wenceslao Q. Vinzons</h2>
                    <p class="text-muted mt-3" style="font-family: 'Inter', sans-serif;">
                        A declared National Historical Landmark, functions as a public shrine, museum, and library showcasing the life and memorabilia of the World War II hero.
                    </p>
                    <p class="fw-bold text-dark">Entrance Fee: <span class="text-success">Php 00.00</span></p>
                    <a href="https://maps.google.com/?q=Wenceslao+Vinzons+Shrine" target="_blank" class="text-primary fw-bold text-decoration-none font-bungee small">
                        SEE DIRECTION <i class="bi bi-geo-alt-fill"></i>
                    </a>
                </div>

                <hr class="my-4 opacity-10">

                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h4 class="font-bungee h6 text-primary">How to get there?</h4>
                        <p class="small text-secondary mb-0">
                            Take a public jeepney or tricycle to the town of Vinzons, a trip of approximately 20-30 minutes. The landmark is easily recognizable once you are in the town proper.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h4 class="font-bungee h6 text-primary">Best time to visit?</h4>
                        <p class="small text-secondary mb-0">
                            The shrine is open year-round, so the best time to visit is during the dry season, from December to June, for more predictable weather conditions.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-side-img" style="background-image: url('assets/images/vinzons map.png');"></div>
            </div>
        </div>
    </div>
</div>


<div class="ragged-hero">
    <div class="ragged-bg-container">
        <img src="assets/images/vinzons2.png"  class="hero-bg-img">
        
        <div class="ragged-white-stroke"></div>

        <div class="container h-100 position-relative d-flex flex-column justify-content-center">
            <h1 class="hero-brand">Vinzons</h1>
            <p class="hero-quote">
                Home to historical landmarks, eco-adventures, and gateways to stunning islands, 
                Vinzons invites you to discover stories carved in nature and history.
            </p>
        </div>
    </div>
</div>

<div class="container overlap-adjustment pb-5">
    <div class="row">
        <div class="col-lg-5">
            <div class="island-frame shadow-lg">
                <img src="assets/images/calaguasisland.png" alt="Calaguas Island" class="img-fluid">
            </div>
        </div>

        <div class="col-lg-7 pt-lg-5 mt-lg-4">
            <h1 class="island-title">Calaguas Island, Vinzons</h1>
            <p class="island-text">
                A pristine group of islands, renowned for its stunning natural beauty and unspoiled environment. 
                The primary attraction is <strong>Mahabang Buhangin Beach</strong>, which features a long stretch 
                of powdery white sand and crystal-clear turquoise waters.
            </p>
            
            <div class="link-box mb-4">
                <p class="mb-1 fw-bold small"><i class="bi bi-tag-fill"></i> Entrance Fee</p>
                <a href="#" class="text-dark small fw-bold text-decoration-underline"><i class="bi bi-geo-alt-fill"></i> See Directions</a>
            </div>

            <div class="travel-details">
                <h4 class="font-bungee h6">How to get there?</h4>
                <p class="small text-muted">
                    Travel overland to ports in Camarines Norte (Vinzons or Paracale), then take a 2-3 hour boat ride.
                </p>

                <h4 class="font-bungee h6 mt-3">Best time to Visit</h4>
                <p class="small text-muted">
                    Visit during the dry season (March–June) for calm seas, ideal for activities like stargazing and trekking.
                </p>
            </div>
        </div>
    </div>
</div>

<section class="hero-section">
  <div class="cards-container">
    <div class="card">
      <img src="assets/images/historical.png" alt="Historical Marker">
    </div>
    <div class="card">
      <img src="assets/images/vinzonshouse.png" alt="Vinzons House">
    </div>
    <div class="card">
      <img src="assets/images/boat.png" alt="Beach Boat">
    </div>
    <div class="card">
      <img src="assets/images/crystalkayak.jpg" alt="Clear Kayak">
    </div>
  </div>
</section>


<section class="agency-section">
  <div class="top-divider"></div>
  
  <div class="content-container">
    <h2 class="title">LIST OF TRAVEL AGENCIES</h2>
    
    <div class="agencies-grid">
      <div class="agency-card">
        <div class="logo-box">
          <img src="Baybreeze.png" alt="Logo">
        </div>
        <div class="agency-info">
          <h3>Baybreeze Escapes</h3>
          <p>Island-hopping specialists offering budget and premium tours to Calaguas and nearby beaches.</p>
          <span class="phone">09123456879</span>
          <button class="msg-btn">Message now</button>
        </div>
      </div>

      <div class="agency-card">
        <div class="logo-box">
          <img src="sunriseshorer.png" alt="Logo">
        </div>
        <div class="agency-info">
          <h3>Sunrise Shores Travel Co.</h3>
          <p>Specializes in sunrise beach photography tours, snorkeling, and coastal sightseeing.</p>
          <span class="phone">09123456879</span>
          <button class="msg-btn">Message now</button>
        </div>
      </div>

      <div class="agency-card">
        <div class="logo-box">
          <img src="buhanginvoyages.png" alt="Logo">
        </div>
        <div class="agency-info">
          <h3>Mahabang Buhangin Voyages</h3>
          <p>Offers kayak tours, boat rides, and eco-trips around the San Nicolas Mangrove Forest.</p>
          <span class="phone">09123456879</span>
          <button class="msg-btn">Message now</button>
        </div>
      </div>

      <div class="agency-card">
        <div class="logo-box">
          <img src="trailadventures.png" alt="Logo">
        </div>
        <div class="agency-info">
          <h3>Mangrove Trail Adventures</h3>
          <p>Offers kayak tours, boat rides, and eco-trips around the San Nicolas Mangrove Forest.</p>
          <span class="phone">09123456879</span>
          <button class="msg-btn">Message now</button>
        </div>
      </div>

      <div class="agency-card">
        <div class="logo-box">
          <img src="islanorte.png" alt="Logo">
        </div>
        <div class="agency-info">
          <h3>Isla Norte Backpacking Tours</h3>
          <p>Affordable student and backpacker packages to Calaguas, Quinamanukan, and lesser-known beaches.</p>
          <span class="phone">09123456879</span>
          <button class="msg-btn">Message now</button>
        </div>
      </div>

      <div class="agency-card">
        <div class="logo-box">
          <img src="greencoast.png" alt="Logo">
        </div>
        <div class="agency-info">
          <h3>Green Coast Expeditions</h3>
          <p>Local community tour group providing nature trekking, fishing trips, and waterfall adventures.</p>
          <span class="phone">09123456879</span>s
          <button class="msg-btn">Message now</button>
        </div>
      </div>
    </div> </div> </section>

    <?php require BASE_PATH . '/includes/footer.php'; ?>

    <script>
document.addEventListener("DOMContentLoaded", function() {
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    // Apply to any element with 'reveal' class
    document.querySelectorAll('.agency-section, .glass-card, .discover-divider').forEach(el => {
        el.classList.add('reveal');
        observer.observe(el);
    });
});
</script>