<?php
declare(strict_types=1);

$pageTitle = 'Tourism';
$activeNav = 'tourism';
require_once dirname(__DIR__) . '/bootstrap.php';

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
$featuredAttraction = $list[0] ?? null;
$heritageAttraction = null;
$islandAttraction = null;
foreach ($list as $row) {
    if ($heritageAttraction === null && in_array($row['category'], ['heritage_site', 'church', 'landmark', 'museum', 'cultural_site'], true)) {
        $heritageAttraction = $row;
    }
    if ($islandAttraction === null && in_array($row['category'], ['island', 'beach', 'eco_tourism'], true)) {
        $islandAttraction = $row;
    }
}
$heritageAttraction ??= $featuredAttraction;
$islandAttraction ??= ($list[1] ?? $featuredAttraction);

$agencies = db()->query(
    "SELECT * FROM businesses WHERE status = 'approved' AND business_type = 'travel_agency' ORDER BY business_name ASC"
)->fetchAll();

$spotlight = db()->query(
    "SELECT * FROM tourist_attractions WHERE status = 'published' AND image IS NOT NULL AND image != '' ORDER BY FIELD(id, 1, 3, 7), id ASC LIMIT 3"
)->fetchAll();
if (count($spotlight) < 3) {
    $have = array_column($spotlight, 'id');
    $placeholders = $have ? implode(',', array_fill(0, count($have), '?')) : '0';
    $extra = db()->prepare(
        "SELECT * FROM tourist_attractions WHERE status = 'published' AND id NOT IN ($placeholders) ORDER BY id ASC LIMIT " . (3 - count($spotlight))
    );
    $extra->execute($have ?: []);
    $spotlight = array_merge($spotlight, $extra->fetchAll());
}

