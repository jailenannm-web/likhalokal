-- =============================================================================
-- LikhaLokal — restore ALL visible content (safe: no TRUNCATE, no DELETE)
-- Run in phpMyAdmin on database `likhalokal_db`
-- Use when marketplace/tourism pages look empty after partial imports.
-- =============================================================================

USE likhalokal_db;
SET NAMES utf8mb4;

SET @pwd := '$2y$10$lqdnAprF.JIYMI2feqX.O.2GowPh/BZp/LbiYVMUAQl6gY.fgzDDi';
SET @admin := 1;

-- =============================================================================
-- CORE USERS (admin, sellers, local users) — required for login demo
-- =============================================================================
INSERT IGNORE INTO users (id, full_name, email, password_hash, contact_number, role, status, created_at, updated_at) VALUES
(1, 'Tourism Admin', 'admin@likhalokal.com', @pwd, '09000000001', 'admin', 'active', NOW(), NOW()),
(2, 'Jannah Cruz', 'jannah@likhalokal.com', @pwd, '09123456789', 'seller', 'active', NOW(), NOW()),
(3, 'Rhuwen Santos', 'rhumens@likhalokal.com', @pwd, '09187654321', 'seller', 'active', NOW(), NOW()),
(4, 'Ley Ubana', 'user@likhalokal.com', @pwd, '09222222222', 'local_user', 'active', NOW(), NOW()),
(5, 'Maria Tourist', 'tourist@likhalokal.com', @pwd, '09333333333', 'local_user', 'active', NOW(), NOW());

