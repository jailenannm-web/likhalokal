<?php
declare(strict_types=1);

$pageTitle = 'Meet the Devs';
$activeNav = 'about';
require_once dirname(__DIR__) . '/bootstrap.php';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- ── CUSTOM DEVELOPER THEME STYLES (FROM USER CODE) ── -->
<style>
:root {
    --navy-deep: #0A1128;
    --navy-bright: #1C2D5A;
    --amber-bright: #FF9F1C;
    --amber-glow: rgba(255, 159, 28, 0.15);
    --charcoal: #1E1E24;
    --cream-bg: #F7F9FC;
    --card-white: #FFFFFF;
    --transition-bounce: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

body {
    padding-top: 0 !important; /* Eliminate white line space under the navbar */
}
.team-wrapper {
    background-color: var(--cream-bg);
    color: var(--charcoal);
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    margin-top: 0;
    padding-bottom: 0;
}

/* ── SCROLL REVEAL ── */
.team-wrapper .reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.7s ease, transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-wrapper .reveal.from-left {
    transform: translateX(-50px);
}
.team-wrapper .reveal.from-right {
    transform: translateX(50px);
}
.team-wrapper .reveal.from-scale {
    transform: scale(0.92) translateY(20px);
}
.team-wrapper .reveal.visible {
    opacity: 1;
    transform: none;
}

/* ── CHAR ANIMATION ── */
.team-wrapper .char-wrap .char {
    display: inline-block;
    opacity: 0;
    transform: translateY(0.5em);
    transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* ── HERO ── */
.team-wrapper .hero {
    background: linear-gradient(rgba(10,17,40,0.82), rgba(10,17,40,0.92)), url('https://images.unsplash.com/photo-1531403009284-440f080d1e12?q=80&w=2070') no-repeat center center/cover;
    color: var(--card-white);
    text-align: center;
    padding: 180px 20px 130px;
    border-bottom: 8px solid var(--amber-bright);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.team-wrapper .hero-border-pulse {
    position: absolute;
    bottom: -4px;
    left: 0;
    right: 0;
    height: 8px;
    background: linear-gradient(90deg, var(--amber-bright), #FFD080, var(--amber-bright));
    background-size: 200% 100%;
    animation: borderPulse 3s linear infinite;
}

/* ── MICRO-ANIMATIONS & HOVER EFFECTS ── */
.team-wrapper .img-container img {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
    z-index: 10;
    border: 4px solid var(--card-white);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-wrapper .team-card:hover .img-container img {
    transform: scale(1.08) rotate(-3deg);
    border-color: var(--amber-bright);
    box-shadow: 0 12px 32px rgba(255, 159, 28, 0.3);
}
.team-wrapper .value-item {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    padding: 35px;
    border-radius: 16px;
    transition: var(--transition-bounce);
    position: relative;
    overflow: hidden;
    text-align: left;
}
.team-wrapper .value-item:hover {
    background: rgba(255,159,28,0.07);
    border-color: var(--amber-bright);
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(255, 159, 28, 0.15);
}
.team-wrapper .value-icon i {
    transition: transform 0.4s ease;
}
.team-wrapper .value-item:hover .value-icon i {
    transform: scale(1.2) rotate(10deg);
}
@keyframes borderPulse {
    0% { background-position: 0% 0; }
    100% { background-position: 200% 0; }
}

/* ── CONTAINER ── */
.team-wrapper .container-dev {
    max-width: 1200px;
    margin: 0 auto;
    padding: 90px 20px;
}

/* ── SECTION TITLE ── */
.team-wrapper .section-title {
    text-align: center;
    font-family: 'Abril Fatface', serif;
    font-size: 3rem;
    color: var(--navy-deep);
    margin-bottom: 60px;
    position: relative;
}
.team-wrapper .section-title::after {
    content: '';
    display: block;
    width: 0;
    height: 6px;
    background-color: var(--amber-bright);
    margin: 15px auto 0;
    border-radius: 4px;
    transition: width 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.4s;
}
.team-wrapper .section-title.line-visible::after {
    width: 90px;
}

/* ── TEAM GRID ── */
.team-wrapper .team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 40px;
    margin-bottom: 120px;
}
.team-wrapper .team-card {
    background: var(--card-white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(10,17,40,0.04);
    transition: var(--transition-bounce);
    position: relative;
    border: 1px solid rgba(10,17,40,0.05);
}
.team-wrapper .team-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 25px 50px rgba(10,17,40,0.15);
    border-color: var(--amber-bright);
}
.team-wrapper .team-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    transform: skewX(-20deg);
    transition: left 0.6s ease;
    z-index: 5;
    pointer-events: none;
}
.team-wrapper .team-card:hover::before {
    left: 180%;
}
.team-wrapper .img-container {
    width: 100%;
    height: 300px;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--card-white);
    font-weight: 500;
    overflow: hidden;
    border-bottom: 4px solid var(--amber-bright);
}
.team-wrapper .img-container::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 50%, rgba(10, 17, 40, 0.4));
    opacity: 0.3;
    transition: all 0.4s ease;
    pointer-events: none;
}
.team-wrapper .team-card:hover .img-container::after {
    opacity: 0.6;
    background: linear-gradient(to bottom, transparent 30%, rgba(28, 45, 90, 0.6));
}
.team-wrapper .team-info {
    padding: 30px 20px;
    text-align: center;
    background: var(--card-white);
    position: relative;
    z-index: 2;
}
.team-wrapper .team-info h3 {
    font-family: 'Abril Fatface', serif;
    font-size: 1.45rem;
    color: var(--navy-deep);
    margin-bottom: 8px;
    line-height: 1.2;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
}
.team-wrapper .team-info h3::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 50%;
    width: 0;
    height: 2px;
    background: var(--amber-bright);
    transform: translateX(-50%);
    transition: width 0.4s ease;
}
.team-wrapper .team-card:hover .team-info h3 {
    color: var(--amber-bright);
}
.team-wrapper .team-card:hover .team-info h3::after {
    width: 100%;
}
.team-wrapper .team-info p {
    font-size: 0.85rem;
    color: #64748B;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* ── MISSION & VISION GRID ── */
.team-wrapper .corporate-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 40px;
    margin-bottom: 70px;
}