$heritageRow = db()->query(
    "SELECT * FROM cultural_information WHERE status = 'published' AND category = 'heritage' ORDER BY id ASC LIMIT 1"
)->fetch();
$heritageImage = media_url($heritageRow['image'] ?? null, asset_url('images/kasaysayan.png'));

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
        font-family: 'Montserrat', sans-serif;
        padding-top: 0 !important; /* Eliminate white line space under the navbar */
    } jubilee-fixed { padding-left: 15px; padding-right: 15px; }

    /* ============================================
       ANIMATION KEYFRAMES
       ============================================ */

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-50px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(50px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.85); }
        to   { opacity: 1; transform: scale(1); }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-15px); }
    }

    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }

    @keyframes lineExpand {
        from { transform: scaleX(0); }
        to   { transform: scaleX(1); }
    }

    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(0, 70, 140, 0.3); }
        50%       { box-shadow: 0 0 20px 8px rgba(0, 70, 140, 0.1); }
    }

    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    @keyframes bounce-subtle {
        0%, 100% { transform: translateY(0); }
        40%       { transform: translateY(-6px); }
        60%       { transform: translateY(-3px); }
    }

    @keyframes flicker {
        0%, 100% { opacity: 1; }
        92%       { opacity: 1; }
        93%       { opacity: 0.85; }
        94%       { opacity: 1; }
    }

    @keyframes hero-pan {
        0%   { background-position: center 55%; }
        50%  { background-position: center 45%; }
        100% { background-position: center 55%; }
    }

    @keyframes card-drift {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        33%       { transform: translateY(-8px) rotate(0.5deg); }
        66%       { transform: translateY(-4px) rotate(-0.5deg); }
    }

    @keyframes text-shimmer {
        0%   { background-position: 0% center; }
        100% { background-position: 200% center; }
    }

    /* ============================================
       SCROLL REVEAL
       ============================================ */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.75s ease, transform 0.75s ease;
    }
    .reveal.reveal-left  { transform: translateX(-45px); }
    .reveal.reveal-right { transform: translateX(45px); }
    .reveal.reveal-scale { transform: scale(0.88); }
    .reveal.reveal-fade  { transform: none; }

    .reveal.active {
        opacity: 1 !important;
        transform: none !important;
    }

    /* Stagger helpers */
    .stagger .reveal:nth-child(1) { transition-delay: 0.05s; }
    .stagger .reveal:nth-child(2) { transition-delay: 0.18s; }
    .stagger .reveal:nth-child(3) { transition-delay: 0.31s; }
    .stagger .reveal:nth-child(4) { transition-delay: 0.44s; }

    /* ============================================
       HERO
       ============================================ */
    .hero-text-animate h1 {
        animation: fadeInLeft 1s ease both;
    }
    .hero-text-animate p {
        animation: fadeInLeft 1s ease 0.25s both;
    }

    /* Subtle slow pan on hero bg */
    .hero-animate-bg {
        animation: hero-pan 18s ease-in-out infinite;
    }

    /* Floating particles */
    .hero-particles {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
        z-index: 1;
    }
    .hero-particles span {
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        animation: float var(--dur, 6s) ease-in-out infinite;
        animation-delay: var(--delay, 0s);
    }

    /* ============================================
       OVERLAP IMAGES
       ============================================ */
    .overlap-img-wrapper {
        transition: transform 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                    box-shadow 0.45s ease;
        border-radius: 20px;
        overflow: hidden;
    }
    .overlap-img-wrapper:hover {
        transform: translateY(-14px) scale(1.03);
        box-shadow: 0 30px 60px rgba(0,0,0,0.25) !important;
        z-index: 5;
    }
    .overlap-img-wrapper img {
        transition: transform 0.5s ease;
    }
    .overlap-img-wrapper:hover img {
        transform: scale(1.07);
    }

    /* Staggered float on the 3 hero images */
    .float-img-1 { animation: card-drift 5s ease-in-out infinite; }
    .float-img-2 { animation: card-drift 5s ease-in-out 0.8s infinite; }
    .float-img-3 { animation: card-drift 5s ease-in-out 1.6s infinite; }

    /* ============================================
       TRANSITION TEXT
       ============================================ */
    .transition-text {
        text-align: center;
        max-width: 800px;
        margin: 60px auto 40px;
        font-size: 1.1rem;
        color: #333;
        line-height: 1.6;
    }

    /* ============================================
       DISCOVER DIVIDER
       ============================================ */
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
        transform-origin: left center;
        transform: scaleX(0);
        transition: transform 0.9s ease;
    }
    .divider-line.line-right { transform-origin: right center; }
    .discover-divider.active .divider-line { transform: scaleX(1); }

    .discover-title-wrap { text-align: center; }

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
        animation: flicker 7s infinite;
        background: linear-gradient(90deg, var(--vinzons-amber), #ffd966, var(--vinzons-amber));
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: text-shimmer 4s linear infinite;
    }

    /* ============================================
       GLASS CARD (About section)
       ============================================ */
    .glass-card {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .glass-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 50px rgba(31, 38, 135, 0.15);
    }

    .about-image-container img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 20px;
        transition: transform 0.5s ease;
    }
    .glass-card:hover .about-image-container img {
        transform: scale(1.04);
    }

    /* ============================================
       READ MORE BUTTON
       ============================================ */
    .btn-read-more {
        display: inline-flex;
        align-items: center;
        background-color: #051937;
        color: #FFFFFF;
        padding: 12px 28px;
        border-radius: 8px;
        text-decoration: none;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .btn-read-more::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: skewX(-20deg);
        transition: left 0.5s ease;
    }
    .btn-read-more:hover::before { left: 160%; }
    .btn-read-more:hover {
        background-color: #00468C;
        transform: translateX(5px);
        color: #FFFFFF;
    }

    .tracking-widest { letter-spacing: 0.15em; }

    @import url('https://fonts.googleapis.com/css2?family=Bilbo+Swash+Caps&display=swap');

    .bilbo-title {
        font-family: 'Bilbo Swash Caps', cursive;
        color: white;
        text-shadow: 2px 4px 10px rgba(0,0,0,0.5);
        position: absolute;
        bottom: 2rem;
        left: 2rem;
        font-size: 1.5rem;
        margin: 0;
        z-index: 20;
    }

    /* ============================================
       HERITAGE BANNER
       ============================================ */
    .heritage-banner {
        height: 350px;
        background: url('assets/images/vinzons-heritage-bg.jpg') no-repeat center center;
        background-size: cover;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.5s ease;
    }
    .heritage-banner:hover {
        transform: scale(1.01);
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

    /* ============================================
       GLASS INFO CARD (WQ Vinzons section)
       ============================================ */
    .glass-info-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .glass-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
    }

    .info-side-img {
        height: 100%;
        min-height: 400px;
        background-size: cover;
        background-position: center;
        transition: transform 0.6s ease;
    }
    .glass-info-card:hover .info-side-img {
        transform: scale(1.04);
    }

    /* ============================================
       TEXT UTILITIES
       ============================================ */
    .text-primary { color: #00468C !important; }
    .font-abril  { font-family: 'Abril Fatface', cursive; }
    .font-bungee { font-family: 'Bungee', cursive; letter-spacing: 0.05em; }

    /* ============================================
       RAGGED HERO
       ============================================ */
    .ragged-hero {
        background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('vinzons-bg.jpg');
        background-size: cover;
        background-position: center;
        height: 400px;
        position: relative;
        clip-path: polygon(0 0, 100% 0, 100% 90%, 85% 98%, 70% 92%, 50% 100%, 30% 91%, 15% 99%, 0 90%);
    }

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

    .overlap-adjustment {
        margin-top: -100px;
        position: relative;
        z-index: 10;
    }

    .island-frame {
        background: white;
        padding: 10px;
        border-radius: 20px;
        max-width: 90%;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .island-frame:hover {
        transform: translateY(-8px) rotate(0.5deg);
        box-shadow: 0 24px 50px rgba(0,0,0,0.18) !important;
    }

    .island-frame img {
        height: 450px;
        width: 100%;
        object-fit: cover;
        border-radius: 15px;
        transition: transform 0.5s ease;
    }
    .island-frame:hover img { transform: scale(1.04); }

    .hero-brand {
        position: absolute;
        top: 25%;
        left: 25px;
        font-family: 'Abril Fatface', serif;
        color: #FFC107;
        font-size: 3.5rem;
        animation: fadeInLeft 1s ease both;
    }

    .hero-quote {
        position: absolute;
        bottom: 50px;
        right: 25px;
        color: white;
        max-width: 400px;
        text-align: right;
        font-size: 0.9rem;
        animation: fadeInRight 1s ease 0.3s both;
    }

    .island-title { font-family: 'Abril Fatface', serif; color: #053921; }
    .font-bungee  { font-family: 'Bungee', cursive; text-transform: uppercase; font-size: 0.8rem; }

    /* ============================================
       HERO SECTION (4-card blur section)
       ============================================ */
    .hero-section {
        position: relative;
        width: 100%;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 4%;
        overflow: hidden;
        background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), 
                    url('background-beach.jpg') no-repeat center center;
        background-size: cover;
    }

    @media (max-width: 768px) {
        .hero-section {
            padding: 50px 15px;
        }
    }

    .hero-section::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        backdrop-filter: blur(8px);
        z-index: 1;
    }

    .cards-container {
        position: relative;
        z-index: 2;
        display: flex;
        flex-wrap: wrap;
        gap: 28px;
        max-width: 1200px;
        width: 100%;
        justify-content: center;
    }

    .card {
        width: 250px;
        height: 350px;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        position: relative;
        border: 0;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                    box-shadow 0.4s ease;
    }

    .card:hover {
        transform: translateY(-16px) scale(1.04);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.35);
    }

    .card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.5s ease;
    }
    .card:hover img { transform: scale(1.1); }

    .attraction-card-title {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 2;
        color: #fff;
        padding: 2.75rem 1rem 1rem;
        background: linear-gradient(transparent, rgba(0, 31, 63, 0.92));
        font-family: 'Inter', sans-serif;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.9rem;
    }

    /* Staggered float on the 4 showcase cards */
    .cards-container .card:nth-child(1) { animation: card-drift 5s ease-in-out infinite; }
    .cards-container .card:nth-child(2) { animation: card-drift 5s ease-in-out 0.7s infinite; }
    .cards-container .card:nth-child(3) { animation: card-drift 5s ease-in-out 1.4s infinite; }
    .cards-container .card:nth-child(4) { animation: card-drift 5s ease-in-out 2.1s infinite; }

    .card:hover { animation: none; }

    /* ============================================
       AGENCY SECTION
       ============================================ */
    .top-divider {
        width: 100%;
        height: 8px;
        background-color: #5b9bd5;
        border-radius: 4px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transform-origin: left center;
        transform: scaleX(0);
        transition: transform 1.2s ease-in-out;
    }
    .top-divider.active { transform: scaleX(1); }

    .title {
        color: #003300;
        font-weight: 900;
        font-size: 28px;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .agency-section {
        width: 100%;
        padding-bottom: 80px;
        margin-top: 50px;
        position: relative;
        z-index: 1;
    }

    .content-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4%;
    }

    @media (max-width: 768px) {
        .content-container {
            padding: 0 15px;
        }
    }

    .agencies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        align-items: stretch;
    }

    .agency-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 6px 20px rgba(0, 70, 140, 0.03);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .agency-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(0, 70, 140, 0.08);
        border-color: rgba(0, 70, 140, 0.12);
    }

    .agency-card-banner {
        width: 100%;
        height: 100px;
        background-size: cover;
        background-position: center;
        position: relative;
        border-bottom: 3px solid var(--vinzons-amber);
    }

    .agency-card-logo {
        position: absolute;
        bottom: -20px;
        left: 15px;
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        border: 2px solid #ffffff;
        z-index: 2;
    }

    .agency-card-logo img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .agency-card-body {
        padding: 28px 16px 16px 16px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        text-align: left;
    }

    .agency-card-badge {
        display: inline-block;
        font-size: 0.58rem;
        font-weight: 800;
        color: var(--vinzons-blue);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }

    .agency-card-body h3 {
        margin: 0 0 4px 0;
        color: var(--vinzons-blue);
        font-size: 0.92rem;
        font-weight: 850;
        line-height: 1.2;
        font-family: 'Montserrat', sans-serif;
    }
    
    .agency-card-body h3 a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }

    .agency-card-body h3 a:hover {
        color: var(--vinzons-amber);
    }

    .agency-card-body p {
        margin: 0 0 10px 0;
        font-size: 0.72rem;
        color: #555;
        line-height: 1.4;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .agency-card-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        padding-top: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .agency-card-phone {
        font-size: 0.7rem;
        font-weight: 700;
        color: #666;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .agency-card-phone i {
        color: var(--vinzons-amber);
    }

    .agency-msg-btn {
        background-color: var(--vinzons-blue);
        color: #ffffff !important;
        border: none;
        border-radius: 50px;
        padding: 4px 12px;
        font-size: 0.68rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 70, 140, 0.15);
    }

    .agency-msg-btn:hover {
        background-color: var(--vinzons-amber);
        color: var(--vinzons-black) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(255, 179, 0, 0.25);
    }

    /* ============================================
       HERITAGE CARD (kasaysayan image section)
       ============================================ */
    .heritage-card {
        border-radius: 20px;
        overflow: hidden;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .heritage-card:hover {
        transform: scale(1.01);
        box-shadow: 0 24px 50px rgba(0,0,0,0.15) !important;
    }

    .heritage-image-container {
        position: relative;
        overflow: hidden;
    }

    .heritage-img {
        width: 100%;
        display: block;
        transition: transform 0.6s ease;
    }
    .heritage-card:hover .heritage-img { transform: scale(1.04); }

    .heritage-bottom-left-text {
        position: absolute;
        bottom: 20px;
        left: 20px;
        color: white;
        font-family: 'Bilbo Swash Caps', cursive;
        font-size: 2rem;
        text-shadow: 2px 3px 8px rgba(0,0,0,0.6);
        margin: 0;
        animation: fadeInLeft 1s ease both;
    }

    /* ============================================
       TRAVEL DETAIL LINKS
       ============================================ */
    .link-box a, .island-text a {
        transition: color 0.3s ease, letter-spacing 0.3s ease;
    }
    .link-box a:hover {
        color: var(--vinzons-blue) !important;
        letter-spacing: 0.5px;
    }

    /* Col-header animated border */
    .col-header-animated {
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 35px;
        display: inline-block;
    }
    .col-header-animated::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0;
        height: 4px;
        width: 0;
        background: linear-gradient(90deg, var(--vinzons-amber), #ffd966);
        transition: width 0.8s ease;
        border-radius: 2px;
    }
    .col-header-animated.active::after { width: 100%; }

    /* Page fade-in */
    body {
        opacity: 0;
        transition: opacity 0.5s ease;
    }

</style>


<!-- HERO SECTION -->
<section class="hero position-relative hero-animate-bg" 
    style="min-height: 65vh; 
           background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?= asset_url('images/tourismbackground.png') ?>'); 
           background-position: center; 
           background-size: cover; 
           background-repeat: no-repeat;
           display: flex;
           align-items: center;
           padding-bottom: 100px;
           overflow: hidden;"> 

    <!-- Floating particles -->
    <div class="hero-particles">
        <span style="left:8%;top:20%;--dur:7s;--delay:0s;width:9px;height:9px;"></span>
        <span style="left:22%;top:65%;--dur:5s;--delay:1.2s;width:6px;height:6px;"></span>
        <span style="left:45%;top:30%;--dur:8s;--delay:0.4s;width:11px;height:11px;opacity:0.12;"></span>
        <span style="left:65%;top:72%;--dur:6s;--delay:2s;width:7px;height:7px;"></span>
        <span style="left:80%;top:22%;--dur:9s;--delay:1.6s;width:8px;height:8px;"></span>
        <span style="left:35%;top:82%;--dur:6.5s;--delay:0.9s;width:5px;height:5px;"></span>
        <span style="left:90%;top:50%;--dur:7.5s;--delay:3s;width:10px;height:10px;opacity:0.1;"></span>
        <span style="left:15%;top:45%;--dur:5.5s;--delay:0.3s;width:4px;height:4px;"></span>
    </div>

    <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5 hero-text-animate" style="z-index:2; padding-left: 25px; padding-right: 25px;">
        <h1 class="display-3 fw-bold text-white mb-2" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4);">
            TUKLAS LAKBAY<br><span style="color: #ffda79;">LOKAL</span>
        </h1>
        <p class="text-white" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">
            Biyahe, Kwento, at Karanasan sa Vinzons
        </p>
    </div>