-- Directory / travel agency seller accounts
INSERT IGNORE INTO users (id, full_name, email, password_hash, contact_number, role, status, created_at, updated_at) VALUES
(6,  'Vinzons Fruit Stand Owner',      'fruitstand@likhalokal.com',      @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(7,  'Coastal Crafts Owner',           'coastalcrafts@likhalokal.com',   @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(8,  'Native Touch Owner',             'nativetouch@likhalokal.com',     @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(9,  'Sweet Treats Owner',             'sweettreats@likhalokal.com',     @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(10, 'Liham Cafe Owner',               'lihamcafe@likhalokal.com',       @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(11, 'Cakefrost Owner',                'cakefrost@likhalokal.com',       @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(12, 'Maxicup Owner',                  'maxicup@likhalokal.com',         @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(13, 'Erica Resort Owner',             'ericaresort@likhalokal.com',     @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(14, 'Casa Indan Owner',               'casaindan@likhalokal.com',       @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(15, 'Calaguas Paradise Owner',        'calaguasparadise@likhalokal.com',@pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(16, 'Calaguas Trips Owner',           'calaguastrips@likhalokal.com',   @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(17, 'Museum Tour Owner',              'museumtour@likhalokal.com',      @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(18, 'Pili Artisan Owner',             'piliartisan@likhalokal.com',     @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(19, 'Baybreeze Escapes Owner',        'baybreeze@likhalokal.com',       @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(20, 'Sunrise Shores Owner',           'sunriseshores@likhalokal.com',   @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(21, 'Buhangin Voyages Owner',         'buhanginvoyages@likhalokal.com', @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(22, 'Mangrove Trail Owner',           'mangrovetrail@likhalokal.com',   @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(23, 'Isla Norte Tours Owner',         'islanorte@likhalokal.com',       @pwd, '09123456879', 'seller', 'active', NOW(), NOW()),
(24, 'Green Coast Owner',              'greencoast@likhalokal.com',      @pwd, '09123456879', 'seller', 'active', NOW(), NOW());

-- =============================================================================
-- CORE BUSINESSES — Jannah's & Rhuwen's Pasalubong (marketplace)
-- =============================================================================
INSERT IGNORE INTO businesses (id, user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, promotional_note, status, approved_by, approved_at, created_at, updated_at) VALUES
(1, 2, 'Jannah''s Pasalubong', 'pasalubong', 'Home of authentic Bicol delicacies and pasalubong treats made fresh for Vinzons and visitors.', '09123456789', 'jannah@likhalokal.com', 'Poblacion I', 'Poblacion I', 14.1720000, 122.9450000, 'Mon–Sat 8:00 AM – 6:00 PM', '["GCash","Maya","Cash on pickup","Bank transfer"]', 'Free taste on weekends for orders Php500+', 'approved', @admin, NOW(), NOW(), NOW()),
(2, 3, 'Rhuwen''s Pasalubong', 'pasalubong', 'Specialty handicrafts, woven goods, and curated local souvenirs from Vinzons artisans.', '09187654321', 'rhumens@likhalokal.com', 'Talisay', 'Talisay', 14.1800000, 122.9500000, 'Daily 9:00 AM – 5:00 PM', '["GCash","Cash on pickup","Pay upon booking"]', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(3, 2, 'Jannah''s Kitchen', 'food_vendor', 'Small food counter — pending demo application.', '09120001111', 'jannah@likhalokal.com', 'Market area', 'Poblacion II', NULL, NULL, NULL, '["Cash on pickup"]', NULL, 'pending', NULL, NULL, NOW(), NOW());

-- Featured local-business cards (old hardcoded directory)
INSERT IGNORE INTO businesses (id, user_id, business_name, business_type, description, contact_number, email, address, barangay, cover_image, status, approved_by, approved_at, created_at, updated_at) VALUES
(10, 6, 'Vinzons Fruit Stand', 'fresh_produce', 'Fresh tropical fruits like pineapple, mangoes, and bananas from local farms.', '09123456879', 'fruitstand@likhalokal.com', 'Vinzons', 'Poblacion', 'images/fruitstand.png', 'approved', @admin, NOW(), NOW(), NOW()),
(11, 7, 'Coastal Crafts Vinzons', 'craft_business', 'Driftwood art, shell ornaments, and miniature boats handcrafted locally.', '09123456879', 'coastalcrafts@likhalokal.com', 'Vinzons', 'Talisay', 'images/coastalcraft.png', 'approved', @admin, NOW(), NOW(), NOW()),
(12, 8, 'Native Touch Souvenirs', 'craft_business', 'Coconut shell crafts, miniature bahay kubo, and decorative ornaments.', '09123456879', 'nativetouch@likhalokal.com', 'Vinzons', 'Poblacion', 'images/nativetouch.png', 'approved', @admin, NOW(), NOW(), NOW()),
(13, 9, 'Sweet Treats Vinzons', 'pasalubong', 'Pandecillos, pili tart, angko, sapin-sapin, and local delicacies.', '09123456879', 'sweettreats@likhalokal.com', 'Vinzons', 'Poblacion', 'images/sweettreats.png', 'approved', @admin, NOW(), NOW(), NOW()),
(14, 10, 'Liham Cafe', 'restaurant', 'Local cafe serving coffee and snacks in Poblacion.', '09123456879', 'lihamcafe@likhalokal.com', 'Barangay Poblacion', 'Poblacion', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(15, 11, 'Cakefrost Vinzons', 'restaurant', 'Cakes and pastries near the town plaza.', '09123456879', 'cakefrost@likhalokal.com', 'Near Town Plaza', 'Poblacion', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(16, 12, 'Maxicup Vinzons', 'food_vendor', 'Refreshing drinks and snacks.', '09123456879', 'maxicup@likhalokal.com', 'Barangay San Isidro', 'San Isidro', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(17, 13, 'Erica Resort', 'resort', 'Coastal resort accommodations in Vinzons.', '09123456879', 'ericaresort@likhalokal.com', 'Coastal Area, Vinzons', 'Coastal', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(18, 14, 'Casa Indan Resort', 'resort', 'Relaxing resort stay in Sabang.', '09123456879', 'casaindan@likhalokal.com', 'Barangay Sabang', 'Sabang', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(19, 15, 'Calaguas Paradise Resort', 'resort', 'Beach resort near Mahabang Buhangin.', '09123456879', 'calaguasparadise@likhalokal.com', 'Mahabang Buhangin', 'Calaguas', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(20, 16, 'Calaguas Island Trips', 'travel_agency', 'Island-hopping and boat tours from Vinzons Port.', '09123456879', 'calaguastrips@likhalokal.com', 'Vinzons Port', 'Poblacion', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(21, 17, 'Museum Tour', 'service', 'Guided tours at the Wenceslao Vinzons Shrine and museum.', '09123456879', 'museumtour@likhalokal.com', 'W. Vinzons Shrine', 'Poblacion I', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(22, 18, 'Pili Artisan Workshop', 'craft_business', 'Hands-on pili crafts and souvenir workshops.', '09123456879', 'piliartisan@likhalokal.com', 'Barangay Minaogan', 'Minaogan', NULL, 'approved', @admin, NOW(), NOW(), NOW()),
(23, 19, 'Baybreeze Escapes', 'travel_agency', 'Island-hopping specialists offering budget and premium tours to Calaguas and nearby beaches.', '09123456879', 'baybreeze@likhalokal.com', 'Vinzons', 'Poblacion', 'images/likhalokal-logo.png', 'approved', @admin, NOW(), NOW(), NOW()),
(24, 20, 'Sunrise Shores Travel Co.', 'travel_agency', 'Sunrise beach photography tours, snorkeling, and coastal sightseeing.', '09123456879', 'sunriseshores@likhalokal.com', 'Vinzons', 'Poblacion', 'images/likhalokal-logo.png', 'approved', @admin, NOW(), NOW(), NOW()),
(25, 21, 'Mahabang Buhangin Voyages', 'travel_agency', 'Kayak tours, boat rides, and eco-trips around the San Nicolas Mangrove Forest.', '09123456879', 'buhanginvoyages@likhalokal.com', 'Vinzons', 'Coastal', 'images/likhalokal-logo.png', 'approved', @admin, NOW(), NOW(), NOW()),
(26, 22, 'Mangrove Trail Adventures', 'travel_agency', 'Kayak tours, boat rides, and eco-trips around mangrove trails.', '09123456879', 'mangrovetrail@likhalokal.com', 'Vinzons', 'Coastal', 'images/likhalokal-logo.png', 'approved', @admin, NOW(), NOW(), NOW()),
(27, 23, 'Isla Norte Backpacking Tours', 'travel_agency', 'Backpacker packages to Calaguas, Quinamanukan, and lesser-known beaches.', '09123456879', 'islanorte@likhalokal.com', 'Vinzons Port', 'Poblacion', 'images/likhalokal-logo.png', 'approved', @admin, NOW(), NOW(), NOW()),
(28, 24, 'Green Coast Expeditions', 'travel_agency', 'Nature trekking, fishing trips, and waterfall adventures.', '09123456879', 'greencoast@likhalokal.com', 'Vinzons', 'Poblacion', 'images/likhalokal-logo.png', 'approved', @admin, NOW(), NOW(), NOW());

-- =============================================================================
-- PRODUCTS — full marketplace catalog with images (Jannah's & Rhuwen's)
-- =============================================================================
INSERT IGNORE INTO products (id, business_id, product_name, category, description, price, image, availability, is_featured, created_at, updated_at) VALUES
(1, 1, 'Angko', 'local_delicacy', 'Sticky rice peanut snack native to Vinzons.', 120.00, 'images/angko2.png', 'available', 1, NOW(), NOW()),
(2, 1, 'Pandecillos', 'local_delicacy', 'Soft local bread rolls.', 80.00, 'images/pandecillos.png', 'available', 1, NOW(), NOW()),
(3, 1, 'Pili Brittle', 'local_delicacy', 'Crunchy caramelized pili candy.', 150.00, 'images/pili brittle.png', 'available', 0, NOW(), NOW()),
(4, 1, 'Pili Nuts', 'local_delicacy', 'Roasted premium pili.', 200.00, 'images/pili nut.png', 'available', 0, NOW(), NOW()),
(5, 1, 'Pili Tarts', 'local_delicacy', 'Buttery tarts with pili filling.', 220.00, 'images/pili tart.png', 'available', 0, NOW(), NOW()),
(6, 1, 'Sapin-Sapin', 'local_delicacy', 'Layered glutinous rice cake.', 180.00, 'images/sapinsapin.png', 'available', 0, NOW(), NOW()),
(7, 1, 'Kakanin', 'local_delicacy', 'Assorted rice cakes — bilao available.', 350.00, 'images/kakanin.png', 'available', 1, NOW(), NOW()),
(8, 1, 'Biko', 'local_delicacy', 'Sweet sticky rice with latik.', 140.00, 'images/biko.png', 'available', 0, NOW(), NOW()),
(9, 1, 'Maja Blanca', 'local_delicacy', 'Coconut milk pudding with corn.', 130.00, 'images/majablanca.png', 'available', 0, NOW(), NOW()),
(10, 1, 'Coco Jam', 'local_delicacy', 'Slow-cooked coconut jam.', 160.00, 'images/cocojam.png', 'available', 0, NOW(), NOW()),
(11, 1, 'Leche Flan de Coco', 'local_delicacy', 'Coconut leche flan.', 200.00, 'images/letcheflancoco.png', 'available', 0, NOW(), NOW()),
(12, 1, 'Bukayo', 'local_delicacy', 'Sweetened coconut strips.', 90.00, 'images/bukayo.png', 'available', 0, NOW(), NOW()),
(13, 1, 'Hinalo', 'local_delicacy', 'Traditional mixed rice delicacy.', 110.00, 'images/hinalo.png', 'available', 0, NOW(), NOW()),
(14, 2, 'Coconut Shell Crafts', 'handicraft', 'Decorative bowls and utensils from coconut shell.', 250.00, 'images/coconut shell craft.png', 'available', 1, NOW(), NOW()),
(15, 2, 'Nito / Pandan Woven Mats', 'handicraft', 'Handwoven mats from local fibers.', 450.00, 'images/nito.png', 'available', 0, NOW(), NOW()),
(16, 2, 'Bamboo & Rattan Baskets', 'handicraft', 'Durable baskets for home and market.', 320.00, 'images/bamboo.png', 'available', 0, NOW(), NOW()),
(17, 2, 'Shell Jewelry & Ornaments', 'handicraft', 'Coastal-inspired accessories.', 180.00, 'images/shell.png', 'available', 0, NOW(), NOW()),
(18, 2, 'Handwoven Bags & Pouches', 'handicraft', 'Eco bags by community weavers.', 400.00, 'images/handwooven.png', 'available', 0, NOW(), NOW()),
(19, 2, 'Miniature Bicolano Houses', 'handicraft', 'Mini bahay kubo souvenirs.', 150.00, 'images/bicolanohouse.png', 'available', 0, NOW(), NOW()),
(20, 2, 'Wooden Keychains & Carvings', 'handicraft', 'Carved keychains and tokens.', 75.00, 'images/keychain.png', 'available', 0, NOW(), NOW()),
(21, 2, 'Woven Table Runners & Placemats', 'handicraft', 'Table accents for homes and cafes.', 380.00, 'images/tableplacement.png', 'available', 0, NOW(), NOW()),
(22, 1, 'Pineapple', 'fresh_produce', 'Sweet Vinzons pineapples.', 60.00, 'images/pineapple.png', 'available', 0, NOW(), NOW()),
(23, 1, 'Coconut', 'fresh_produce', 'Fresh mature coconut.', 40.00, 'images/coconut.png', 'available', 0, NOW(), NOW()),
(24, 1, 'Banana Varieties', 'fresh_produce', 'Saba, lakatan, and local varieties.', 55.00, 'images/banana.png', 'available', 0, NOW(), NOW()),
(25, 1, 'Root Crops & Vegetables', 'fresh_produce', 'Seasonal farm harvest bundle.', 120.00, 'images/root.png', 'available', 0, NOW(), NOW()),
(26, 1, 'Tropical Fruits Combo', 'fresh_produce', 'Curated selection of fresh local fruits.', 150.00, 'images/tropical.png', 'available', 0, NOW(), NOW()),
(30, 1, 'Fresh Catch Seafood', 'fresh_produce', 'Locally sourced fresh fish and seafood.', 320.00, 'images/fish.png', 'available', 0, NOW(), NOW()),
(100, 10, 'Tropical Fruit Bundle', 'fresh_produce', 'Seasonal mixed fruits from Vinzons farms.', 150.00, 'images/pineapple.png', 'available', 1, NOW(), NOW()),
(101, 11, 'Driftwood Ornament', 'handicraft', 'Handmade coastal driftwood decor.', 250.00, 'images/coconut shell craft.png', 'available', 1, NOW(), NOW()),
(102, 12, 'Mini Bahay Kubo', 'handicraft', 'Miniature bahay kubo souvenir.', 150.00, 'images/bicolanohouse.png', 'available', 1, NOW(), NOW()),
(103, 13, 'Assorted Kakanin Box', 'local_delicacy', 'Sampler of local rice cakes and sweets.', 350.00, 'images/kakanin.png', 'available', 1, NOW(), NOW());

-- =============================================================================
-- TOURIST ATTRACTIONS — with images from old design
-- =============================================================================
INSERT IGNORE INTO tourist_attractions (id, admin_id, attraction_name, category, description, history, travel_guide, entrance_fee, best_time_to_visit, address, latitude, longitude, image, status, created_at, updated_at) VALUES
(1, @admin, 'Saint Peter the Apostle Church', 'church', 'Historic stone church and spiritual landmark of Vinzons.', 'Built in 1811; among the oldest churches in Camarines Norte.', 'From town proper, walk or tricycle to the church plaza.', 'Free / donation', 'Early morning or late afternoon', 'Poblacion, Vinzons', 14.1725000, 122.9448000, 'images/St. Peter Church.png', 'published', NOW(), NOW()),
(2, @admin, 'Wenceslao Q. Vinzons Ancestral Home', 'heritage_site', 'National Historical Landmark honoring the hero''s residence.', 'Declared landmark; WWII resistance history.', 'Located near municipal center.', 'Free', 'Weekday mornings', 'Poblacion I, Vinzons', 14.1730000, 122.9452000, 'images/vinzonshouse.png', 'published', NOW(), NOW()),
(3, @admin, 'Calaguas Islands', 'island', 'Powdery white sand and turquoise waters.', 'Boat trips coordinated by accredited operators.', 'Check weather advisories before travel.', 'Php 150–200', 'Dry season Nov–May', 'Offshore Vinzons', 14.1000000, 122.9000000, 'images/calaguas.png', 'published', NOW(), NOW()),
(4, @admin, 'Cory Aquino Boulevard', 'landmark', 'Scenic coastal boulevard ideal for sunset walks.', 'Showcases Vinzons coastline.', 'Drive or stroll along the boulevard.', 'Free', 'Sunset hours', 'Coastal Vinzons', 14.1750000, 122.9480000, NULL, 'published', NOW(), NOW()),
(5, @admin, 'Vinzons Town Proper Heritage Area', 'cultural_site', 'Walkable heritage blocks with ancestral structures.', 'Centuries of trade and faith shaped the town core.', 'Heritage walk maps at municipal tourism.', 'Free', 'Morning to evening', 'Poblacion', 14.1728000, 122.9455000, NULL, 'published', NOW(), NOW()),
(6, @admin, 'Mangrove & Coastal Eco-Tourism', 'eco_tourism', 'Mangrove trails and coastal biodiversity.', 'Community-managed eco-tours.', 'Book with accredited guides.', 'Minimal fee', 'High tide for boat', 'Coastal barangays', 14.1680000, 122.9520000, NULL, 'published', NOW(), NOW()),
(7, @admin, 'Vinzons River', 'eco_tourism', 'Scenic river views and community fishing culture along Vinzons waterways.', NULL, 'Best visited in dry season mornings.', 'Free', 'Morning', 'Vinzons River', 14.1700000, 122.9460000, 'images/vinzonsriver.png', 'published', NOW(), NOW());

-- =============================================================================
-- EVENTS, ANNOUNCEMENTS, CULTURAL INFO
-- =============================================================================
INSERT IGNORE INTO events (id, admin_id, title, description, event_date, event_time, location, status, created_at, updated_at) VALUES
(1, @admin, 'Tacboan Festival', 'Rhythm, colors, and traditions of Vinzons — street dancing, exhibits, and cultural shows.', '2026-05-28', '08:00:00', 'Municipal grounds & plaza', 'published', NOW(), NOW()),
(2, @admin, 'Vinzons Day / Wenceslao Vinzons Birth Anniversary', 'Commemorative programs, wreath-laying, and civic parade.', '2026-09-28', '07:30:00', 'Hero monuments & LGU venues', 'published', NOW(), NOW()),
(3, @admin, 'Local Cultural Fair', 'Handicraft demos, food stalls, and live folk music.', '2026-08-15', '16:00:00', 'Baybay activity center', 'published', NOW(), NOW());

INSERT IGNORE INTO announcements (id, admin_id, title, content, status, created_at, updated_at) VALUES
(1, @admin, 'Attention Local Entrepreneurs!', 'Register your business on LikhaLokal to reach tourists and residents with a digital storefront. LGU verification applies.', 'published', NOW(), NOW()),
(2, @admin, 'Dry Season Travel Advisory', 'Plan Calaguas and coastal trips with accredited operators and monitor weather updates.', 'published', NOW(), NOW());

INSERT IGNORE INTO cultural_information (id, admin_id, title, content, category, image, status, created_at, updated_at) VALUES
(1, @admin, 'From Tacboan to Vinzons', 'The town''s story spans indigenous roots, Spanish-era missions, and the heroism of Wenceslao Q. Vinzons.', 'history', NULL, 'published', NOW(), NOW()),
(2, @admin, 'Faith and Heritage', 'Churches, plazas, and ancestral homes reflect Vinzons'' enduring faith and community memory.', 'heritage', 'images/kasaysayan.png', 'published', NOW(), NOW()),
(3, @admin, 'Livelihoods by Land and Sea', 'Farming, fishing, crafts, and tourism sustain families across 19 barangays.', 'livelihood', NULL, 'published', NOW(), NOW()),
(4, @admin, 'Tacboan Festival', 'Annual celebration weaving music, dance, and local identity.', 'festival', NULL, 'published', NOW(), NOW());

-- =============================================================================
-- REVIEWS, MESSAGES (demo)
-- =============================================================================
INSERT IGNORE INTO reviews (id, user_id, business_id, attraction_id, rating, comment, status, created_at, updated_at) VALUES
(1, 4, 1, NULL, 5, 'The quality and portion of the food is good! Would definitely order again!', 'approved', NOW(), NOW()),
(2, 5, 2, NULL, 5, 'Beautiful handicrafts — great gifts for friends abroad.', 'approved', NOW(), NOW()),
(3, 5, NULL, 3, 5, 'Calaguas is stunning. Boat ride was smooth with our accredited guide.', 'approved', NOW(), NOW());

INSERT IGNORE INTO messages (id, sender_id, receiver_id, business_id, product_id, message_content, is_read, created_at) VALUES
(1, 4, 2, 1, 7, 'Hi! How much is bilao ng kakanin?', 1, NOW() - INTERVAL 2 HOUR),
(2, 2, 4, 1, 7, 'Hello! Small Php350, Medium Php500, Large Php650.', 1, NOW() - INTERVAL 1 HOUR);

INSERT IGNORE INTO activity_logs (id, user_id, action, description, ip_address, created_at) VALUES
(1, 1, 'seed', 'Database seeded', '127.0.0.1', NOW()),
(2, 2, 'login', 'Seller login', '127.0.0.1', NOW());

-- Ensure approved status on core marketplace businesses
UPDATE businesses SET status = 'approved', approved_by = COALESCE(approved_by, @admin), approved_at = COALESCE(approved_at, NOW()) WHERE id IN (1, 2);