/* ── MISSION / VISION ── */
.team-wrapper .corporate-box {
    background: var(--card-white);
    border-left: 8px solid var(--amber-bright);
    border-radius: 6px 24px 24px 6px;
    padding: 50px 45px;
    margin-bottom: 0; /* Managed by grid gap */
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    position: relative;
    transition: var(--transition-bounce);
    text-align: left;
}
.team-wrapper .corporate-box:hover {
    transform: translateX(8px);
    box-shadow: 0 15px 35px var(--amber-glow);
}
.team-wrapper .corporate-box::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 15%;
    bottom: 15%;
    width: 8px;
    background: linear-gradient(180deg, var(--amber-bright), #FFD080, var(--amber-bright));
    background-size: 100% 200%;
    border-radius: 4px 0 0 4px;
    animation: barGlow 2.5s ease-in-out infinite;
}
@keyframes barGlow {
    0%, 100% { background-position: 0 0; }
    50% { background-position: 0 100%; }
}
.team-wrapper .box-badge {
    position: absolute;
    top: -20px;
    left: 40px;
    background: var(--navy-deep);
    color: var(--amber-bright);
    padding: 8px 28px;
    font-family: 'Bungee', sans-serif;
    font-size: 1.1rem;
    border-radius: 6px;
    box-shadow: 4px 4px 0px var(--charcoal);
}
.team-wrapper .corporate-box p {
    font-size: 1.25rem;
    color: #2D3748;
    font-weight: 300;
    margin-top: 10px;
}