</section>

<!-- OVERLAP IMAGES -->
<section class="overlap-section container stagger" style="margin-top: -100px; position: relative; z-index: 10; padding-left: 15px; padding-right: 15px;">
    <div class="row g-4 justify-content-center">
        <?php foreach (array_slice($spotlight, 0, 3) as $idx => $spot): ?>
            <div class="col-md-4 reveal reveal-scale">
                <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $spot['id'] ?>" class="overlap-img-wrapper float-img-<?= $idx + 1 ?> shadow-lg d-block position-relative text-decoration-none">
                    <img src="<?= e(media_url($spot['image'] ?? null, asset_url('images/placeholder.png'))) ?>" alt="<?= e($spot['attraction_name']) ?>" style="width: 100%; height: 400px; object-fit: cover;">
                    <span class="attraction-card-title"><?= e($spot['attraction_name']) ?></span>
                </a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($spotlight)): ?>
            <div class="col-12">
                <div class="alert alert-light border text-center shadow-sm">No published attractions yet.</div>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="container jubilee-fixed">
    <p class="transition-text reveal">
        From heritage trails to pristine islands and farm escapes—discover the beauty of Vinzons, crafted by nature and shaped by culture.
    </p>

    <div class="discover-divider reveal reveal-fade">
        <div class="divider-line"></div>
        <div class="discover-title-wrap">
            <p class="discover-small">Discover</p>
            <h2 class="more-large">MORE</h2>
        </div>
        <div class="divider-line line-right"></div>
    </div>
