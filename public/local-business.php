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
@import url('https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Bungee&family=Dancing+Script:wght@400..700&display=swap');
    :root {
        --vinzons-blue: #0077C2;
        --vinzons-dark-blue: #050A30;
        --vinzons-amber: #FFBF00;
        --vinzons-orange: #FF9800;
        --vinzons-white: #ffffff;
        --body-font: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

body, p, span, li, small, a:not(.font-bungee) {
    font-family: var(--body-font);
}

.font-dancing {
    font-family: 'Dancing Script', cursive !important;
}
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

/* ============================================
   ANIMATION KEYFRAMES
   ============================================ */

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(-40px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInRight {
    from { opacity: 0; transform: translateX(40px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
}

@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-10px); }
}

@keyframes pulse-ring {
    0%   { box-shadow: 0 0 0 0 rgba(242, 166, 61, 0.5); }
    70%  { box-shadow: 0 0 0 14px rgba(242, 166, 61, 0); }
    100% { box-shadow: 0 0 0 0 rgba(242, 166, 61, 0); }
}

@keyframes shimmer {
    0%   { background-position: -200% center; }
    100% { background-position:  200% center; }
}

@keyframes lineExpand {
    from { width: 0; }
    to   { width: 100%; }
}

@keyframes bounce-in {
    0%   { opacity: 0; transform: scale(0.3); }
    50%  { opacity: 1; transform: scale(1.08); }
    70%  { transform: scale(0.97); }
    100% { transform: scale(1); }
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

@keyframes text-flicker {
    0%, 100% { opacity: 1; }
    92%       { opacity: 1; }
    93%       { opacity: 0.8; }
    94%       { opacity: 1; }
}

@keyframes gradient-shift {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* ============================================
   SCROLL-REVEAL BASE STATES
   ============================================ */
.reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.reveal-left {
    transform: translateX(-40px);
}
.reveal.reveal-right {
    transform: translateX(40px);
}
.reveal.reveal-scale {
    transform: scale(0.88);
}
.reveal.visible {
    opacity: 1;
    transform: none;
}

/* Staggered children */
.stagger-children .reveal:nth-child(1) { transition-delay: 0.05s; }
.stagger-children .reveal:nth-child(2) { transition-delay: 0.15s; }
.stagger-children .reveal:nth-child(3) { transition-delay: 0.25s; }
.stagger-children .reveal:nth-child(4) { transition-delay: 0.35s; }

/* ============================================
   HERO
   ============================================ */
.biz-hero {
    position: relative;
    min-height: 450px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #ffffff;
    background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), 
                      url('assets/images/local.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: scroll;
    overflow: hidden;
}

/* Animated overlay shimmer on hero */
.biz-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.04) 50%, transparent 70%);
    background-size: 200% 100%;
    animation: shimmer 4s infinite linear;
    pointer-events: none;
}

.biz-hero-content {
    z-index: 2;
    padding: 30px;
}

.biz-hero-title {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 15px;
    letter-spacing: 1px;
    text-transform: uppercase;
    animation: fadeInUp 1s ease both;
}

.biz-hero-tagline {
    font-size: 1.25rem;
    font-weight: 400;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
    animation: fadeInUp 1s ease 0.3s both;
}

/* ============================================
   CTA SECTION
   ============================================ */
.biz-cta-section {
    padding: 60px 0;
    text-align: center;
    background-color: #f9f9f9;
}