/* ── VALUES ── */
.team-wrapper .values-container {
    background: linear-gradient(145deg, var(--navy-deep) 0%, #050914 100%);
    color: var(--card-white);
    border-radius: 32px;
    padding: 70px 50px;
    margin: 100px 0;
    box-shadow: 0 25px 50px rgba(5,9,20,0.4);
    position: relative;
    overflow: hidden;
}
.team-wrapper .values-container::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(255,159,28,0.12) 1px, transparent 1px), radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 60px 60px, 30px 30px;
    background-position: 0 0, 15px 15px;
    animation: starDrift 20s linear infinite;
    pointer-events: none;
}
@keyframes starDrift {
    0% { background-position: 0 0, 15px 15px; }
    100% { background-position: 0 60px, 15px 75px; }
}
.team-wrapper .values-container .section-title {
    color: var(--card-white);
}
.team-wrapper .values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 50px;
    position: relative;
    z-index: 1;
}
.team-wrapper .value-item {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    padding: 35px;
    border-radius: 16px;
    transition: var(--transition-bounce);
    position: relative;
    overflow: hidden;
    text-align: left;
}
.team-wrapper .value-item::after {
    content: '';
    position: absolute;
    bottom: -30px;
    right: -30px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,159,28,0.15), transparent 70%);
    transition: transform 0.5s ease;
}
.team-wrapper .value-item:hover::after {
    transform: scale(2.5);
}
.team-wrapper .value-item:hover {
    background: rgba(255,159,28,0.07);
    border-color: var(--amber-bright);
    transform: translateY(-8px);
}
.team-wrapper .value-item h4 {
    font-family: 'Bungee', sans-serif;
    font-size: 1.2rem;
    color: var(--amber-bright);
    margin-bottom: 15px;
    letter-spacing: 0.5px;
    position: relative;
}
.team-wrapper .value-item h4::after {
    content: '';
    display: block;
    width: 0;
    height: 2px;
    background: var(--amber-bright);
    margin-top: 6px;
    transition: width 0.4s ease;
}
.team-wrapper .value-item:hover h4::after {
    width: 40px;
}
.team-wrapper .value-item p {
    font-size: 0.95rem;
    color: #D1D5DB;
    font-weight: 300;
}

/* ── STRATEGY GRID ── */
.team-wrapper .strategy-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-top: 100px;
}
@media (max-width: 768px) {
    .team-wrapper .strategy-grid {
        grid-template-columns: 1fr;
        gap: 80px;
    }
}
.team-wrapper .strat-card {
    background: var(--card-white);
    border: 3px solid var(--navy-deep);
    border-radius: 24px;
    padding: 70px 40px 40px;
    position: relative;
    overflow: visible;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    transition: var(--transition-bounce);
    text-align: left;
}
.team-wrapper .strat-card:hover {
    border-color: var(--amber-bright);
    box-shadow: 0 20px 40px var(--amber-glow);
    transform: translateY(-4px);
}
.team-wrapper .strat-header {
    position: absolute;
    top: -46px;
    left: 32px;
    background-color: var(--cream-bg);
    padding: 0 12px 4px;
    z-index: 3;
    line-height: 1;
}
.team-wrapper .strat-header span {
    display: block;
    font-family: 'Abril Fatface', serif;
    font-size: 0.95rem;
    color: var(--navy-deep);
    line-height: 1;
    margin-bottom: 1px;
    letter-spacing: 0.5px;
}
.team-wrapper .strat-header h4 {
    font-family: 'Bungee', sans-serif;
    color: var(--amber-bright);
    font-size: 2rem;
    text-shadow: 2px 2px 0px var(--navy-deep);
    line-height: 1;
}
.team-wrapper .strat-card ul {
    list-style: none;
    padding-left: 0;
}
.team-wrapper .strat-card ul li {
    position: relative;
    padding-left: 35px;
    margin-bottom: 18px;
    font-size: 1.05rem;
    color: #4A5568;
    opacity: 0;
    transform: translateX(-20px);
    transition: opacity 0.5s ease, transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.3s;
}
.team-wrapper .strat-card ul li.visible {
    opacity: 1;
    transform: none;
}
.team-wrapper .strat-card ul li:hover {
    color: var(--navy-deep);
}
.team-wrapper .strat-card ul li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 0;
    color: var(--amber-bright);
    font-weight: 900;
    font-size: 1.2rem;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-wrapper .strat-card ul li:hover::before {
    transform: scale(1.3) rotate(-10deg);
}

/* ── FOOTER BRANDING ── */
.team-wrapper .footer-branding {
    text-align: center;
    margin-top: 120px;
    padding-top: 60px;
    border-top: 2px dashed rgba(10,17,40,0.1);
}
.team-wrapper .footer-branding > p {
    font-size: 1.15rem;
    max-width: 850px;
    margin: 0 auto 40px;
    color: #4A5568;
    font-weight: 300;
}