</div>

<!-- ABOUT GLASS CARD -->
<div class="container my-5 jubilee-fixed">
    <div class="glass-card reveal">
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
                        the home of the Vinzons' Marsh and Mangrove Forest to gateways to the famous Calaguas Islands.
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

<!-- HERITAGE IMAGE CARD -->
<div class="container my-5 jubilee-fixed">
    <div class="heritage-card shadow-lg reveal">
        <div class="heritage-image-container">
            <img src="<?= e($heritageImage) ?>" class="heritage-img" alt="<?= e($heritageRow['title'] ?? 'Heritage') ?>">
            <h2 class="heritage-bottom-left-text">Puso ng Kasaysayan, Likha ng Kalikasan</h2>
        </div>
    </div>
</div>

<?php if ($heritageAttraction): ?>
<!-- FEATURED ATTRACTION INFO CARD -->
<div class="container pb-5 jubilee-fixed">
    <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $heritageAttraction['id'] ?>" class="glass-info-card p-0 overflow-hidden reveal d-block text-decoration-none">
        <div class="row g-0">
            <div class="col-lg-7 p-5">
                <div class="mb-4">
                    <span class="font-bungee text-warning small"><?= e(ucwords(str_replace('_', ' ', (string) $heritageAttraction['category']))) ?></span>
                    <h2 class="font-abril display-6 mt-2 text-dark"><?= e($heritageAttraction['attraction_name']) ?></h2>
                    <p class="text-muted mt-3" style="font-family: 'Inter', sans-serif;">
                        <?= e(str_limit((string) ($heritageAttraction['description'] ?? 'Not provided'), 220)) ?>
                    </p>
                    <p class="fw-bold text-dark">Entrance Fee: <span class="text-success"><?= e($heritageAttraction['entrance_fee'] ?: 'Not provided') ?></span></p>
                    <span class="text-primary fw-bold text-decoration-none font-bungee small">
                        VIEW DETAILS <i class="bi bi-arrow-right-short"></i>
                    </span>
                </div>

                <hr class="my-4 opacity-10">

                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h4 class="font-bungee h6 text-primary">Location</h4>
                        <p class="small text-secondary mb-0"><?= e($heritageAttraction['address'] ?: 'Location unavailable') ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4 class="font-bungee h6 text-primary">Best time to visit?</h4>
                        <p class="small text-secondary mb-0"><?= e($heritageAttraction['best_time_to_visit'] ?: 'Not provided') ?></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-side-img" style="background-image: url('<?= e(media_url($heritageAttraction['image'] ?? null, asset_url('images/placeholder.png'))) ?>');"></div>
            </div>
        </div>
    </a>