.biz-keywords {
    font-size: 1rem;
    color: #007bff;
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

/* ============================================
   DIRECTORY SECTION
   ============================================ */
.directory-section {
    padding: 60px 4%;
    background-color: #fffaf5;
}

@media (max-width: 768px) {
    .directory-section {
        padding: 60px 15px;
    }
}

.section-title {
    font-family: 'Bungee';
    color: var(--dark-navy);
    font-size: 2rem;
    margin-bottom: 30px;
    position: relative;
    display: inline-block;
}

/* Animated underline on section titles */
.section-title::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 0;
    height: 3px;
    width: 0;
    background-color: var(--amber-orange);
    transition: width 0.6s ease;
}
.section-title.visible::after {
    width: 100%;
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
    transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

/* Ripple effect on category cards */
.category-card::before {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    width: 0; height: 0;
    background: rgba(255,255,255,0.25);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.5s ease, height 0.5s ease;
}
.category-card:hover::before {
    width: 250px;
    height: 250px;
}

.category-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 16px 32px rgba(0,0,0,0.18);
    background-color: #e8952a;
}

.category-card i {
    font-size: 2.5rem;
    margin-bottom: 15px;
    display: block;
    transition: transform 0.4s ease;
}
.category-card:hover i {
    transform: scale(1.25) rotate(-5deg);
}

.category-card span {
    font-family: 'Bungee', cursive;
    font-size: 1.1rem;
}

/* ============================================
   SUPPORT LOCAL DIVIDER
   ============================================ */
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
    transform-origin: left center;
    transform: scaleX(0);
    transition: transform 0.8s ease;
}
.support-line.line-right {
    transform-origin: right center;
}
.support-local-wrap.visible .support-line {
    transform: scaleX(1);
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
    animation: text-flicker 6s infinite;
}

/* ============================================
   FEATURED BUSINESS CARDS
   ============================================ */
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
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                box-shadow 0.4s ease;
}

.business-card:hover {
    transform: translateY(-12px) rotate(0.5deg);
    box-shadow: 0 24px 48px rgba(0,0,0,0.15);
}

.biz-image-area {
    padding: 30px;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.biz-image-area img {
    max-width: 80%;
    max-height: 80%;
    object-fit: contain;
    transition: transform 0.5s ease;
}
.business-card:hover .biz-image-area img {
    transform: scale(1.1);
}

.biz-info-bar {
    background-color: var(--amber-orange);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    transition: background-color 0.3s ease;
}
.business-card:hover .biz-info-bar {
    background-color: #e8952a;
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
    transition: transform 0.3s ease, background-color 0.3s ease;
    animation: pulse-ring 2.5s infinite;
}
.mail-btn:hover {
    transform: scale(1.15);
    background-color: var(--dark-navy);
    color: white;
}

/* ============================================
   ROOT VARIABLES (override/extend)
   ============================================ */
:root {
    --amber-orange: #f2a63d;
    --dark-navy: #051024;
    --section-bg: #fffaf5;
    --light-blue-bg: #a8e0ff;
}

/* ============================================
   HERO (MAIN HERO SECTION)
   ============================================ */
.biz-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('images/local.png'); 
    background-size: cover;
    background-position: center;
    padding: 120px 4%;
    color: white;
}

@media (max-width: 768px) {
    .biz-hero {
        padding: 120px 15px;
    }
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
    padding: 80px 4%;
    text-align: center;
    color: white;
}

@media (max-width: 768px) {
    .biz-cta-section {
        padding: 80px 15px;
    }
}

.biz-keywords {
    font-family: 'Bungee';
    color: var(--amber-orange);
    font-size: 2.2rem;
    margin-bottom: 10px;
    background: linear-gradient(90deg, var(--amber-orange), #ffcf6b, var(--amber-orange));
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 3s linear infinite;
}

.biz-main-heading {
    font-family: 'Abril Fatface', serif;
    font-size: 2.5rem;
    margin-bottom: 25px;
    color: white;
}

.biz-description {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.15rem;
    line-height: 1.6;
    max-width: 850px;
    margin: 0 auto;
    color: var(--amber-orange);
}

/* ============================================
   DIRECTORY WRAPPER
   ============================================ */
.directory-wrapper {
    background-color: var(--section-bg);
    padding: 80px 4%;
}

@media (max-width: 768px) {
    .directory-wrapper {
        padding: 80px 15px;
    }
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

/* ============================================
   SUPPORT LOCAL DIVIDER (FULL)
   ============================================ */
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

/* ============================================
   FEATURED BUSINESS GRID (FULL)
   ============================================ */
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
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                box-shadow 0.4s ease;
}
.biz-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.biz-img-box { height: 200px; display: flex; align-items: center; justify-content: center; padding: 20px; overflow: hidden; }
.biz-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease; }
.biz-card:hover .biz-img-box img { transform: scale(1.08); }

