<?php

declare(strict_types=1);

$pageTitle = 'Products';
$activeNav = 'products';
require_once dirname(__DIR__) . '/bootstrap.php';

$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
$q = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, b.business_name, b.id AS business_id FROM products p JOIN businesses b ON b.id = p.business_id WHERE b.status = 'approved'";
$params = [];
if ($category !== '') {
    $sql .= ' AND p.category = ?';
    $params[] = $category;
}
if ($q !== '') {
    $sql .= ' AND (p.product_name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($sort === 'price_asc') {
    $sql .= ' ORDER BY p.price ASC';
} elseif ($sort === 'price_desc') {
    $sql .= ' ORDER BY p.price DESC';
} else {
    $sql .= ' ORDER BY p.created_at DESC';
}
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$featured = db()->query(
    "SELECT p.*, b.business_name FROM products p JOIN businesses b ON b.id = p.business_id WHERE b.status='approved' AND p.is_featured=1 ORDER BY p.price DESC LIMIT 1"
)->fetch();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<style>

    @import url('https://fonts.googleapis.com/css2?family=Bilbo+Swash+Caps&family=Bungee&family=Inter:wght@400;700;900&family=Lisu+Bosa:wght@400;700&display=swap');
:root {
    --dark-navy: #051024;
    --amber-orange: #f2a63d;
    --primary-green: #28a745;
    --seamless-bg: #FFF8F8;
}

/* Base Seamless Background */
body, 
.product-showcase, 
.deals-section, 
.bottom-grid-section, 
.handicrafts-section, 
.produce-section, 
.seller-section {
    background-color: var(--seamless-bg) !important;
}

/* Hero Section */
.local-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), 
                url('assets/images/coconuts-bg.jpg'); 
    background-size: cover;
    background-position: center;
    padding: 100px 8%;
    color: white;
}

.local-hero-title {
    font-family: 'Bungee';
    font-size: 4.5rem;
    line-height: 1;
    margin: 0;
    text-transform: uppercase;
}

.local-hero-tagline {
    font-family: 'Bilbo Swash Caps', cursive;
    font-size: 2.5rem;
    margin-top: 10px;
}

/* Top Product Showcase */
.product-showcase {
    padding: 60px 8% 80px;
    text-align: center;
}

