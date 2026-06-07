-- Getas Real Estate — City Gate Database
-- Complete rebuild with real project data

CREATE DATABASE IF NOT EXISTS getas_realty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE getas_realty;

DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS payment_plans;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','agent','user') DEFAULT 'user',
    phone VARCHAR(20),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(250) NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    description TEXT,
    tower ENUM('City Gate 1','City Gate 2','City Gate 3') NOT NULL,
    unit_type VARCHAR(10) NOT NULL COMMENT 'Type 1..6',
    bedrooms TINYINT NOT NULL,
    bathrooms TINYINT DEFAULT 2,
    area DECIMAL(8,2) NOT NULL,
    price_per_m2 DECIMAL(12,2) DEFAULT 0,
    total_price DECIMAL(15,2) NOT NULL,
    down_payment_10pct DECIMAL(15,2) DEFAULT 0 COMMENT '10% Down – pre-completion',
    status ENUM('available','sold','reserved') DEFAULT 'available',
    featured TINYINT(1) DEFAULT 0,
    balcony TINYINT(1) DEFAULT 1,
    maids_room TINYINT(1) DEFAULT 1,
    laundry TINYINT(1) DEFAULT 1,
    parking TINYINT(1) DEFAULT 1,
    floor_plan_img VARCHAR(255) COMMENT 'Floor plan image path',
    images TEXT COMMENT 'JSON array',
    amenities TEXT COMMENT 'JSON array',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Payment plans (completion-based pricing)
CREATE TABLE payment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    completion_pct INT NOT NULL COMMENT 'e.g. 85 or 100',
    down_payment_pct INT NOT NULL COMMENT 'e.g. 10, 25, 50, 65',
    bedrooms TINYINT NOT NULL,
    area DECIMAL(8,2) NOT NULL,
    price_per_m2 DECIMAL(12,2),
    total_price DECIMAL(15,2) NOT NULL,
    down_payment_amount DECIMAL(15,2) NOT NULL,
    sort_order INT DEFAULT 0
);

-- Inquiries
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    property_id INT,
    tower VARCHAR(50),
    status ENUM('new','read','replied','closed') DEFAULT 'new',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

-- Testimonials
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(150),
    message TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- SEED: ADMIN USERS
-- ============================================================
-- password: password
INSERT INTO users (name, email, password, role, phone) VALUES
('Getas Admin', 'admin@getasrealestate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+251911234567'),
('Sales Agent',  'agent@getasrealestate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', '+251922345678');

-- ============================================================
-- SEED: CITY GATE 1 APARTMENTS (Wing 1)
-- ============================================================
INSERT INTO properties (title, slug, description, tower, unit_type, bedrooms, bathrooms, area, price_per_m2, total_price, down_payment_10pct, status, featured, amenities) VALUES