.biz-footer {
    background-color: var(--amber-orange);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.3s ease;
}
.biz-card:hover .biz-footer { background-color: #e8952a; }

.biz-footer h4 { font-family: 'Bungee'; font-size: 0.9rem; margin: 0; }
.biz-footer p { font-size: 0.75rem; margin: 5px 0 0; line-height: 1.3; }

/* ============================================
   LISTINGS SECTION
   ============================================ */
.listings-wrapper {
    background-color: var(--section-bg);
    padding: 0 4% 100px;
}

@media (max-width: 768px) {
    .listings-wrapper {
        padding: 0 15px 100px;
    }
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
    position: relative;
    overflow: hidden;
}
.col-header::after {
    content: '';
    position: absolute;
    bottom: -4px; left: 0;
    height: 4px;
    width: 0;
    background: linear-gradient(90deg, var(--amber-orange), #ffcf6b);
    transition: width 0.8s ease;
}
.col-header.visible::after { width: 100%; }

.list-item {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    align-items: flex-start;
    transition: transform 0.3s ease;
}
.list-item:hover {
    transform: translateX(6px);
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
    transition: transform 0.4s ease, box-shadow 0.4s ease, background-color 0.3s ease;
    animation: float 4s ease-in-out infinite;
}
.list-item:nth-child(2) .list-icon { animation-delay: 0.5s; }
.list-item:nth-child(3) .list-icon { animation-delay: 1s; }

.list-item:hover .list-icon {
    transform: scale(1.15) rotate(-5deg);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    background-color: var(--amber-orange);
}
.list-item:hover .list-icon i {
    color: white;
}

.list-icon i { font-size: 1.8rem; color: var(--dark-navy); transition: color 0.3s ease; }

.list-info h5 { font-family: 'Inter', sans-serif; font-weight: 800; margin: 0; color: var(--dark-navy); }
.list-info p { font-size: 0.85rem; color: #555; margin: 2px 0; }
.list-info .contact-btn {
    font-size: 0.85rem; font-weight: 700; color: var(--dark-navy);
    text-decoration: underline; display: inline-block; margin-top: 5px;
    transition: color 0.3s ease, letter-spacing 0.3s ease;
    position: relative;
}
.list-info .contact-btn:hover {
    color: var(--amber-orange);
    letter-spacing: 0.5px;
}

/* ============================================
   REGISTRATION SECTION
   ============================================ */
.reg-container {
    background-color: var(--light-blue-bg);
    padding-bottom: 80px;
    margin-top: -1px;
    position: relative;
    overflow: hidden;
}

/* Animated floating blobs in bg */
.reg-container::before,
.reg-container::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    opacity: 0.15;
    pointer-events: none;
}
.reg-container::before {
    width: 400px; height: 400px;
    background: var(--dark-navy);
    top: -100px; left: -100px;
    animation: float 8s ease-in-out infinite;
}
.reg-container::after {
    width: 300px; height: 300px;
    background: var(--amber-orange);
    bottom: -80px; right: -80px;
    animation: float 6s ease-in-out infinite reverse;
}

.reg-banner {
    background-color: var(--dark-navy);
    padding: 20px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.reg-banner::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.05) 50%, transparent 100%);
    background-size: 200% 100%;
    animation: shimmer 3s infinite linear;
}