</div>
<?php endif; ?>

<!-- RAGGED HERO -->
<div class="ragged-hero">
    <div class="ragged-bg-container">
        <img src="assets/images/vinzons2.png" class="hero-bg-img">
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

<?php if ($islandAttraction): ?>
<!-- FEATURED PLACE -->
<div class="container overlap-adjustment pb-5 jubilee-fixed">
    <div class="row">
        <div class="col-lg-5 reveal reveal-left">
            <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $islandAttraction['id'] ?>" class="island-frame shadow-lg d-block">
                <img src="<?= e(media_url($islandAttraction['image'] ?? null, asset_url('images/placeholder.png'))) ?>" alt="<?= e($islandAttraction['attraction_name']) ?>" class="img-fluid">
            </a>
        </div>

        <div class="col-lg-7 pt-lg-5 mt-lg-4 reveal reveal-right">
            <h1 class="island-title"><?= e($islandAttraction['attraction_name']) ?></h1>
            <p class="island-text"><?= nl2br(e($islandAttraction['description'] ?: 'Not provided')) ?></p>
            
            <div class="link-box mb-4">
                <p class="mb-1 fw-bold small"><i class="bi bi-tag-fill"></i> Entrance Fee: <?= e($islandAttraction['entrance_fee'] ?: 'Not provided') ?></p>
                <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $islandAttraction['id'] ?>" class="text-dark small fw-bold text-decoration-underline"><i class="bi bi-geo-alt-fill"></i> View details</a>
            </div>

            <div class="travel-details">
                <h4 class="font-bungee h6">Travel guide</h4>
                <p class="small text-muted"><?= nl2br(e($islandAttraction['travel_guide'] ?: 'Not provided')) ?></p>

                <h4 class="font-bungee h6 mt-3">Best time to Visit</h4>
                <p class="small text-muted"><?= e($islandAttraction['best_time_to_visit'] ?: 'Not provided') ?></p>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- ATTRACTION CARDS -->