('Type 1 – 2 Bedroom 115m² | City Gate 1',
 'cg1-type1-2br-115',
 'Elegant 2-bedroom apartment in City Gate 1. Features an open-plan living and dining area, two spacious bedrooms with en-suite bathrooms, a modern fully-fitted kitchen, two private balconies, maid\'s room, and laundry space. Premium finishes throughout.',
 'City Gate 1','Type 1',2,2,115.00,99378,11428470,1142847,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Fiber Internet"]'),

('Type 2 – 2 Bedroom 116m² | City Gate 1',
 'cg1-type2-2br-116',
 'Spacious 2-bedroom apartment in City Gate 1 with a superior floor plan. Two en-suite bedrooms, open reception rooms, gourmet kitchen, two balconies with panoramic views, dedicated maid\'s room and laundry. International-grade finishes.',
 'City Gate 1','Type 2',2,2,116.00,107476,12467216,1246722,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Fiber Internet"]'),

('Type 3 – 2 Bedroom 116m² | City Gate 1',
 'cg1-type3-2br-116',
 'Premium 2-bedroom apartment in City Gate 1. Generous open-plan layout with two full en-suite bedrooms, contemporary kitchen, two private balconies overlooking the city, maid\'s room, and dedicated laundry space.',
 'City Gate 1','Type 3',2,2,116.00,119000,13804000,1380400,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Fiber Internet"]'),

('Type 4 – 3 Bedroom 137m² | City Gate 1',
 'cg1-type4-3br-137',
 'Impressive 3-bedroom apartment in City Gate 1 — ideal for families. Three en-suite bedrooms, grand open living and dining area, chef\'s kitchen, two wraparound balconies, maid\'s room and laundry. The finest quality throughout.',
 'City Gate 1','Type 4',3,3,137.00,119000,16303000,1630300,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Fiber Internet","Storage"]'),

('Type 5 – 3 Bedroom 144m² | City Gate 1',
 'cg1-type5-3br-144',
 'Flagship 3-bedroom apartment in City Gate 1. Three luxurious en-suite bedrooms, a sweeping open-plan reception, bespoke kitchen, two balconies with skyline views, dedicated maid\'s room and laundry — the ultimate in City Gate living.',
 'City Gate 1','Type 5',3,3,144.00,99378,14310432,1431043,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Fiber Internet","Storage"]'),

('Type 6 – 2 Bedroom 117m² | City Gate 1',
 'cg1-type6-2br-117',
 'Refined 2-bedroom corner apartment in City Gate 1. Generous floor plan with two en-suite bedrooms, bright living spaces, modern kitchen, two balconies capturing panoramic city views, maid\'s room and laundry.',
 'City Gate 1','Type 6',2,2,117.00,99378,11627226,1162723,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Fiber Internet"]');

-- ============================================================
-- SEED: CITY GATE 2 APARTMENTS (Wing 2)
-- ============================================================
INSERT INTO properties (title, slug, description, tower, unit_type, bedrooms, bathrooms, area, price_per_m2, total_price, down_payment_10pct, status, featured, amenities) VALUES

('Type 1 – 3 Bedroom 144m² | City Gate 2',
 'cg2-type1-3br-144',
 'Magnificent 3-bedroom apartment in City Gate 2. Three en-suite bedrooms, vast open-plan living and dining, fully fitted kitchen, two generous balconies with city panorama, maid\'s room, and laundry space.',
 'City Gate 2','Type 1',3,3,144.00,99378,14310432,1431043,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet","Storage"]'),

('Type 2 – 3 Bedroom 137m² | City Gate 2',
 'cg2-type2-3br-137',
 'Luxury 3-bedroom apartment in City Gate 2. Three en-suite bedrooms, large reception rooms, premium kitchen, two balconies, maid\'s room and laundry — exceptional quality in a prime location.',
 'City Gate 2','Type 2',3,3,137.00,119000,16303000,1630300,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet","Storage"]'),

('Type 3 – 2 Bedroom 116m² | City Gate 2',
 'cg2-type3-2br-116',
 'Stylish 2-bedroom apartment in City Gate 2. Two en-suite bedrooms, bright open-plan living and dining, modern kitchen, two balconies, maid\'s room, and laundry space. Premium finishes throughout.',
 'City Gate 2','Type 3',2,2,116.00,119000,13804000,1380400,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet"]'),

('Type 4 – 2 Bedroom 116m² | City Gate 2',
 'cg2-type4-2br-116',
 'Elegant 2-bedroom apartment in City Gate 2. Mirror-plan to Type 3, featuring two en-suite bedrooms, open reception, fitted kitchen, two balconies, maid\'s room, and laundry. High-specification finish.',
 'City Gate 2','Type 4',2,2,116.00,119000,13804000,1380400,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet"]'),

('Type 5 – 2 Bedroom 115m² | City Gate 2',
 'cg2-type5-2br-115',
 'Premium 2-bedroom apartment in City Gate 2. Two spacious en-suite bedrooms, generous open-plan living, contemporary kitchen, two balconies with skyline views, maid\'s room, and laundry space.',
 'City Gate 2','Type 5',2,2,115.00,119000,13685000,1368500,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet"]'),

('Type 6 – 2 Bedroom 117m² | City Gate 2',
 'cg2-type6-2br-117',
 'Contemporary 2-bedroom apartment in City Gate 2. Two en-suite bedrooms, bright living areas, fully fitted kitchen, two private balconies, maid\'s room, and laundry. Superior position within the tower.',
 'City Gate 2','Type 6',2,2,117.00,107476,12574692,1257469,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet"]');

-- ============================================================
-- SEED: CITY GATE 3 APARTMENTS (Wing 3)
-- ============================================================
INSERT INTO properties (title, slug, description, tower, unit_type, bedrooms, bathrooms, area, price_per_m2, total_price, down_payment_10pct, status, featured, amenities) VALUES

('Type 1 – 2 Bedroom 149m² | City Gate 3',
 'cg3-type1-2br-149',
 'Grand 2-bedroom apartment in City Gate 3 — the most spacious 2BR in the development. Enormous open-plan living and dining area, two en-suite master bedrooms, bespoke kitchen, two balconies, maid\'s room, and laundry space.',
 'City Gate 3','Type 1',2,2,149.00,107476,16013924,1601392,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet","Storage"]'),

('Type 2 – 3 Bedroom 159m² | City Gate 3',
 'cg3-type2-3br-159',
 'Pinnacle 3-bedroom apartment in City Gate 3 at 159m² — the largest available. Three luxurious en-suite bedrooms, a sweeping reception area, gourmet kitchen, two wraparound balconies, maid\'s room, and laundry. Unrivalled in City Gate.',
 'City Gate 3','Type 2',3,3,159.00,99378,15801102,1580110,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet","Storage","Corner Unit"]'),

('Type 3 – 2 Bedroom 132m² | City Gate 3',
 'cg3-type3-2br-132',
 'Sophisticated 2-bedroom apartment in City Gate 3. Two en-suite bedrooms, spacious open-plan living and dining, fully fitted kitchen, two balconies, maid\'s room, and laundry space. Ideal for professionals seeking premium space.',
 'City Gate 3','Type 3',2,2,132.00,99378,13117896,1311790,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet"]'),

('Type 4 – 2 Bedroom 132m² | City Gate 3',
 'cg3-type4-2br-132',
 'Premium 2-bedroom apartment in City Gate 3. Mirror of Type 3, offering two en-suite bedrooms, bright open reception, modern kitchen, two balconies overlooking the city, maid\'s room, and laundry.',
 'City Gate 3','Type 4',2,2,132.00,99378,13117896,1311790,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet"]'),

('Type 5 – 3 Bedroom 159m² | City Gate 3',
 'cg3-type5-3br-159',
 'Landmark 3-bedroom corner apartment in City Gate 3. At 159m², this unit delivers three en-suite bedrooms, a grand reception, premium kitchen, and two wrap-around balconies capturing 180° city views. The finest address in Addis Ababa.',
 'City Gate 3','Type 5',3,3,159.00,107476,17088684,1708868,'available',1,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet","Storage","Corner Unit"]'),

('Type 6 – 2 Bedroom 149m² | City Gate 3',
 'cg3-type6-2br-149',
 'Distinguished 2-bedroom apartment in City Gate 3. Mirror-plan to Type 1 with the same generous 149m² layout: two en-suite bedrooms, expansive living spaces, bespoke kitchen, two balconies, maid\'s room, and laundry.',
 'City Gate 3','Type 6',2,2,149.00,107476,16013924,1601392,'available',0,
 '["Two Balconies","Maid\'s Room","Laundry Space","Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Rooftop Terrace","Concierge","Fiber Internet","Storage"]');

-- ============================================================
-- PAYMENT PLANS (completion-based pricing)
-- ============================================================
INSERT INTO payment_plans (plan_name, completion_pct, down_payment_pct, bedrooms, area, price_per_m2, total_price, down_payment_amount, sort_order) VALUES
-- 100% Completed – 50% Down Payment
('100% Completed – 50% Down',100,50,1,61.00,101379,6184119,3092060,1),
('100% Completed – 50% Down',100,50,1,65.00,101379,6589635,3294818,2),
('100% Completed – 50% Down',100,50,2,116.00,88672,10285952,5142976,3),
('100% Completed – 50% Down',100,50,3,150.00,86918,13037700,6518850,4),

-- 100% Completed – 65% Down Payment
('100% Completed – 65% Down',100,65,2,109.00,88672,9665248,6282411,5),
('100% Completed – 65% Down',100,65,2,115.00,88672,10197280,6628232,6),
('100% Completed – 65% Down',100,65,3,144.00,86918,12516192,8135525,7),
('100% Completed – 65% Down',100,65,3,147.00,86918,12776946,8305015,8),

-- 85% Completed – 25% Down Payment
('85% Completed – 25% Down',85,25,1,61.00,101379,6184119,1546030,9),
('85% Completed – 25% Down',85,25,1,65.00,101379,6589635,1647409,10),
('85% Completed – 25% Down',85,25,2,109.00,88672,9665248,2416312,11),
('85% Completed – 25% Down',85,25,2,115.00,88672,10197280,2549320,12),
('85% Completed – 25% Down',85,25,2,116.00,88672,10285952,2571488,13),
('85% Completed – 25% Down',85,25,3,144.00,86918,12516192,3129048,14),
('85% Completed – 25% Down',85,25,3,147.00,86918,12776946,3194237,15),
('85% Completed – 25% Down',85,25,3,150.00,86918,13037700,3259425,16);

-- ============================================================
-- TESTIMONIALS
-- ============================================================
INSERT INTO testimonials (name, role, message, rating, approved) VALUES
('Abebe Girma',    'Purchased 3BR Type 4 – City Gate 1',  'Getas Real Estate handled everything from start to finish. The quality of our apartment at City Gate exceeded all expectations. Highly recommend!', 5, 1),
('Tigist Haile',   'Investor – City Gate 2',              'I have invested in two units at City Gate 2. Transparent pricing, professional service and a stunning building. Excellent investment.', 5, 1),
('Solomon Bekele', 'Purchased 2BR – City Gate 3',         'The 149m² apartment in City Gate 3 is exceptional. The finishes are world-class and the views are breathtaking. Getas made the buying process easy.', 5, 1),
('Mekdes Alemu',   'Purchased 2BR Type 1 – City Gate 1',  'From first enquiry to key handover, the team at Getas was professional and supportive. Our new apartment is everything we dreamed of.', 5, 1);

-- ============================================================
-- SETTINGS
-- ============================================================
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name',        'Getas Real Estate'),
('project_name',     'City Gate'),
('tagline',          'City Gate — Premium Apartments in Addis Ababa'),
('sub_tagline',      'Three Iconic Towers. 18 Apartment Types. One Address.'),
('phone_1',          '+251 911 234 567'),
('phone_2',          '+251 922 345 678'),
('email',            'info@getasrealestate.com'),
('address',          'Addis Ababa, Ethiopia'),
('working_hours',    'Mon–Sat: 8:00 AM – 6:00 PM'),
('facebook',         'https://facebook.com/getasrealestate'),
('instagram',        'https://instagram.com/getasrealestate'),
('telegram',         'https://t.me/getasrealestate'),
('youtube',          ''),
('about_short',      'Getas Real Estate is the developer behind City Gate — Addis Ababa\'s most ambitious residential landmark. Three towers, 25 floors, premium 2 and 3 bedroom apartments with world-class finishes, rooftop amenities, and clear title deeds.'),
('meta_description', 'Premium 2 and 3 bedroom apartments at City Gate by Getas Real Estate. Three towers in the heart of Addis Ababa. 10% down payment. Book your apartment today.');