.reg-banner h2 {
    font-family: 'Bungee';
    color: white;
    margin: 0;
    font-size: 1.8rem;
    letter-spacing: 2px;
    animation: slideInDown 0.8s ease both;
}

.registration-card {
    max-width: 900px;
    margin: 50px auto;
    padding: 45px 70px;
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 35px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    position: relative;
    z-index: 1;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.registration-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.1);
}

.reg-title {
    font-family: 'Bungee';
    color: var(--amber-orange);
    font-size: 2.5rem;
    margin-bottom: 12px;
}

.lisu-description {
    font-family: 'Lisu Bosa', serif !important;
    font-size: 1.15rem;
    font-weight: 500;
    line-height: 1.35;
    color: var(--dark-navy);
    margin-bottom: 20px;
}

.step-box {
    margin-bottom: 22px;
    transition: transform 0.3s ease;
}
.step-box:hover { transform: translateX(6px); }

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
    transition: border-color 0.3s ease, color 0.3s ease;
}
.step-box:hover .step-label {
    border-left-color: var(--dark-navy);
    color: var(--amber-orange);
}

.step-details {
    list-style: none;
    padding-left: 35px;
    margin-top: 8px;
}

.step-details li {
    font-family: 'Lisu Bosa', serif !important;
    font-size: 1.1rem;
    line-height: 1.25;
    margin-bottom: 6px;
    position: relative;
    color: var(--dark-navy);
    transition: transform 0.2s ease;
}
.step-details li:hover { transform: translateX(4px); }

.step-details li::before {
    content: "→";
    color: var(--amber-orange);
    position: absolute;
    left: -22px;
    font-weight: bold;
    transition: transform 0.3s ease;
}
.step-details li:hover::before { transform: translateX(4px); }

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
    position: relative;
    overflow: hidden;
}

/* Shimmer sweep on button */
.apply-button::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.35), transparent);
    transform: skewX(-20deg);
    transition: left 0.5s ease;
}
.apply-button:hover::before { left: 160%; }

.apply-button:hover {
    transform: translateY(2px);
    box-shadow: 0 3px 0px #c98220;
    color: var(--dark-navy);
}

@media (max-width: 768px) {
    .registration-card {
        margin: 20px;
        padding: 30px;
    }
    .reg-title { font-size: 1.8rem; }
}

/* ============================================
   MAIN HERO SECTION (inline style override)
   ============================================ */
.hero-text-animate h1 {
    animation: fadeInLeft 1s ease both;
}
.hero-text-animate p {
    animation: fadeInLeft 1s ease 0.25s both;
}

/* Particle dots in hero */
.hero-particles {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 1;
}
.hero-particles span {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    animation: float var(--dur, 5s) ease-in-out infinite;
    animation-delay: var(--delay, 0s);
}

</style>


<!-- HERO SECTION -->
<section class="hero position-relative" 
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
        <span style="left:10%;top:20%;--dur:6s;--delay:0s;width:8px;height:8px;"></span>
        <span style="left:25%;top:60%;--dur:8s;--delay:1s;width:5px;height:5px;"></span>
        <span style="left:50%;top:30%;--dur:7s;--delay:0.5s;width:10px;height:10px;opacity:0.15;"></span>
        <span style="left:70%;top:70%;--dur:5s;--delay:2s;width:6px;height:6px;"></span>
        <span style="left:85%;top:25%;--dur:9s;--delay:1.5s;width:7px;height:7px;"></span>
        <span style="left:40%;top:80%;--dur:6.5s;--delay:0.8s;width:4px;height:4px;"></span>
        <span style="left:60%;top:15%;--dur:7.5s;--delay:3s;width:9px;height:9px;opacity:0.1;"></span>
    </div>
    
 <div class="container position-relative h-100 py-5 d-flex flex-column justify-content-center mt-5 hero-text-animate" style="z-index:2; padding-left: 25px; padding-right: 25px;">
        <h1 class="display-3 fw-bold text-white mb-2" style="font-family: Impact, sans-serif; letter-spacing: 2px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4);">
            LOKAL NA NEGOSYO,<br><span style="color: #c98220;">LOKAL NA ASENSO</span>
        </h1>
        <p class="text-white mb-0 font-dancing" style="font-size: 2.5rem; line-height: 1.2; text-shadow: 2px 2px 6px rgba(0,0,0,0.6); max-width: 750px;">
            Building community livelihoods.
        </p>
    </div>