/* ── CONTACT STRIP ── */
.team-wrapper .contact-strip {
    margin: 50px auto 40px;
    max-width: 780px;
}
.team-wrapper .contact-heading {
    font-family: 'Abril Fatface', serif;
    font-size: 1.6rem;
    color: var(--navy-deep);
    margin-bottom: 28px;
    position: relative;
    display: inline-block;
}
.team-wrapper .contact-heading::after {
    content: '';
    display: block;
    width: 0;
    height: 4px;
    background: var(--amber-bright);
    border-radius: 2px;
    margin-top: 6px;
    transition: width 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s;
}
.team-wrapper .contact-strip.visible .contact-heading::after {
    width: 100%;
}
.team-wrapper .contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 600px) {
    .team-wrapper .contact-grid {
        grid-template-columns: 1fr;
    }
}
.team-wrapper .contact-card {
    display: flex;
    align-items: center;
    gap: 18px;
    background: var(--card-white);
    border: 2px solid rgba(10,17,40,0.07);
    border-radius: 16px;
    padding: 22px 26px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 6px 20px rgba(10,17,40,0.04);
    transition: var(--transition-bounce);
    position: relative;
    overflow: hidden;
}
.team-wrapper .contact-card::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--amber-bright), #FFD080);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}
.team-wrapper .contact-card:hover {
    border-color: var(--amber-bright);
    transform: translateY(-6px);
    box-shadow: 0 16px 35px var(--amber-glow);
}
.team-wrapper .contact-card:hover::before {
    transform: scaleX(1);
}
.team-wrapper .contact-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: var(--amber-glow);
    border: 1.5px solid rgba(255,159,28,0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s, transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-wrapper .contact-icon svg {
    width: 22px;
    height: 22px;
    stroke: var(--amber-bright);
    transition: stroke 0.3s;
}
.team-wrapper .contact-card:hover .contact-icon {
    background: var(--amber-bright);
    transform: rotate(-8deg) scale(1.1);
}
.team-wrapper .contact-card:hover .contact-icon svg {
    stroke: var(--card-white);
}
.team-wrapper .contact-info {
    display: flex;
    flex-direction: column;
    text-align: left;
}
.team-wrapper .contact-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--amber-bright);
    margin-bottom: 4px;
}
.team-wrapper .contact-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--navy-deep);
    transition: color 0.3s;
}
.team-wrapper .contact-card:hover .contact-value {
    color: var(--charcoal);
}

/* ── DIVIDER ── */
.team-wrapper .footer-divider {
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, var(--amber-bright), rgba(255,159,28,0.2));
    border-radius: 2px;
    margin: 40px auto 36px;
}