<section class="hero-section">
  <div class="cards-container stagger">
    <?php foreach ($list as $attr): ?>
    <?php $img = media_url($attr['image'] ?? null, asset_url('images/placeholder.png')); ?>
    <a href="<?= e(BASE_URL) ?>attraction-detail.php?id=<?= (int) $attr['id'] ?>" class="card reveal reveal-scale text-decoration-none">
      <img src="<?= e($img) ?>" alt="<?= e($attr['attraction_name']) ?>">
      <span class="attraction-card-title"><?= e($attr['attraction_name']) ?></span>
    </a>
    <?php endforeach; ?>
    <?php if (empty($list)): ?>
        <div class="alert alert-light border shadow-sm text-center w-100">No published attractions yet.</div>
    <?php endif; ?>
  </div>
</section>

<!-- TRAVEL AGENCIES -->
<section class="agency-section">
  <div class="content-container">
    <div class="top-divider"></div>
    <h2 class="title reveal">LIST OF TRAVEL AGENCIES</h2>
    
    <div class="agencies-grid stagger">
      <?php if (empty($agencies)): ?>
        <p class="text-center text-muted w-100 py-4">No travel agencies listed yet. Run <code>database/restore_static_content.sql</code> in phpMyAdmin.</p>
      <?php else: ?>
      <?php foreach ($agencies as $agency): ?>
      <?php
      $logo = media_url($agency['logo'], asset_url('images/likhalokal-logo.png'));
      $profileUrl = vendor_profile_url((int) $agency['id'], current_request_return_url());
      ?>
      <div class="agency-card">
        <div class="agency-card-banner" style="background-image: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.25)), url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=500&q=80');">
          <div class="agency-card-logo">
            <img src="<?= e($logo) ?>" alt="">
          </div>
        </div>
        <div class="agency-card-body">
          <span class="agency-card-badge"><i class="fa-solid fa-map-location-dot me-1"></i> Tourism Partner</span>
          <h3><a href="<?= e($profileUrl) ?>"><?= e($agency['business_name']) ?></a></h3>
          <p><?= e(str_limit((string) ($agency['description'] ?? ''), 110)) ?></p>
          <div class="agency-card-footer">
            <span class="agency-card-phone">
              <i class="fa-solid fa-phone"></i>
              <?= e($agency['contact_number'] ?? 'No contact') ?>
            </span>
            <?php if (is_logged_in()): ?>
              <a href="<?= e(BASE_URL) ?>message.php?business_id=<?= (int) $agency['id'] ?>&return=<?= rawurlencode(current_request_return_url()) ?>" class="agency-msg-btn text-decoration-none d-inline-block text-center">Message</a>
            <?php else: ?>
              <button type="button" class="agency-msg-btn" data-require-auth>Message</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>

