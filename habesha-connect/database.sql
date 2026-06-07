-- =============================================
-- Habesha Connect - Ethiopian Dating Platform
-- Database Schema
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

CREATE DATABASE IF NOT EXISTS `habesha_connect` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `habesha_connect`;

-- =============================================
-- USERS TABLE
-- =============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `seeking` enum('male','female','both') NOT NULL DEFAULT 'both',
  `birthdate` date NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Ethiopia',
  `ethnicity` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `marital_status` enum('single','divorced','widowed','separated') DEFAULT 'single',
  `education` varchar(100) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `height_cm` int(3) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `skin_tone` varchar(50) DEFAULT NULL,
  `eye_color` varchar(50) DEFAULT NULL,
  `hair_type` varchar(50) DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `ideal_partner` text DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `smoking` enum('never','occasionally','regularly') DEFAULT 'never',
  `drinking` enum('never','occasionally','regularly') DEFAULT 'never',
  `children` enum('none','have_children','want_children','dont_want') DEFAULT 'none',
  `profile_photo` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `premium_expires` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `email_verified` tinyint(1) DEFAULT 0,
  `verify_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_banned` tinyint(1) DEFAULT 0,
  `ban_reason` text DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `profile_views` int(11) DEFAULT 0,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `hide_age` tinyint(1) DEFAULT 0,
  `hide_location` tinyint(1) DEFAULT 0,
  `allow_messages` enum('everyone','matches','premium') DEFAULT 'everyone',
  `notifications_email` tinyint(1) DEFAULT 1,
  `notifications_likes` tinyint(1) DEFAULT 1,
  `notifications_messages` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `gender` (`gender`),
  KEY `city` (`city`),
  KEY `is_active` (`is_active`),
  KEY `last_active` (`last_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PHOTOS TABLE
-- =============================================
CREATE TABLE `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `is_profile` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 1,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_photos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- LIKES TABLE
-- =============================================
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`from_user_id`, `to_user_id`),
  KEY `to_user_id` (`to_user_id`),
  CONSTRAINT `fk_likes_from` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_likes_to` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- MATCHES TABLE (mutual likes)
-- =============================================
CREATE TABLE `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match` (`user1_id`, `user2_id`),
  KEY `user2_id` (`user2_id`),
  CONSTRAINT `fk_match_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- MESSAGES TABLE
-- =============================================
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `from_user_id` (`from_user_id`),
  KEY `to_user_id` (`to_user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `fk_msg_from` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_to` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PROFILE VIEWS TABLE
-- =============================================
CREATE TABLE `profile_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `viewer_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `viewer_id` (`viewer_id`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `fk_view_viewer` FOREIGN KEY (`viewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_view_profile` FOREIGN KEY (`profile_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- BLOCKED USERS TABLE
-- =============================================
CREATE TABLE `blocked_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_block` (`blocker_id`, `blocked_id`),
  CONSTRAINT `fk_block_blocker` FOREIGN KEY (`blocker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_block_blocked` FOREIGN KEY (`blocked_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- REPORTS TABLE
-- =============================================
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) NOT NULL,
  `reported_id` int(11) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reporter_id` (`reporter_id`),
  KEY `reported_id` (`reported_id`),
  CONSTRAINT `fk_report_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_report_reported` FOREIGN KEY (`reported_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SUBSCRIPTIONS TABLE
-- =============================================
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan` enum('weekly','monthly','quarterly','annual') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'ETB',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','expired','cancelled') DEFAULT 'pending',
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SITE SETTINGS TABLE
-- =============================================
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADMIN USERS TABLE
-- =============================================
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','moderator') DEFAULT 'moderator',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DEFAULT SITE SETTINGS
-- =============================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Habesha Connect'),
('site_tagline', 'Find Your Perfect Ethiopian Match'),
('site_email', 'admin@habeshaconnect.com'),
('site_phone', '+251 911 000 000'),
('weekly_price', '99'),
('monthly_price', '299'),
('quarterly_price', '699'),
('annual_price', '1999'),
('currency', 'ETB'),
('max_photos', '6'),
('require_email_verify', '0'),
('maintenance_mode', '0'),
('telebirr_enabled', '1'),
('cbebirr_enabled', '1'),
('bank_transfer_enabled', '1');

-- =============================================
-- DEFAULT ADMIN USER (password: Admin@1234)
-- =============================================
INSERT INTO `admins` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@habeshaconnect.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- =============================================
-- SAMPLE USERS (for demo)
-- =============================================
INSERT INTO `users` (`username`, `email`, `password`, `gender`, `seeking`, `birthdate`, `city`, `region`, `ethnicity`, `religion`, `marital_status`, `education`, `occupation`, `about_me`, `ideal_partner`, `hobbies`, `is_active`, `email_verified`, `last_active`) VALUES
('selam_addis', 'selam@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', 'male', '1995-03-15', 'Addis Ababa', 'Addis Ababa', 'Amhara', 'Orthodox Christian', 'single', 'Bachelor Degree', 'Accountant', 'I am a cheerful and family-oriented woman who loves Ethiopian culture, cooking injera, and spending time with loved ones.', 'Looking for a kind, responsible, and God-fearing man who values family.', 'Cooking, Reading, Music, Hiking', 1, 1, NOW()),
('biruk_haile', 'biruk@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', 'female', '1990-07-22', 'Addis Ababa', 'Addis Ababa', 'Amhara', 'Orthodox Christian', 'single', 'Master Degree', 'Software Engineer', 'Tech professional who loves Ethiopian history, outdoor activities, and good coffee.', 'Seeking a loving, educated, and culturally grounded woman.', 'Football, Coffee, Reading, Travel', 1, 1, NOW()),
('meron_tigray', 'meron@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', 'male', '1993-11-08', 'Mekelle', 'Tigray', 'Tigrayan', 'Orthodox Christian', 'single', 'Bachelor Degree', 'Teacher', 'Passionate educator who loves her culture, traditional music, and community service.', 'Looking for an honest and hardworking man with strong values.', 'Teaching, Music, Dancing, Volunteering', 1, 1, NOW()),
('dawit_oromo', 'dawit@demo.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', 'female', '1988-05-30', 'Dire Dawa', 'Dire Dawa', 'Oromo', 'Muslim', 'single', 'Bachelor Degree', 'Business Owner', 'Entrepreneur passionate about Ethiopian business and development. Love sports and travel.', 'Looking for a strong, independent woman who shares my ambitions.', 'Business, Football, Travel, Music', 1, 1, NOW());
