-- Getas Reality Database Setup
CREATE DATABASE IF NOT EXISTS getas_realty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE getas_realty;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','agent','user') DEFAULT 'user',
    phone VARCHAR(20),
    avatar VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    site ENUM('Summit 72','Kazanchis','Other') NOT NULL DEFAULT 'Summit 72',
    type ENUM('1BR','2BR','3BR') NOT NULL,
    bedrooms TINYINT NOT NULL,
    bathrooms TINYINT DEFAULT 1,
    area DECIMAL(8,2) NOT NULL COMMENT 'Square meters',
    floor_number TINYINT,
    total_floors TINYINT,
    price DECIMAL(15,2) NOT NULL,
    price_type ENUM('sale','rent') DEFAULT 'sale',
    currency ENUM('ETB','USD') DEFAULT 'ETB',
    status ENUM('available','sold','reserved','coming_soon') DEFAULT 'available',
    featured TINYINT(1) DEFAULT 0,
    image_main VARCHAR(255),
    images TEXT COMMENT 'JSON array of image paths',
    amenities TEXT COMMENT 'JSON array',
    parking TINYINT DEFAULT 0,
    balcony TINYINT(1) DEFAULT 0,
    furnished TINYINT(1) DEFAULT 0,
    year_built YEAR,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    property_id INT,
    status ENUM('new','read','replied','closed') DEFAULT 'new',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    message TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    image VARCHAR(255),
    author_id INT,
    published TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- SEED DATA
-- =====================================================

-- Admin user (password: admin123)
INSERT IGNORE INTO users (name, email, password, role, phone) VALUES
('Getas Admin', 'admin@getasreality.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+251911234567'),
('Sales Agent', 'agent@getasreality.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', '+251922345678');

