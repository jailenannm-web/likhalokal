USE likhalokal_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE notifications;
TRUNCATE TABLE activity_logs;
TRUNCATE TABLE inquiries;
TRUNCATE TABLE messages;
TRUNCATE TABLE reviews;
TRUNCATE TABLE products;
TRUNCATE TABLE businesses;
TRUNCATE TABLE cultural_information;
TRUNCATE TABLE announcements;
TRUNCATE TABLE events;
TRUNCATE TABLE tourist_attractions;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- password123 for all demo accounts (bcrypt)
SET @pwd := '$2y$10$lqdnAprF.JIYMI2feqX.O.2GowPh/BZp/LbiYVMUAQl6gY.fgzDDi';

INSERT INTO users (id, full_name, email, password_hash, contact_number, role, status, created_at, updated_at) VALUES
(1, 'Tourism Admin', 'admin@likhalokal.com', @pwd, '09000000001', 'admin', 'active', NOW(), NOW()),
(2, 'Jannah Cruz', 'jannah@likhalokal.com', @pwd, '09123456789', 'seller', 'active', NOW(), NOW()),
(3, 'Rhuwen Santos', 'rhumens@likhalokal.com', @pwd, '09187654321', 'seller', 'active', NOW(), NOW()),
(4, 'Ley Ubana', 'user@likhalokal.com', @pwd, '09222222222', 'local_user', 'active', NOW(), NOW()),
(5, 'Maria Tourist', 'tourist@likhalokal.com', @pwd, '09333333333', 'local_user', 'active', NOW(), NOW());

INSERT INTO businesses (id, user_id, business_name, business_type, description, contact_number, email, address, barangay, latitude, longitude, operating_hours, accepted_payments, promotional_note, status, approved_by, approved_at, created_at, updated_at) VALUES
(1, 2, 'Jannah''s Pasalubong', 'pasalubong', 'Home of authentic Bicol delicacies and pasalubong treats made fresh for Vinzons and visitors.', '09123456789', 'jannah@likhalokal.com', 'Poblacion I', 'Poblacion I', 14.1720000, 122.9450000, 'Mon–Sat 8:00 AM – 6:00 PM', '["GCash","Maya","Cash on pickup","Bank transfer"]', 'Free taste on weekends for orders Php500+', 'approved', 1, NOW(), NOW(), NOW()),
(2, 3, 'Rhuwen''s Pasalubong', 'pasalubong', 'Specialty handicrafts, woven goods, and curated local souvenirs from Vinzons artisans.', '09187654321', 'rhumens@likhalokal.com', 'Talisay', 'Talisay', 14.1800000, 122.9500000, 'Daily 9:00 AM – 5:00 PM', '["GCash","Cash on pickup","Pay upon booking"]', NULL, 'approved', 1, NOW(), NOW(), NOW()),
(3, 2, 'Jannah''s Kitchen', 'food_vendor', 'Small food counter — pending demo application.', '09120001111', 'jannah@likhalokal.com', 'Market area', 'Poblacion II', NULL, NULL, NULL, '["Cash on pickup"]', NULL, 'pending', NULL, NULL, NOW(), NOW());