</section>

<!-- CTA SECTION -->
<section class="biz-cta-section">
    <div class="container">
        <h2 class="biz-keywords reveal">CONNECT. SHOWCASE. SELL. GROW.</h2>
        <h3 class="biz-main-heading reveal" style="transition-delay:0.1s;">Bring your business closer to the community.</h3>
        <p class="biz-description reveal" style="transition-delay:0.2s;">
            Discover the vibrant local businesses of Vinzons and Talisay! Support homegrown 
            entrepreneurs, explore unique products, and book services directly through our platform. 
            Local business owners can register to showcase their products and services, reaching 
            more customers in just a few clicks.
        </p>
    </div>
</section>

<!-- DIRECTORY SECTION -->
<section class="directory-section">
    <h2 class="section-title reveal">BUSINESS DIRECTORY</h2>
    
    <div class="category-grid stagger-children">
        <a href="#" class="category-card reveal">
            <i class="bi bi-egg-fried"></i>
            <span>Food & Restaurants</span>
        </a>
        <a href="#" class="category-card reveal">
            <i class="bi bi-house-heart"></i>
            <span>Resorts & Homestays</span>
        </a>
        <a href="#" class="category-card reveal">
            <i class="bi bi-bag-check"></i>
            <span>Pasalubongs</span>
        </a>
        <a href="#" class="category-card reveal">
            <i class="bi bi-gear-wide-connected"></i>
            <span>Services</span>
        </a>
    </div>

    <div class="support-local-wrap reveal">
        <div class="support-line"></div>
        <div class="support-text">
            <p class="small-text">Support</p>
            <h2 class="large-text">LOCAL</h2>
        </div>
        <div class="support-line line-right"></div>
    </div>

    <h2 class="section-title reveal">FEATURED BUSINESS</h2>

    <div class="featured-grid stagger-children">
        <div class="business-card reveal reveal-scale">
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

        <div class="business-card reveal reveal-scale">
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

        <div class="business-card reveal reveal-scale">
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

        <div class="business-card reveal reveal-scale">
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

<!-- LISTINGS SECTION -->
<section class="listings-wrapper">
    <div class="listings-grid">
        
        <div class="listing-col">
            <h3 class="col-header reveal">Restaurants & Cafes</h3>
            
            <div class="list-item reveal reveal-left">
                <div class="list-icon"><i class="bi bi-cup-hot"></i></div>
                <div class="list-info">
                    <h5>Liham Cafe</h5>
                    <p>Barangay Poblacion</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item reveal reveal-left" style="transition-delay:0.1s;">
                <div class="list-icon"><i class="bi bi-cake2"></i></div>
                <div class="list-info">
                    <h5>Cakefrost Vinzons</h5>
                    <p>Near Town Plaza</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item reveal reveal-left" style="transition-delay:0.2s;">
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
            <h3 class="col-header reveal">Resorts & Stays</h3>
            
            <div class="list-item reveal">
                <div class="list-icon"><i class="bi bi-building"></i></div>
                <div class="list-info">
                    <h5>Erica Resort</h5>
                    <p>Coastal Area, Vinzons</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item reveal" style="transition-delay:0.1s;">
                <div class="list-icon"><i class="bi bi-house-door"></i></div>
                <div class="list-info">
                    <h5>Casa Indan Resort</h5>
                    <p>Barangay Sabang</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item reveal" style="transition-delay:0.2s;">
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
            <h3 class="col-header reveal">Local Services</h3>
            
            <div class="list-item reveal reveal-right">
                <div class="list-icon"><i class="bi bi-tsunami"></i></div>
                <div class="list-info">
                    <h5>Calaguas Island Trips</h5>
                    <p>Vinzons Port</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item reveal reveal-right" style="transition-delay:0.1s;">
                <div class="list-icon"><i class="bi bi-bank"></i></div>
                <div class="list-info">
                    <h5>Museum Tour</h5>
                    <p>W. Vinzons Shrine</p>
                    <p>09123456879</p>
                    <a href="#" class="contact-btn">Message now</a>
                </div>
            </div>

            <div class="list-item reveal reveal-right" style="transition-delay:0.2s;">
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