/* ── TAGLINE ── */
.team-wrapper .tagline {
    font-family: 'Abril Fatface', serif;
    font-size: 2.2rem;
    color: var(--navy-deep);
    letter-spacing: 0.5px;
    line-height: 1.5;
    padding-bottom: 60px;
}
.team-wrapper .tagline span.highlight {
    color: var(--amber-bright);
    display: inline-block;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-wrapper .tagline span.highlight:hover {
    transform: scale(1.15) rotate(-2deg);
}
.team-wrapper .tagline span.accent {
    color: #E63946;
    display: inline-block;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-wrapper .tagline span.accent:hover {
    transform: scale(1.15) rotate(2deg);
}

/* ── SCROLL TO TOP ── */
.team-wrapper .scroll-top {
    position: fixed;
    bottom: 32px;
    right: 32px;
    width: 46px;
    height: 46px;
    background: var(--navy-deep);
    border: 2px solid var(--amber-bright);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.4s, transform 0.4s, background 0.3s;
    z-index: 999;
}
.team-wrapper .scroll-top.show {
    opacity: 1;
    transform: none;
}
.team-wrapper .scroll-top:hover {
    background: var(--amber-bright);
}
.team-wrapper .scroll-top svg {
    width: 18px;
    height: 18px;
    fill: none;
    stroke: var(--amber-bright);
    stroke-width: 2.5;
    transition: stroke 0.3s;
}
.team-wrapper .scroll-top:hover svg {
    stroke: var(--navy-deep);
}
</style>

<div class="team-wrapper">
    <!-- HERO SECTION -->
    <header class="hero">
        <canvas id="hero-canvas" style="position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;"></canvas>
        <div class="hero-border-pulse" style="z-index: 3;"></div>
        <div class="hero-content-wrapper" style="position: relative; z-index: 2; max-width: 900px; margin: 0 auto; padding: 0 20px;">
            <span class="badge rounded-pill mb-3 px-3 py-1.5 animate__animated animate__fadeIn" style="background: rgba(255,159,28,0.15); color: var(--amber-bright); font-size: 0.8rem; letter-spacing: 2px; border: 1px solid rgba(255,159,28,0.3);">THE ENGINEERING ENGINE</span>
            <h1 class="animate__animated animate__fadeInDown fw-bold mb-3" style="font-family: 'Bungee', sans-serif; font-size: clamp(2.5rem, 8vw, 4.5rem); letter-spacing: 2px; text-shadow: 4px 4px 15px rgba(0,0,0,0.8); background: linear-gradient(135deg, var(--amber-bright) 0%, #FFFFFF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Powerpuff Co.</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s" style="font-family: 'Dancing Script', cursive; font-size: clamp(1.8rem, 4vw, 2.8rem); color: rgba(255,255,255,0.95); text-shadow: 2px 2px 12px rgba(0,0,0,0.6); max-width: 800px; margin: 0 auto; line-height: 1.3;">Engineering Solutions That Connect Local Markets Globally</p>
        </div>
    </header>

    <div class="container-dev container">
        <!-- TEAM -->
        <section>
            <h2 class="section-title char-wrap">The Engineering Team</h2>
            <div class="team-grid">
                <!-- Niña Theressa B. Ragos -->
                <div class="team-card reveal from-scale">
                    <div class="img-container">
                        <img src="<?= asset_url('images/nina.png') ?>" alt="Niña Theressa B. Ragos" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Nina+Ragos&background=FF9F1C&color=0A1128&bold=true&size=180'">
                    </div>
                    <div class="team-info">
                        <h3>Niña Theressa B. Ragos</h3>
                        <p><i class="fa-solid fa-network-wired text-warning me-1.5" style="font-size:0.8rem;"></i>Lead Integration Architect</p>
                        <div class="team-social mt-3 pt-3 border-top border-light" style="display: flex; justify-content: center; gap: 18px;">
                            <a href="https://github.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-github fs-5"></i></a>
                            <a href="https://linkedin.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-linkedin-in fs-5"></i></a>
                            <a href="mailto:nina@likha.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'"><i class="fa-solid fa-envelope fs-5"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Jailen Ann A. Mostoles -->
                <div class="team-card reveal from-scale">
                    <div class="img-container">
                        <img src="<?= asset_url('images/jailen.png') ?>" alt="Jailen Ann A. Mostoles" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Jailen+Mostoles&background=7C3AED&color=0A1128&bold=true&size=180'">
                    </div>
                    <div class="team-info">
                        <h3>Jailen Ann A. Mostoles</h3>
                        <p><i class="fa-solid fa-server text-warning me-1.5" style="font-size:0.8rem;"></i>Database & Systems Architect</p>
                        <div class="team-social mt-3 pt-3 border-top border-light" style="display: flex; justify-content: center; gap: 18px;">
                            <a href="https://github.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-github fs-5"></i></a>
                            <a href="https://linkedin.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-linkedin-in fs-5"></i></a>
                            <a href="mailto:jailen@likha.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'"><i class="fa-solid fa-envelope fs-5"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Christianne Ley B. Ubana -->
                <div class="team-card reveal from-scale">
                    <div class="img-container">
                        <img src="<?= asset_url('images/christianne.png') ?>" alt="Christianne Ley B. Ubana" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Christianne+Ubana&background=10B981&color=0A1128&bold=true&size=180'">
                    </div>
                    <div class="team-info">
                        <h3>Christianne Ley B. Ubana</h3>
                        <p><i class="fa-solid fa-cubes text-warning me-1.5" style="font-size:0.8rem;"></i>UI/UX & Frontend Engineer</p>
                        <div class="team-social mt-3 pt-3 border-top border-light" style="display: flex; justify-content: center; gap: 18px;">
                            <a href="https://github.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-github fs-5"></i></a>
                            <a href="https://linkedin.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-linkedin-in fs-5"></i></a>
                            <a href="mailto:christianne@likha.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'"><i class="fa-solid fa-envelope fs-5"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Samantha B. Frondozo -->
                <div class="team-card reveal from-scale">
                    <div class="img-container">
                        <img src="<?= asset_url('images/samantha.png') ?>" alt="Samantha B. Frondozo" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Samantha+Frondozo&background=F43F5E&color=0A1128&bold=true&size=180'">
                    </div>
                    <div class="team-info">
                        <h3>Samantha B. Frondozo</h3>
                        <p><i class="fa-solid fa-gauge-high text-warning me-1.5" style="font-size:0.8rem;"></i>QA & Optimization Specialist</p>
                        <div class="team-social mt-3 pt-3 border-top border-light" style="display: flex; justify-content: center; gap: 18px;">
                            <a href="https://github.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-github fs-5"></i></a>
                            <a href="https://linkedin.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'" target="_blank"><i class="fa-brands fa-linkedin-in fs-5"></i></a>
                            <a href="mailto:samantha@likha.com" class="social-link" style="color: var(--navy-deep); transition: color 0.3s;" onmouseover="this.style.color='var(--amber-bright)'" onmouseout="this.style.color='var(--navy-deep)'"><i class="fa-solid fa-envelope fs-5"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MISSION & VISION SIDE-BY-SIDE GRID -->
        <div class="corporate-grid">
            <!-- MISSION -->
            <div class="corporate-box reveal from-left">
                <div class="box-badge">OUR MISSION</div>
                <p>To engineer secure, scalable, and intuitive digital ecosystems that bridge the gap between traditional grassroot entrepreneurs and global tech infrastructure—empowering small-to-medium operations with accessible business tools.</p>
            </div>

            <!-- VISION -->
            <div class="corporate-box reveal from-right">
                <div class="box-badge">OUR VISION</div>
                <p>To be the leading catalyst for localized digital transformation, standardizing hyper-local digital marketplaces where regional tech innovation preserves unique heritage and fosters economic self-reliance.</p>
            </div>
        </div>

        <!-- VALUES -->
        <div class="values-container reveal">
            <h2 class="section-title char-wrap">Corporate Values</h2>
            <div class="values-grid">
                <div class="value-item reveal">
                    <div class="value-icon mb-3"><i class="fa-solid fa-rocket text-warning fs-1"></i></div>
                    <h4>Innovation</h4>
                    <p>Leveraging state-of-the-art frameworks to simplify intricate software capabilities for non-technical application end users.</p>
                </div>
                <div class="value-item reveal">
                    <div class="value-icon mb-3"><i class="fa-solid fa-award text-warning fs-1"></i></div>
                    <h4>Authenticity</h4>
                    <p>Building secure software architecture dedicated solely to showcasing unaltered community assets, narratives, and homegrown products.</p>
                </div>
                <div class="value-item reveal">
                    <div class="value-icon mb-3"><i class="fa-solid fa-shield-halved text-warning fs-1"></i></div>
                    <h4>Sovereignty</h4>
                    <p>Creating tech-forward platforms that keep resources and direct business ownership firmly within local regional limits.</p>
                </div>
            </div>
        </div>

        <!-- STRATEGY -->
        <div class="strategy-grid" style="margin-top: 110px;">
            <div class="strat-card reveal from-left">
                <div class="strat-header">
                    <span>Target</span>
                    <h4>MARKET</h4>
                </div>
                <ul>
                    <li>Independent regional agricultural networks and cooperative farms.</li>
                    <li>Micro-businesses, home-based production units, and creative artisans.</li>
                    <li>Regional tourism, hospitality vendors, and experiential services.</li>
                    <li>Youth-led startup initiatives and local enterprise pipelines.</li>
                </ul>
            </div>
            <div class="strat-card reveal from-right">
                <div class="strat-header">
                    <span>Platform</span>
                    <h4>IMPACT</h4>
                </div>
                <ul>
                    <li>Dynamic web systems showcasing regional solutions and services directly.</li>
                    <li>Frictionless onboard systems for micro-merchants with minimal tech footprint.</li>
                    <li>Localized, data-driven promotional frameworks to maximize product scale.</li>
                    <li>Scalable integration ecosystems that empower B2B collaboration.</li>
                </ul>
            </div>
        </div>

        <!-- FOOTER BRANDING -->
        <footer class="footer-branding reveal">
            <p>What started as a software concept has grown into a structured mission—to build reliable infrastructure where it matters most. Inspired by community resilience and powered by modern architectures, this application serves to make technology accessible while preserving authentic identities.</p>
            
            <!-- CONTACTS -->
            <div class="contact-strip reveal">
                <h3 class="contact-heading">Get in Touch</h3>
                <div class="contact-grid">
                    <a href="mailto:powerpuffco@likha.com" class="contact-card">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <polyline points="2,4 12,13 22,4"/>
                            </svg>
                        </div>
                        <div class="contact-info">
                            <span class="contact-label">Email Us</span>
                            <span class="contact-value">powerpuffco@likha.com</span>
                        </div>
                    </a>
                    <a href="tel:+639123456789" class="contact-card">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 12 19.79 19.79 0 0 1 1.08 3.4 2 2 0 0 1 3.05 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.15a16 16 0 0 0 5.76 5.76l1.2-1.2a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="contact-info">
                            <span class="contact-label">Call Us</span>
                            <span class="contact-value">+63 912 345 6789</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- DIVIDER -->
            <div class="footer-divider"></div>

            <!-- TAGLINE -->
            <div class="tagline">
                Together, we <span class="highlight">create</span>. Together, we <span class="highlight">grow</span>.<br>
                Sama-samang <span class="highlight">pag-angat</span>, para sa <span class="accent">lokal</span>.
            </div>
        </footer>
    </div>

    <!-- Scroll-to-top -->
    <div class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    /* ── 0. HERO CANVAS CONSTELLATION ANIMATION ── */
    const canvas = document.getElementById('hero-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width = canvas.width = canvas.offsetWidth;
        let height = canvas.height = canvas.offsetHeight;
        
        window.addEventListener('resize', () => {
            width = canvas.width = canvas.offsetWidth;
            height = canvas.height = canvas.offsetHeight;
        });
        
        const particles = [];
        for (let i = 0; i < 45; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.4,
                vy: (Math.random() - 0.5) * 0.4,
                radius: Math.random() * 2 + 1
            });
        }
        
        function animate() {
            ctx.clearRect(0, 0, width, height);
            ctx.fillStyle = 'rgba(255, 159, 28, 0.6)';
            
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > width) p.vx *= -1;
                if (p.y < 0 || p.y > height) p.vy *= -1;
                
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fill();
            });
            
            ctx.lineWidth = 1;
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 100) {
                        ctx.strokeStyle = `rgba(255, 159, 28, ${0.15 * (1 - dist / 100)})`;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animate);
        }
        animate();
    }

    /* ── 1. SCROLL REVEAL ── */
    const revealEls = document.querySelectorAll('.team-wrapper .reveal');
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.closest('.team-grid, .values-grid') ? Array.from(entry.target.parentElement.children).indexOf(entry.target) * 120 : 0;
                setTimeout(() => entry.target.classList.add('visible'), delay);
                revealObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    revealEls.forEach(el => revealObs.observe(el));

    /* ── 2. SECTION TITLE UNDERLINE ── */
    const titleObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('line-visible');
                titleObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.team-wrapper .section-title').forEach(t => titleObs.observe(t));

    /* ── 3. LETTER-BY-LETTER on section titles ── */
    document.querySelectorAll('.team-wrapper .char-wrap').forEach(el => {
        const nodes = Array.from(el.childNodes);
        el.innerHTML = '';
        nodes.forEach(node => {
            if (node.nodeType === 3) {
                node.textContent.split('').forEach(ch => {
                    const s = document.createElement('span');
                    s.className = 'char';
                    s.textContent = ch === ' ' ? '\u00A0' : ch;
                    el.appendChild(s);
                });
            } else {
                el.appendChild(node);
            }
        });
        const charObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.char').forEach((c, i) => {
                        setTimeout(() => c.style.cssText = 'opacity:1;transform:none;', i * 40);
                    });
                    charObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });
        charObs.observe(el);
    });

    /* ── 4. STRAT CARD LIST stagger ── */
    document.querySelectorAll('.team-wrapper .strat-card').forEach(card => {
        const listObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('li').forEach((li, i) => {
                        setTimeout(() => li.classList.add('visible'), 300 + i * 130);
                    });
                    listObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        listObs.observe(card);
    });

    /* ── 5. SCROLL-TO-TOP ── */
    const btn = document.getElementById('scrollTop');
    if (btn) {
        window.addEventListener('scroll', () => {
            btn.classList.toggle('show', window.scrollY > 400);
        });
    }
});
</script>

<?php
require BASE_PATH . '/includes/footer.php';
?>