.product-grid-top {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.product-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    height: 350px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.product-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover img {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 20px 30px;
    text-align: left;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
}

.card-label {
    font-family: 'Bungee';
    color: white;
    font-size: 1.8rem;
    margin: 0;
}

.product-footer-text {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.25rem;
    color: var(--dark-navy);
    max-width: 900px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Deals Section */
.deals-section {
    padding: 60px 10%;
    text-align: center;
}

.deals-header-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 50px;
}

.divider-line {
    flex-grow: 1;
    height: 5px;
    background-color: var(--primary-green);
    max-width: 450px;
}

.deals-main-title {
    font-family: 'Bungee';
    font-size: 3.5rem;
    color: var(--amber-orange);
    line-height: 1;
}

.deals-main-title span {
    display: block;
    font-family: 'Inter', sans-serif;
    font-size: 1.5rem;
    color: var(--dark-navy);
    text-transform: uppercase;
}

.deals-intro {
    text-align: left;
    margin-bottom: 40px;
}

.deals-intro h2 {
    font-family: 'Bungee';
    color: var(--amber-orange);
    font-size: 2rem;
    margin-bottom: 10px;
}

.deals-intro p {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.25rem;
    color: #444;
    max-width: 600px;
}

.deal-card {
    display: flex;
    align-items: center;
    gap: 40px;
    padding: 40px;
    border-radius: 25px;
    background: radial-gradient(circle at center, #d4fcd6 0%, #a8f0ad 100%);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    text-align: left;
    margin-bottom: 60px;
}

.card-image-wrap {
    flex: 0 0 45%;
}

.card-image-wrap img {
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.card-content {
    flex: 1;
}

.card-tag {
    font-family: 'Lisu Bosa', serif;
    color: #1b5e20;
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: 1px;
}

.card-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 2.2rem;
    color: #0d2e0f;
    margin: 5px 0;
}

.card-desc {
    font-family: 'Lisu Bosa', serif;
    font-size: 1.2rem;
    color: #2e4d2f;
    margin-bottom: 25px;
    line-height: 1.4;
}

.order-btn {
    background-color: var(--amber-orange);
    color: #000;
    font-weight: 800;
    padding: 12px 35px;
    border: none;
    border-radius: 50px;
    font-family: 'Inter', sans-serif;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 4px 0px #c98220;
    transition: all 0.2s;
}

.order-btn:hover {
    transform: translateY(2px);
    box-shadow: 0 2px 0px #c98220;
}

/* Bottom Products Grid */
.bottom-grid-section {
    padding: 0 10% 80px;
}

.product-grid-bottom {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    max-width: 1400px;
    margin: 0 auto;
}

.card {
    position: relative;
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    height: 300px;
}

.card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.info-overlay {
    position: absolute;
    bottom: 12px;
    left: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    padding: 12px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.text-content {
    flex: 1;
    padding-right: 8px;
}

.text-content h3 {
    margin: 0;
    font-size: 0.9rem;
    color: #1a237e;
    font-weight: 800;
    text-transform: uppercase;
}

.text-content p {
    margin: 4px 0 0 0;
    font-size: 0.65rem;
    color: #444;
    line-height: 1.2;
}

.seller-btn {
    background-color: #ffb300;
    color: #000;
    border: none;
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 0.65rem;
    font-weight: bold;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.3s;
}

.seller-btn:hover {
    background-color: #ffa000;
}

@media (max-width: 1100px) {
    .product-grid-bottom { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .product-grid-bottom { grid-template-columns: 1fr; }
}

/* Section Divider */
.section-divider {
    height: 6px;
    background-color: #28a745;
    width: 85%;
    margin: 40px auto;
    border-radius: 10px;
}

.handicrafts-section {
    padding: 20px 10% 80px;
}

.category-intro {
    text-align: left;
    margin-bottom: 30px;
}

.category-title {
    font-family: 'Bungee';
    color: #f2a63d;
    font-size: 2.5rem;
    margin: 0;
}

.category-subtitle {
    font-family: 'Inter', sans-serif;
    color: #444;
    font-size: 1.1rem;
    margin-top: 5px;
}

.featured-handicraft-card {
    display: flex;
    align-items: center;
    gap: 40px;
    padding: 30px;
    border-radius: 25px;
    background: radial-gradient(circle at center, #e2fce4 0%, #b8f2bc 100%);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    text-align: left;
    margin-bottom: 50px;
}

.produce-section {
    padding: 20px 10% 80px;
}

.produce-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 25px;
    margin: 0 auto;
}
/* Seller Section - Compact Version */
.seller-section {
    padding: 40px 10%;
}

.section-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 2rem; /* Reduced from 2.5rem */
    color: #0d2e0f;
    margin-bottom: 30px;
}

.seller-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px; /* Reduced gap */
}

.seller-card {
    background-color: #bdf7b2;
    border-radius: 15px; /* Slightly sharper corners */
    padding: 15px; /* Reduced from 25px */
    display: flex;
    align-items: center;
    gap: 15px; /* Reduced gap */
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    height: 140px; /* Fixed height to keep them uniform */
}

.logo-placeholder {
    width: 80px; /* Reduced from 100px */
    height: 80px; /* Reduced from 100px */
    background-color: #d1d5db;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-shrink: 0;
}

.logo-placeholder i {
    font-size: 2rem;
    color: #1f2937;
}

.seller-info h3 {
    margin: 0 0 3px 0;
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 1rem; /* Reduced from 1.2rem */
    color: #0d2e0f;
}

.seller-info p {
    margin: 0 0 8px 0;
    font-size: 0.75rem; /* Reduced from 0.85rem */
    color: #1b431e;
    line-height: 1.2;
    max-width: 250px;
    /* Limit to 2 lines to prevent height overflow */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.contact-links {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.contact-links a {
    text-decoration: none;
    color: #000;
    font-size: 0.75rem; /* Reduced from 0.85rem */
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.contact-links i {
    font-size: 0.8rem;
}

@media (max-width: 900px) {
    .seller-grid { grid-template-columns: 1fr; }
    .seller-card { height: auto; }
}
</style>

<section class="local-hero">
    <h1 class="local-hero-title">SUPORTA LOKAL,<br>LIKHA LOKAL</h1>
    <p class="local-hero-tagline">Mga produktong tunay, gawa ng sariling komunidad.</p>
</section>

<section class="product-showcase">
    <div class="product-grid-top">
        <div class="product-card">
            <img src="assets/images/kakanin.jpg" alt="Local Delicacies">
            <div class="card-overlay">
                <h2 class="card-label">Local Delicacies</h2>
            </div>
        </div>

        <div class="product-card">
            <img src="assets/images/handicrafts.jpg" alt="Handicrafts">
            <div class="card-overlay">
                <h2 class="card-label">Handicrafts</h2>
            </div>
        </div>
    </div>

    <p class="product-footer-text">
        From handcrafted souvenirs to fresh harvests and local delicacies, discover products made with skill, tradition, and the flavors of Vinzons—crafted by nature, perfected by the community.
    </p>
</section>

<section class="deals-section">
    <div class="deals-header-wrapper">
        <div class="divider-line"></div>
        <h1 class="deals-main-title">
            <span>See</span> DEALS
        </h1>
        <div class="divider-line"></div>
    </div>

    <div class="deals-intro">
        <h2>LOCAL DELICACIES</h2>
        <p>Taste the flavors and take home the craftsmanship of our towns. Every product reflects the creativity and livelihood of our local makers.</p>
    </div>

    <div class="deal-card">
        <div class="card-image-wrap">
            <img src="angko-image.jpg" alt="Vinzons Best Delicacy - Angko">
        </div>
        <div class="card-content">
            <span class="card-tag">VINZONS’ BEST DELICACY</span>
            <h3 class="card-title">Angko</h3>
            <p class="card-desc">
                A chewy steamed rice cake made from glutinous rice and coconut, wrapped in fragrant banana leaves.
            </p>
            <button class="order-btn">
                Order now <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</section>

<section class="bottom-grid-section">
    <div class="product-grid-bottom">
        <div class="card">
            <img src="assets/images/pandecillos.jpg" alt="Pandecillos">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Pandecillos</h3>
                    <p>Fluffy bite-sized pastries with a sweet center.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/pili_brittle.jpg" alt="Pili Brittle">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Pili Brittle</h3>
                    <p>Thin, crunchy caramel candy with pili nuts.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/pili_nuts.jpg" alt="Pili Nuts">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Pili Nuts</h3>
                    <p>Rich, creamy native nuts, roasted or glazed.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/pili_tarts.jpg" alt="Pili Tarts">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Pili Tarts</h3>
                    <p>Mini tarts with buttery custard and pili.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/sapin_sapin.jpg" alt="Sapin-Sapin">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Sapin-Sapin</h3>
                    <p>Colorful layered glutinous rice cake.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/kakanin_alt.jpg" alt="Kakanin">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Kakanin</h3>
                    <p>Traditional Filipino rice cakes.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/biko.jpg" alt="Biko">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Biko</h3>
                    <p>Sticky rice cake with brown sugar.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>

        <div class="card">
            <img src="assets/images/maja_blanca.jpg" alt="Maja Blanca">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Maja Blanca</h3>
                    <p>Creamy coconut pudding with corn.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<section class="handicrafts-section">
    <div class="category-intro">
        <h1 class="category-title">HANDICRAFTS</h1>
        <p class="category-subtitle">Likha ng Komunidad – Crafted by local hands, made with heart.</p>
    </div>

    <div class="featured-handicraft-card">
        <div class="card-image-wrap">
            <img src="assets/images/coconut-shells-main.jpg" alt="Vinzons Best Handicraft">
        </div>
        <div class="card-content">
            <span class="card-tag">VINZONS’ BEST HANDICRAFT</span>
            <h3 class="card-title">Coconut Shell Crafts</h3>
            <p class="card-desc">Small décor items, jewelry holders, or keychains made from polished coconut shells.</p>
            <button class="order-btn">Order now <i class="bi bi-arrow-right"></i></button>
        </div>
    </div>

    <div class="product-grid-bottom">
        <div class="card">
            <img src="assets/images/coconut-shell.jpg" alt="Coconut Shell Crafts">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Coconut Shell Crafts</h3>
                    <p>Polished décor items and jewelry holders.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/woven-mats.jpg" alt="Nito / Pandan Woven Mats">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Nito / Pandan Mats</h3>
                    <p>Durable floor and table mats with native patterns.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/baskets.jpg" alt="Bamboo & Rattan Baskets">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Bamboo Baskets</h3>
                    <p>Handwoven storage bins and decorative trays.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/shell-jewelry.jpg" alt="Shell Jewelry">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Shell Jewelry</h3>
                    <p>Necklaces and ornaments from local seashells.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/woven-bags.jpg" alt="Handwoven Bags">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Handwoven Bags</h3>
                    <p>Stylish pouches and wallets from abaca fibers.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/mini-houses.jpg" alt="Miniature Houses">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Miniature Houses</h3>
                    <p>Replica Bahay Kubo made from bamboo and nipa.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/keychains.jpg" alt="Wooden Keychains">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Wooden Keychains</h3>
                    <p>Hand-carved trinkets showcasing local culture.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/table-runners.jpg" alt="Woven Table Runners">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Woven Runners</h3>
                    <p>Intricate placemats and runners for dining.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<section class="produce-section">
    <div class="category-intro">
        <h1 class="category-title">FRESH PRODUCE</h1>
        <p class="category-subtitle">Ani ng Komunidad – Fresh from local farms, nurtured with care.</p>
    </div>

    <div class="product-grid-bottom produce-grid">
        <div class="card">
            <img src="assets/images/pineapple.jpg" alt="Pineapple">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Pineapple</h3>
                    <p>Sweet tropical pineapples grown in nearby plantations.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/coconut.jpg" alt="Coconut">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Coconut</h3>
                    <p>Freshly harvested; used for drinks, cooking, or oil.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/banana.jpg" alt="Banana">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Banana</h3>
                    <p>Native and commercial varieties, sold in fresh bunches.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/root-crops.jpg" alt="Root Crops">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Root Crops</h3>
                    <p>Taro, cassava, and sweet potatoes harvested daily.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/seafood.jpg" alt="Fresh Fish and Seafood">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Fish & Seafood</h3>
                    <p>Locally caught fish and shellfish from coastal waters.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/tropical-fruits.jpg" alt="Tropical Fruits">
            <div class="info-overlay">
                <div class="text-content">
                    <h3>Tropical Fruits</h3>
                    <p>Seasonal fruits including lanzones, guava, and papaya.</p>
                </div>
                <button class="seller-btn">See Sellers</button>
            </div>
        </div>
    </div>
</section>

<!-- Green Divider before List of Shops -->
<div class="section-divider"></div>

<section class="seller-section">
    <h1 class="section-title">LIST OF SHOPS/SELLERS</h1>
    <div class="seller-grid">
        <div class="seller-card">
            <div class="logo-placeholder"><i class="bi bi-house-door-fill"></i></div>
            <div class="seller-info">
                <h3>Vinzons Fruit Stand</h3>
                <p>Fresh tropical fruits like pineapple, mangoes, and bananas from local farms.</p>
                <div class="contact-links">
                    <a href="tel:09123456879"><i class="bi bi-telephone-fill"></i> 09123456879</a>
                    <a href="#"><i class="bi bi-envelope-fill"></i> Message now</a>
                </div>
            </div>
        </div>
        <div class="seller-card">
            <div class="logo-placeholder"><i class="bi bi-house-door-fill"></i></div>
            <div class="seller-info">
                <h3>Native Touch Souvenirs</h3>
                <p>Coconut shell crafts, miniature bahay kubo, and decorative ornaments.</p>
                <div class="contact-links">
                    <a href="tel:09123456879"><i class="bi bi-telephone-fill"></i> 09123456879</a>
                    <a href="#"><i class="bi bi-envelope-fill"></i> Message now</a>
                </div>
            </div>
        </div>
        <div class="seller-card">
            <div class="logo-placeholder"><i class="bi bi-house-door-fill"></i></div>
            <div class="seller-info">
                <h3>Baybay Market Seafood</h3>
                <p>Fresh fish, shrimps, and crabs straight from local fishermen.</p>
                <div class="contact-links">
                    <a href="tel:09123456879"><i class="bi bi-telephone-fill"></i> 09123456879</a>
                    <a href="#"><i class="bi bi-envelope-fill"></i> Message now</a>
                </div>
            </div>
        </div>
        <div class="seller-card">
            <div class="logo-placeholder"><i class="bi bi-house-door-fill"></i></div>
            <div class="seller-info">
                <h3>Sweet Treats Vinzons</h3>
                <p>Pandecillos, pili tart, angko, sapin-sapin, and biko.</p>
                <div class="contact-links">
                    <a href="tel:09123456879"><i class="bi bi-telephone-fill"></i> 09123456879</a>
                    <a href="#"><i class="bi bi-envelope-fill"></i> Message now</a>
                </div>
            </div>
        </div>
        <div class="seller-card">
            <div class="logo-placeholder"><i class="bi bi-house-door-fill"></i></div>
            <div class="seller-info">
                <h3>Vinzons Handicrafts</h3>
                <p>Woven bags, mats, baskets, and table runners.</p>
                <div class="contact-links">
                    <a href="tel:09123456879"><i class="bi bi-telephone-fill"></i> 09123456879</a>
                    <a href="#"><i class="bi bi-envelope-fill"></i> Message now</a>
                </div>
            </div>
        </div>
        <div class="seller-card">
            <div class="logo-placeholder"><i class="bi bi-house-door-fill"></i></div>
            <div class="seller-info">
                <h3>Coastal Crafts Talisay</h3>
                <p>Driftwood art, shell ornaments, and miniature boats.</p>
                <div class="contact-links">
                    <a href="tel:09123456879"><i class="bi bi-telephone-fill"></i> 09123456879</a>
                    <a href="#"><i class="bi bi-envelope-fill"></i> Message now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>