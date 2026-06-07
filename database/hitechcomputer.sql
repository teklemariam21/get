-- Hitechcomputer Database Schema
-- Run this SQL to create the database

CREATE DATABASE IF NOT EXISTS hitechcomputer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hitechcomputer;

-- Admin users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO users (username, password, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hitechcomputer.com', 'Hitech Admin');

-- Posts (tutorials, tips & tricks)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    category ENUM('tutorial','tip','trick','news') NOT NULL DEFAULT 'tutorial',
    tags VARCHAR(255),
    image VARCHAR(255) DEFAULT NULL,
    views INT DEFAULT 0,
    status ENUM('published','draft') DEFAULT 'published',
    featured TINYINT(1) DEFAULT 0,
    user_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    content LONGTEXT,
    duration VARCHAR(100),
    price DECIMAL(10,2) DEFAULT 0,
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    image VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(100) DEFAULT 'fas fa-graduation-cap',
    color VARCHAR(50) DEFAULT '#00d4ff',
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(30),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enrollments / course inquiries
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(30) NOT NULL,
    message TEXT,
    status ENUM('pending','contacted','enrolled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Site settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Hitechcomputer'),
('tagline', 'Learn. Repair. Innovate.'),
('phone', '0968752100'),
('address', 'Zefmesh Grand Mall, Mobile Zone, Megenagna, Addis Ababa'),
('email', 'info@hitechcomputer.com'),
('facebook', '#'),
('telegram', '#'),
('youtube', '#'),
('about_text', 'Hitechcomputer is your premier technology training center and repair shop in Addis Ababa. We offer professional courses in mobile phone repair, computer repair, CCTV installation, and office machine repair. Our certified technicians also provide fast and reliable repair services for laptops and mobile phones.'),
('hero_title', 'Master Tech Skills'),
('hero_subtitle', 'Professional courses & repair services in the heart of Addis Ababa');

-- Sample courses
INSERT INTO courses (title, slug, description, duration, price, level, icon, color, sort_order) VALUES
('Mobile Phone Repairing', 'mobile-phone-repairing', 'Learn complete mobile phone diagnosis, motherboard-level repair, screen replacement, water damage recovery, and software troubleshooting. Get hands-on training with real devices.', '3 Months', 3500.00, 'beginner', 'fas fa-mobile-alt', '#00d4ff', 1),
('Computer Repairing', 'computer-repairing', 'Master desktop and laptop hardware repair, OS installation, networking, data recovery, and component-level troubleshooting. Includes both hardware and software repair skills.', '3 Months', 3500.00, 'beginner', 'fas fa-laptop', '#7b2fff', 2),
('Office Machine Repairing', 'office-machine-repairing', 'Comprehensive training on printer repair, photocopier maintenance, fax machines, and other office equipment. Learn calibration, parts replacement and preventive maintenance.', '2 Months', 2500.00, 'beginner', 'fas fa-print', '#00ff88', 3),
('CCTV Camera Training', 'cctv-camera-training', 'Learn CCTV system design, camera installation, DVR/NVR configuration, network camera setup, and remote monitoring. Includes practical installation projects.', '2 Months', 3000.00, 'beginner', 'fas fa-video', '#ff6b35', 4);

-- Sample posts
INSERT INTO posts (title, slug, excerpt, content, category, tags, featured) VALUES
('How to Replace a Broken iPhone Screen', 'how-to-replace-iphone-screen', 'Step-by-step guide to replacing a cracked iPhone screen at home with the right tools.', '<h2>Tools You Need</h2><p>Before starting, gather: suction cup, pentalobe screwdriver, spudger, replacement screen.</p><h2>Step 1: Power Off</h2><p>Always power off the device before any repair work.</p><h2>Step 2: Remove Screws</h2><p>Remove the two pentalobe screws on either side of the Lightning connector.</p><h2>Step 3: Lift the Screen</h2><p>Use the suction cup and carefully lift the screen at a 90-degree angle.</p><h2>Step 4: Disconnect Cables</h2><p>Disconnect the three screen cables carefully using a spudger.</p><h2>Step 5: Install New Screen</h2><p>Reverse the steps with your new screen. Test before closing.</p>', 'tutorial', 'iPhone,screen repair,mobile phone', 1),
('Top 5 Tools Every Phone Technician Must Have', 'top-5-tools-phone-technician', 'Essential tools that every professional mobile phone repair technician needs in their toolkit.', '<h2>1. Hot Air Rework Station</h2><p>Essential for soldering and desoldering SMD components on motherboards.</p><h2>2. DC Power Supply</h2><p>Allows you to test phones without a battery and identify short circuits.</p><h2>3. Ultrasonic Cleaner</h2><p>Cleans water-damaged boards effectively.</p><h2>4. Digital Multimeter</h2><p>For measuring voltage, resistance and diagnosing circuit problems.</p><h2>5. Microscope</h2><p>For inspecting tiny components and performing micro-soldering work.</p>', 'tip', 'tools,technician,equipment', 1),
('How to Install CCTV Camera System: Complete Guide', 'how-to-install-cctv-system', 'A comprehensive guide on planning and installing a complete CCTV security system for home or office.', '<h2>Planning Your CCTV System</h2><p>First, identify the areas you want to monitor and count the number of cameras needed.</p><h2>Choosing the Right Cameras</h2><p>For outdoor use, choose IP66-rated cameras. For indoor, standard cameras work fine.</p><h2>Running the Cables</h2><p>Use RG59 coaxial cable for analog cameras or CAT6 for IP cameras.</p><h2>DVR Configuration</h2><p>Connect all cameras to the DVR, configure recording schedules and motion detection.</p><h2>Remote Access Setup</h2><p>Configure port forwarding on your router and install the manufacturer app for remote viewing.</p>', 'tutorial', 'CCTV,security,installation', 1);