<!-- ============================================
     ANIMATION JAVASCRIPT
     ============================================ -->
<script>
(function () {
    'use strict';

    /* ---- Page fade-in ---- */
    document.body.style.opacity = '0';
    window.addEventListener('load', function () {
        document.body.style.opacity = '1';
    });

    var observerOpts = { threshold: 0.12, rootMargin: '0px 0px -40px 0px' };

    /* ---- Generic .reveal handler ---- */
    var revealEls = document.querySelectorAll('.reveal');
    var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                revealObserver.unobserve(entry.target);
            }
        });
    }, observerOpts);
    revealEls.forEach(function (el) { revealObserver.observe(el); });

    /* ---- Divider line expansion ---- */
    var dividers = document.querySelectorAll('.discover-divider');
    var dividerObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                dividerObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    dividers.forEach(function (el) { dividerObserver.observe(el); });

    /* ---- Top divider in agency section ---- */
    var topDividers = document.querySelectorAll('.top-divider');
    var topDivObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                topDivObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });
    topDividers.forEach(function (el) { topDivObserver.observe(el); });

    /* ---- 3D tilt on glass cards ---- */
    var tiltCards = document.querySelectorAll('.glass-card, .glass-info-card, .agency-card');
    tiltCards.forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var cx = rect.width / 2;
            var cy = rect.height / 2;
            var rx = ((y - cy) / cy) * -4;
            var ry = ((x - cx) / cx) * 4;
            card.style.transform = 'perspective(700px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) translateY(-6px)';
        });
        card.addEventListener('mouseleave', function () {
            card.style.transform = '';
        });
    });

    /* ---- Showcase card tilt (separate, stronger) ---- */
    var showcaseCards = document.querySelectorAll('.cards-container .card');
    showcaseCards.forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            card.style.animation = 'none';
        });
        card.addEventListener('mouseleave', function () {
            card.style.animation = '';
            card.style.transform = '';
        });
        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var cx = rect.width / 2;
            var cy = rect.height / 2;
            var rx = ((y - cy) / cy) * -8;
            var ry = ((x - cx) / cx) * 8;
            card.style.transform = 'translateY(-16px) scale(1.04) perspective(600px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
        });
    });

    /* ---- Overlap image tilt ---- */
    var overlapImgs = document.querySelectorAll('.overlap-img-wrapper');
    overlapImgs.forEach(function (wrapper) {
        wrapper.addEventListener('mousemove', function (e) {
            var rect = wrapper.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var cx = rect.width / 2;
            var cy = rect.height / 2;
            var rx = ((y - cy) / cy) * -5;
            var ry = ((x - cx) / cx) * 5;
            wrapper.style.animation = 'none';
            wrapper.style.transform = 'translateY(-14px) scale(1.03) perspective(600px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
        });
        wrapper.addEventListener('mouseleave', function () {
            wrapper.style.animation = '';
            wrapper.style.transform = '';
        });
    });

    /* ---- Scroll-triggered parallax nudge on hero ---- */
    var heroSection = document.querySelector('.hero');
    if (heroSection) {
        window.addEventListener('scroll', function () {
            var scrolled = window.pageYOffset;
            heroSection.style.backgroundPositionY = (scrolled * 0.3) + 'px';
        }, { passive: true });
    }

})();
</script>