<!-- REGISTRATION SECTION -->
<section class="reg-container">
    <div class="reg-banner">
        <h2>REGISTER YOUR BUSINESS</h2>
    </div>

    <div class="registration-card reveal reveal-scale">
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
            <a href="register-business.php" class="apply-button">APPLY BUSINESS</a>
        </div>
    </div>
</section>

<!-- ============================================
     SCROLL REVEAL + ANIMATION JAVASCRIPT
     ============================================ */ -->
<script>
(function () {
    'use strict';

    /* ---- Intersection Observer for .reveal elements ---- */
    var revealEls = document.querySelectorAll('.reveal');
    var sectionTitles = document.querySelectorAll('.section-title');
    var colHeaders = document.querySelectorAll('.col-header');
    var supportWraps = document.querySelectorAll('.support-local-wrap');

    var observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -40px 0px'
    };

    var revealObserver = new IntersectionObserver(function (entries) {
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

    /* ---- Section title underline animation ---- */
    var titleObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                titleObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    sectionTitles.forEach(function (el) { titleObserver.observe(el); });
    colHeaders.forEach(function (el) { titleObserver.observe(el); });

    /* ---- Support local wrap lines ---- */
    var lineObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                lineObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    supportWraps.forEach(function (el) { lineObserver.observe(el); });

    /* ---- Tilt effect on business cards ---- */
    var bizCards = document.querySelectorAll('.business-card, .biz-card');
    bizCards.forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var cx = rect.width / 2;
            var cy = rect.height / 2;
            var rotateX = ((y - cy) / cy) * -6;
            var rotateY = ((x - cx) / cx) * 6;
            card.style.transform = 'translateY(-12px) perspective(600px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
        });
        card.addEventListener('mouseleave', function () {
            card.style.transform = '';
        });
    });

    /* ---- Counter animation for any .count-up elements (future use) ---- */
    function animateCount(el, target, duration) {
        var start = 0;
        var step = target / (duration / 16);
        var timer = setInterval(function () {
            start += step;
            if (start >= target) { start = target; clearInterval(timer); }
            el.textContent = Math.floor(start).toLocaleString();
        }, 16);
    }

    var countEls = document.querySelectorAll('.count-up');
    if (countEls.length) {
        var countObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var target = parseInt(entry.target.getAttribute('data-target'), 10);
                    animateCount(entry.target, target, 1500);
                    countObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        countEls.forEach(function (el) { countObserver.observe(el); });
    }

    /* ---- Category card icon bounce on click ---- */
    var catCards = document.querySelectorAll('.category-card');
    catCards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            var icon = card.querySelector('i');
            if (!icon) return;
            icon.style.animation = 'none';
            icon.style.transform = 'scale(0.7)';
            setTimeout(function () {
                icon.style.transform = '';
                icon.style.animation = '';
            }, 200);
        });
    });

    /* ---- Apply button shimmer on hover (already handled by CSS) ---- */

    /* ---- Smooth page entrance ---- */
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease';
    window.addEventListener('load', function () {
        document.body.style.opacity = '1';
    });

})();
</script>

<?php require BASE_PATH . '/includes/footer.php'; ?>