INSERT INTO products (business_id, product_name, category, description, price, availability, is_featured) VALUES
(1, 'Angko', 'local_delicacy', 'Sticky rice peanut snack native to Vinzons.', 120.00, 'available', 1),
(1, 'Pandecillos', 'local_delicacy', 'Soft local bread rolls.', 80.00, 'available', 1),
(1, 'Pili Brittle', 'local_delicacy', 'Crunchy caramelized pili candy.', 150.00, 'available', 0),
(1, 'Pili Nuts', 'local_delicacy', 'Roasted premium pili.', 200.00, 'available', 0),
(1, 'Pili Tarts', 'local_delicacy', 'Buttery tarts with pili filling.', 220.00, 'available', 0),
(1, 'Sapin-Sapin', 'local_delicacy', 'Layered glutinous rice cake.', 180.00, 'available', 0),
(1, 'Kakanin', 'local_delicacy', 'Assorted rice cakes — bilao available.', 350.00, 'available', 1),
(1, 'Biko', 'local_delicacy', 'Sweet sticky rice with latik.', 140.00, 'available', 0),
(1, 'Maja Blanca', 'local_delicacy', 'Coconut milk pudding with corn.', 130.00, 'available', 0),
(1, 'Coco Jam', 'local_delicacy', 'Slow-cooked coconut jam.', 160.00, 'available', 0),
(1, 'Leche Flan de Coco', 'local_delicacy', 'Coconut leche flan.', 200.00, 'available', 0),
(1, 'Bukayo', 'local_delicacy', 'Sweetened coconut strips.', 90.00, 'available', 0),
(1, 'Hinalo', 'local_delicacy', 'Traditional mixed rice delicacy.', 110.00, 'available', 0),
(2, 'Coconut Shell Crafts', 'handicraft', 'Decorative bowls and utensils from coconut shell.', 250.00, 'available', 1),
(2, 'Nito / Pandan Woven Mats', 'handicraft', 'Handwoven mats from local fibers.', 450.00, 'available', 0),
(2, 'Bamboo & Rattan Baskets', 'handicraft', 'Durable baskets for home and market.', 320.00, 'available', 0),
(2, 'Shell Jewelry & Ornaments', 'handicraft', 'Coastal-inspired accessories.', 180.00, 'available', 0),
(2, 'Handwoven Bags & Pouches', 'handicraft', 'Eco bags by community weavers.', 400.00, 'available', 0),
(2, 'Miniature Bicolano Houses', 'handicraft', 'Mini bahay kubo souvenirs.', 150.00, 'available', 0),
(2, 'Wooden Keychains & Carvings', 'handicraft', 'Carved keychains and tokens.', 75.00, 'available', 0),
(2, 'Woven Table Runners & Placemats', 'handicraft', 'Table accents for homes and cafes.', 380.00, 'available', 0),
(1, 'Pineapple', 'fresh_produce', 'Sweet Vinzons pineapples.', 60.00, 'available', 0),
(1, 'Coconut', 'fresh_produce', 'Fresh mature coconut.', 40.00, 'available', 0),
(1, 'Banana Varieties', 'fresh_produce', 'Saba, lakatan, and local varieties.', 55.00, 'available', 0),
(1, 'Root Crops & Vegetables', 'fresh_produce', 'Seasonal farm harvest bundle.', 120.00, 'available', 0),
(1, 'Fresh Fish & Seafood', 'fresh_produce', 'Catch of the day — preorder.', 350.00, 'available', 0),
(1, 'Tropical Fruits', 'fresh_produce', 'Mixed seasonal fruits.', 200.00, 'available', 0);

INSERT INTO tourist_attractions (admin_id, attraction_name, category, description, history, travel_guide, entrance_fee, best_time_to_visit, address, latitude, longitude, image, status) VALUES
(1, 'Saint Peter the Apostle Church', 'church', 'Historic stone church and spiritual landmark of Vinzons.', 'Built in 1811; among the oldest churches in Camarines Norte.', 'From town proper, walk or tricycle to the church plaza. Respect mass schedules.', 'Free / donation', 'Early morning or late afternoon', 'Poblacion, Vinzons', 14.1725000, 122.9448000, NULL, 'published'),
(1, 'Wenceslao Q. Vinzons Ancestral Home', 'heritage_site', 'National Historical Landmark honoring the hero''s residence.', 'Declared landmark; tells the story of Wenceslao Vinzons and WWII resistance.', 'Located near municipal center. Guided visits may be arranged with LGU.', 'Free', 'Weekday mornings', 'Poblacion I, Vinzons', 14.1730000, 122.9452000, NULL, 'published'),
(1, 'Calaguas Islands', 'island', 'Powdery white sand and turquoise waters — gateway adventure from Vinzons.', 'Part of the larger Calaguas experience; boat trips coordinated by accredited operators.', 'Take authorized boat from designated ports; check weather advisories.', 'Php 150–200 (varies)', 'Dry season (Nov–May)', 'Offshore Vinzons / Daet jump-off', 14.1000000, 122.9000000, NULL, 'published'),
(1, 'Cory Aquino Boulevard', 'landmark', 'Scenic coastal boulevard ideal for sunset walks.', 'Named in honor of President Corazon Aquino; showcases Vinzons coastline.', 'Drive or stroll along the boulevard; parking available in sections.', 'Free', 'Sunset hours', 'Coastal Vinzons', 14.1750000, 122.9480000, NULL, 'published'),
(1, 'Vinzons Town Proper Heritage Area', 'cultural_site', 'Walkable heritage blocks with ancestral structures and plazas.', 'Centuries of trade and faith shaped the town core.', 'Heritage walk maps available at municipal tourism.', 'Free', 'Morning to evening', 'Poblacion', 14.1728000, 122.9455000, NULL, 'published'),
(1, 'Mangrove & Coastal Eco-Tourism', 'eco_tourism', 'Mangrove trails and coastal biodiversity experiences.', 'Community-managed eco-tours support livelihoods and conservation.', 'Book with accredited guides; wear appropriate footwear.', 'Minimal environmental fee', 'High tide windows for boat', 'Coastal barangays', 14.1680000, 122.9520000, NULL, 'published');