-- Summit 72 Properties
INSERT IGNORE INTO properties (title, slug, description, site, type, bedrooms, bathrooms, area, floor_number, price, status, featured, amenities, balcony, parking) VALUES
-- Summit 72 - 1 Bedroom
('1 Bedroom Apartment – 61m² | Summit 72', 'summit-72-1br-61sqm', 'Modern 1-bedroom apartment at Summit 72, Addis Ababa's premier residential complex. Features open-plan living, fully-fitted kitchen, master bedroom with en-suite, and a private balcony with panoramic city views. High-quality finishes throughout.', 'Summit 72', '1BR', 1, 1, 61.00, 5, 2850000, 'available', 1, '["Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Intercom","Fiber Internet"]', 1, 1),
('1 Bedroom Apartment – 65m² | Summit 72', 'summit-72-1br-65sqm', 'Spacious 1-bedroom apartment at Summit 72 with extra living area. This premium unit features a large living room, modern kitchen, generous master bedroom with en-suite bathroom, and a wrap-around balcony offering stunning city views.', 'Summit 72', '1BR', 1, 1, 65.00, 8, 3100000, 'available', 0, '["Elevator","24/7 Security","CCTV","Generator","Parking","Swimming Pool","Gym","Concierge","Intercom","Fiber Internet"]', 1, 1),

-- Summit 72 - 2 Bedroom
('2 Bedroom Apartment – 109m² | Summit 72', 'summit-72-2br-109sqm', 'Beautiful 2-bedroom apartment at Summit 72. Perfect for families or professionals seeking spacious urban living. Features two well-proportioned bedrooms, modern bathrooms, open-plan kitchen/dining/living area, and a generous balcony.', 'Summit 72', '2BR', 2, 2, 109.00, 10, 4950000, 'available', 1, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Swimming Pool","Gym","Concierge","Intercom","Fiber Internet","Storage Room"]', 1, 2),
('2 Bedroom Apartment – 115m² | Summit 72', 'summit-72-2br-115sqm', 'Premium 2-bedroom apartment at Summit 72 with generous floor plan. The 115m² layout provides exceptional living space with two master bedrooms each with en-suite, a large open living area, gourmet kitchen, and expansive balcony.', 'Summit 72', '2BR', 2, 2, 115.00, 12, 5200000, 'available', 1, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Swimming Pool","Gym","Concierge","Intercom","Fiber Internet","Storage Room"]', 1, 2),

-- Summit 72 - 3 Bedroom
('3 Bedroom Apartment – 144m² | Summit 72', 'summit-72-3br-144sqm', 'Impressive 3-bedroom apartment at Summit 72 ideal for families. Offers three spacious bedrooms with en-suite bathrooms, a grand open-plan living and dining area, chef's kitchen, utility room, and a large balcony.', 'Summit 72', '3BR', 3, 3, 144.00, 15, 6800000, 'available', 1, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Swimming Pool","Gym","Concierge","Intercom","Fiber Internet","Storage Room","Maids Room"]', 1, 2),
('3 Bedroom Apartment – 151m² | Summit 72', 'summit-72-3br-151sqm', 'Flagship 3-bedroom apartment at Summit 72 — the largest in the series. Features three luxurious en-suite bedrooms, expansive living spaces, premium kitchen, utility room, and a spectacular corner balcony. The ultimate in urban luxury.', 'Summit 72', '3BR', 3, 3, 151.00, 18, 7200000, 'available', 0, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Swimming Pool","Gym","Concierge","Intercom","Fiber Internet","Storage Room","Maids Room","Corner Unit"]', 1, 2);

-- Kazanchis Properties
INSERT IGNORE INTO properties (title, slug, description, site, type, bedrooms, bathrooms, area, floor_number, price, status, featured, amenities, balcony, parking) VALUES
-- Kazanchis - 1 Bedroom
('1 Bedroom Apartment – 61m² | Kazanchis', 'kazanchis-1br-61sqm', 'Contemporary 1-bedroom apartment in the vibrant Kazanchis business district. Steps from major offices, restaurants, and entertainment. Features modern open-plan living, fully-fitted kitchen, and private balcony.', 'Kazanchis', '1BR', 1, 1, 61.00, 4, 2700000, 'available', 0, '["Elevator","24/7 Security","CCTV","Generator","Parking","Rooftop Terrace","Concierge","Intercom","Fiber Internet"]', 1, 1),
('1 Bedroom Apartment – 65m² | Kazanchis', 'kazanchis-1br-65sqm', 'Well-appointed 1-bedroom apartment in Kazanchis with a superior central location. Larger floor plan with extended living area, smart kitchen, generous bedroom with en-suite, and balcony overlooking the vibrant cityscape.', 'Kazanchis', '1BR', 1, 1, 65.00, 7, 2950000, 'available', 0, '["Elevator","24/7 Security","CCTV","Generator","Parking","Rooftop Terrace","Concierge","Intercom","Fiber Internet"]', 1, 1),

-- Kazanchis - 2 Bedroom
('2 Bedroom Apartment – 109m² | Kazanchis', 'kazanchis-2br-109sqm', 'Stylish 2-bedroom apartment in the heart of Kazanchis. Features two spacious bedrooms, modern bathrooms, bright living and dining area, fitted kitchen, and balcony. Premium finishes and access to full building amenities.', 'Kazanchis', '2BR', 2, 2, 109.00, 9, 4750000, 'available', 1, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Rooftop Terrace","Concierge","Intercom","Fiber Internet","Storage Room"]', 1, 2),
('2 Bedroom Apartment – 115m² | Kazanchis', 'kazanchis-2br-115sqm', 'Executive 2-bedroom apartment in Kazanchis with premium finishes. The generous 115m² layout includes two en-suite bedrooms, spacious reception rooms, a well-equipped kitchen, and a sizeable private balcony.', 'Kazanchis', '2BR', 2, 2, 115.00, 11, 4950000, 'available', 0, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Rooftop Terrace","Concierge","Intercom","Fiber Internet","Storage Room"]', 1, 2),

-- Kazanchis - 3 Bedroom
('3 Bedroom Apartment – 144m² | Kazanchis', 'kazanchis-3br-144sqm', 'Luxurious 3-bedroom apartment in Kazanchis offering sophisticated city living. Three full en-suite bedrooms, large open living spaces, premium kitchen, and a sweeping balcony with panoramic views of Addis Ababa.', 'Kazanchis', '3BR', 3, 3, 144.00, 13, 6500000, 'available', 1, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Rooftop Terrace","Concierge","Intercom","Fiber Internet","Storage Room","Maids Room"]', 1, 2),
('3 Bedroom Apartment – 151m² | Kazanchis', 'kazanchis-3br-151sqm', 'Ultimate 3-bedroom apartment in Kazanchis — a pinnacle of luxury living. Three en-suite bedrooms, grand reception rooms, bespoke kitchen, and a wrap-around balcony capturing the full Addis Ababa skyline.', 'Kazanchis', '3BR', 3, 3, 151.00, 16, 6900000, 'available', 0, '["Elevator","24/7 Security","CCTV","Generator","2 Parking Spaces","Rooftop Terrace","Concierge","Intercom","Fiber Internet","Storage Room","Maids Room","Corner Unit"]', 1, 2);

-- Testimonials
INSERT IGNORE INTO testimonials (name, role, message, rating, approved) VALUES
('Abebe Girma', 'Purchased 2BR at Summit 72', 'Getas Reality made buying our apartment seamless. The team is professional, honest and very knowledgeable about the properties. We love our new home at Summit 72!', 5, 1),
('Tigist Haile', 'Investor – Kazanchis', 'I have invested in multiple properties through Getas Reality. Their market insight and transparency are unmatched. Strong returns and excellent service every time.', 5, 1),
('Solomon Bekele', 'Purchased 3BR at Summit 72', 'The 3-bedroom apartment at Summit 72 exceeded our expectations. Getas Reality guided us through every step with patience and expertise. Highly recommended!', 5, 1),
('Mekdes Alemu', 'Purchased 1BR at Kazanchis', 'Perfect apartment for a young professional. The location in Kazanchis is fantastic — close to everything. Getas Reality found exactly what I needed within my budget.', 5, 1);

-- Settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'Getas Reality'),
('tagline', 'Premium Apartments in Addis Ababa'),
('sub_tagline', 'Summit 72 & Kazanchis — Where Luxury Meets Location'),
('phone_1', '+251 911 234 567'),
('phone_2', '+251 922 345 678'),
('email', 'info@getasreality.com'),
('address', 'Bole Road, Addis Ababa, Ethiopia'),
('working_hours', 'Mon–Sat: 8:00 AM – 6:00 PM'),
('facebook', 'https://facebook.com/getasreality'),
('instagram', 'https://instagram.com/getasreality'),
('telegram', 'https://t.me/getasreality'),
('youtube', ''),
('about_short', 'Getas Reality is your trusted partner for premium apartment sales in Addis Ababa. With two flagship developments — Summit 72 and Kazanchis — we offer world-class residences with Ethiopian warmth and service.'),
('meta_description', 'Premium apartments for sale in Addis Ababa, Ethiopia. 1, 2 and 3 bedroom apartments at Summit 72 and Kazanchis. Contact Getas Reality today.');