INSERT INTO events (admin_id, title, description, event_date, event_time, location, status) VALUES
(1, 'Tacboan Festival', 'Rhythm, colors, and traditions of Vinzons — street dancing, exhibits, and cultural shows.', '2026-05-28', '08:00:00', 'Municipal grounds & plaza', 'published'),
(1, 'Vinzons Day / Wenceslao Vinzons Birth Anniversary', 'Commemorative programs, wreath-laying, and civic parade.', '2026-09-28', '07:30:00', 'Hero monuments & LGU venues', 'published'),
(1, 'Local Cultural Fair', 'Handicraft demos, food stalls, and live folk music.', '2026-08-15', '16:00:00', 'Baybay activity center', 'published');

INSERT INTO announcements (admin_id, title, content, status) VALUES
(1, 'Attention Local Entrepreneurs!', 'Register your business on LikhaLokal to reach tourists and residents with a digital storefront. LGU verification applies.', 'published'),
(1, 'Dry Season Travel Advisory', 'Plan Calaguas and coastal trips with accredited operators and monitor weather updates.', 'published');

INSERT INTO cultural_information (admin_id, title, content, category, status) VALUES
(1, 'From Tacboan to Vinzons', 'The town''s story spans indigenous roots, Spanish-era missions, and the heroism of Wenceslao Q. Vinzons.', 'history', 'published'),
(1, 'Faith and Heritage', 'Churches, plazas, and ancestral homes reflect Vinzons'' enduring faith and community memory.', 'heritage', 'published'),
(1, 'Livelihoods by Land and Sea', 'Farming, fishing, crafts, and tourism sustain families across 19 barangays.', 'livelihood', 'published'),
(1, 'Tacboan Festival', 'Annual celebration weaving music, dance, and local identity.', 'festival', 'published');

INSERT INTO reviews (user_id, business_id, attraction_id, rating, comment, status) VALUES
(4, 1, NULL, 5, 'The quality and portion of the food is good! Would definitely order again!', 'approved'),
(5, 2, NULL, 5, 'Beautiful handicrafts — great gifts for friends abroad.', 'approved'),
(5, NULL, 3, 5, 'Calaguas is stunning. Boat ride was smooth with our accredited guide.', 'approved');

INSERT INTO messages (sender_id, receiver_id, business_id, product_id, message_content, is_read, created_at) VALUES
(4, 2, 1, 7, 'Hi! How much is bilao ng kakanin?', 1, NOW() - INTERVAL 2 HOUR),
(2, 4, 1, 7, 'Hello! Small Php350, Medium Php500, Large Php650.', 1, NOW() - INTERVAL 1 HOUR);

INSERT INTO inquiries (user_id, business_id, product_id, subject, message, status) VALUES
(5, 1, 1, 'Bulk order Angko', 'Do you accept bulk orders for 50 packs next week?', 'open');

INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES
(1, 'seed', 'Database seeded', '127.0.0.1', NOW()),
(2, 'login', 'Seller login', '127.0.0.1', NOW());
