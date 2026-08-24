-- ============================================
-- FlyOn Complete Database Schema
-- All-in-one SQL file for complete database setup
-- Includes: Tables, Data, Fixes, and Sample Data
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `flyon_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `flyon_db`;

-- ============================================
-- TABLE STRUCTURES
-- ============================================

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','blocked','pending') DEFAULT 'active',
  `profile_photo` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Airlines Table
CREATE TABLE IF NOT EXISTS `airlines` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Airports Table
CREATE TABLE IF NOT EXISTS `airports` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Flights Table
CREATE TABLE IF NOT EXISTS `flights` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flight_number` varchar(20) NOT NULL,
  `airline_id` int(11) UNSIGNED NOT NULL,
  `departure_airport_id` int(11) UNSIGNED NOT NULL,
  `arrival_airport_id` int(11) UNSIGNED NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `base_price_economy` decimal(10,2) NOT NULL,
  `base_price_business` decimal(10,2) DEFAULT NULL,
  `total_seats_economy` int(11) NOT NULL DEFAULT 0,
  `total_seats_business` int(11) NOT NULL DEFAULT 0,
  `available_seats_economy` int(11) NOT NULL DEFAULT 0,
  `available_seats_business` int(11) NOT NULL DEFAULT 0,
  `status` enum('scheduled','delayed','cancelled','completed') DEFAULT 'scheduled',
  `search_count` int(11) DEFAULT 0,
  `average_rating` decimal(2,1) DEFAULT NULL,
  `total_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `airline_id` (`airline_id`),
  FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`departure_airport_id`) REFERENCES `airports` (`id`),
  FOREIGN KEY (`arrival_airport_id`) REFERENCES `airports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `flight_id` int(11) UNSIGNED NOT NULL,
  `travel_class` enum('economy','business','first') NOT NULL,
  `total_passengers` int(11) NOT NULL DEFAULT 1,
  `base_price` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `booking_status` enum('confirmed','cancelled','completed','pending') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Passengers Table
CREATE TABLE IF NOT EXISTS `passengers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) UNSIGNED NOT NULL,
  `title` enum('Mr','Mrs','Ms','Dr') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seats Table
CREATE TABLE IF NOT EXISTS `seats` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flight_id` int(11) UNSIGNED NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `class` enum('economy','business','first') NOT NULL,
  `status` enum('available','booked','locked') DEFAULT 'available',
  `locked_until` datetime DEFAULT NULL,
  `locked_by_session` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flight_seat` (`flight_id`, `seat_number`, `class`),
  FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Promotions Table
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Loyalty Table
CREATE TABLE IF NOT EXISTS `loyalty` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `available_points` int(11) DEFAULT 0,
  `tier` enum('bronze','silver','gold','platinum') DEFAULT 'bronze',
  `referral_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `referral_code` (`referral_code`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pricing Rules Table
CREATE TABLE IF NOT EXISTS `pricing_rules` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `rule_type` enum('time_based','seat_based','demand_based') NOT NULL,
  `condition_value` varchar(100) NOT NULL,
  `adjustment_type` enum('percentage','fixed') NOT NULL,
  `adjustment_value` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `flight_id` int(11) UNSIGNED NOT NULL,
  `booking_id` int(11) UNSIGNED NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking_review` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `flight_id` (`flight_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- GoZayaan Integration Tables
CREATE TABLE IF NOT EXISTS `flight_sync_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flight_id` int(11) UNSIGNED NOT NULL,
  `changes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_flight_id` (`flight_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert Admin User (password: admin123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `phone`, `role`, `email_verified`) VALUES
('Admin', 'User', 'admin@flyon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000000', 'admin', 1)
ON DUPLICATE KEY UPDATE `email` = `email`;

-- Insert Airlines
INSERT INTO `airlines` (`name`, `code`, `country`, `status`) VALUES
('Biman Bangladesh Airlines', 'BG', 'Bangladesh', 'active'),
('US-Bangla Airlines', 'BS', 'Bangladesh', 'active'),
('Novoair', 'VQ', 'Bangladesh', 'active'),
('Emirates', 'EK', 'UAE', 'active'),
('Singapore Airlines', 'SQ', 'Singapore', 'active'),
('Turkish Airlines', 'TK', 'Turkey', 'active'),
('Qatar Airways', 'QR', 'Qatar', 'active'),
('Air India', 'AI', 'India', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `status` = 'active';

-- Insert Airports
INSERT INTO `airports` (`name`, `code`, `city`, `country`, `status`) VALUES
-- Bangladesh Airports
('Hazrat Shahjalal International Airport', 'DAC', 'Dhaka', 'Bangladesh', 'active'),
('Shah Amanat International Airport', 'CGP', 'Chittagong', 'Bangladesh', 'active'),
('Osmani International Airport', 'ZYL', 'Sylhet', 'Bangladesh', 'active'),
('Cox\'s Bazar Airport', 'CXB', 'Cox\'s Bazar', 'Bangladesh', 'active'),
('Jessore Airport', 'JSR', 'Jessore', 'Bangladesh', 'active'),
('Saidpur Airport', 'SPD', 'Saidpur', 'Bangladesh', 'active'),
('Barisal Airport', 'BZL', 'Barisal', 'Bangladesh', 'active'),
-- International Airports
('Dubai International Airport', 'DXB', 'Dubai', 'UAE', 'active'),
('Singapore Changi Airport', 'SIN', 'Singapore', 'Singapore', 'active'),
('Indira Gandhi International Airport', 'DEL', 'New Delhi', 'India', 'active'),
('Netaji Subhas Chandra Bose International Airport', 'CCU', 'Kolkata', 'India', 'active'),
('Chhatrapati Shivaji International Airport', 'BOM', 'Mumbai', 'India', 'active'),
('Bangkok Suvarnabhumi Airport', 'BKK', 'Bangkok', 'Thailand', 'active'),
('Kuala Lumpur International Airport', 'KUL', 'Kuala Lumpur', 'Malaysia', 'active'),
('Jeddah King Abdulaziz International Airport', 'JED', 'Jeddah', 'Saudi Arabia', 'active'),
('Doha Hamad International Airport', 'DOH', 'Doha', 'Qatar', 'active'),
('London Heathrow Airport', 'LHR', 'London', 'UK', 'active'),
('Muscat International Airport', 'MCT', 'Muscat', 'Oman', 'active'),
('Istanbul Airport', 'IST', 'Istanbul', 'Turkey', 'active'),
('Toronto Pearson International Airport', 'YYZ', 'Toronto', 'Canada', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `status` = 'active';

-- Insert 50 Flights for Bangladesh Routes
-- DOMESTIC FLIGHTS (30 flights)

-- 1-6: Dhaka to Cox's Bazar
INSERT INTO `flights` (
    `flight_number`, `airline_id`, `departure_airport_id`, `arrival_airport_id`,
    `departure_time`, `arrival_time`, `duration`,
    `base_price_economy`, `base_price_business`,
    `total_seats_economy`, `total_seats_business`,
    `available_seats_economy`, `available_seats_business`,
    `status`
) VALUES
('BG-101', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 8 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 9 HOUR, 60,
 4500.00, 8500.00, 120, 20, 95, 18, 'scheduled'),
('BS-201', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 11 HOUR, 60,
 4200.00, 8000.00, 140, 20, 110, 18, 'scheduled'),
('VQ-301', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 15 HOUR, 60,
 4000.00, 7500.00, 130, 18, 105, 16, 'scheduled'),
('BG-102', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 9 HOUR, 60,
 4600.00, 8600.00, 120, 20, 90, 17, 'scheduled'),
('BS-202', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 11 HOUR, 60,
 4300.00, 8100.00, 140, 20, 115, 19, 'scheduled'),
('VQ-302', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 15 HOUR, 60,
 4100.00, 7600.00, 130, 18, 100, 15, 'scheduled'),

-- 7-12: Dhaka to Chittagong
('BG-103', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CGP'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 7 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 8 HOUR, 60,
 3500.00, 6500.00, 150, 30, 120, 25, 'scheduled'),
('BS-203', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CGP'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 12 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 13 HOUR, 60,
 3200.00, 6000.00, 140, 25, 110, 22, 'scheduled'),
('VQ-303', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CGP'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 9 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 10 HOUR, 60,
 3300.00, 6200.00, 130, 20, 105, 18, 'scheduled'),
('BG-104', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CGP'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 15 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 16 HOUR, 60,
 3600.00, 6700.00, 150, 30, 125, 28, 'scheduled'),
('BS-204', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CGP'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 11 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 12 HOUR, 60,
 3400.00, 6300.00, 140, 25, 115, 23, 'scheduled'),
('VQ-304', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CGP'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 13 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 14 HOUR, 60,
 3350.00, 6250.00, 130, 20, 100, 17, 'scheduled'),

-- 13-18: Dhaka to Sylhet
('BG-105', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'ZYL'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 9 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 10 HOUR, 60,
 3800.00, 7000.00, 120, 20, 95, 18, 'scheduled'),
('BS-205', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'ZYL'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 16 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 17 HOUR, 60,
 3500.00, 6500.00, 130, 18, 105, 15, 'scheduled'),
('VQ-305', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'ZYL'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 11 HOUR, 60,
 3600.00, 6700.00, 125, 20, 100, 17, 'scheduled'),
('BG-106', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'ZYL'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 9 HOUR, 60,
 3900.00, 7200.00, 120, 20, 90, 16, 'scheduled'),
('BS-206', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'ZYL'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 15 HOUR, 60,
 3700.00, 6800.00, 130, 18, 110, 16, 'scheduled'),
('VQ-306', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'ZYL'),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 11 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 12 HOUR, 60,
 3650.00, 6750.00, 125, 20, 105, 18, 'scheduled'),

-- 19-24: Dhaka to Jessore
('BS-207', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'JSR'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 11 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 12 HOUR, 60,
 3000.00, 5500.00, 100, 15, 75, 12, 'scheduled'),
('BG-107', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'JSR'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 13 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 14 HOUR, 60,
 3100.00, 5700.00, 110, 18, 85, 15, 'scheduled'),
('BS-208', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'JSR'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 11 HOUR, 60,
 3050.00, 5600.00, 100, 15, 80, 13, 'scheduled'),
('VQ-307', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'JSR'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 15 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 16 HOUR, 60,
 2950.00, 5400.00, 95, 12, 70, 10, 'scheduled'),
('BG-108', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'JSR'),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 9 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 10 HOUR, 60,
 3150.00, 5800.00, 110, 18, 90, 16, 'scheduled'),
('BS-209', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'JSR'),
 DATE_ADD(CURDATE(), INTERVAL 6 DAY) + INTERVAL 12 HOUR, DATE_ADD(CURDATE(), INTERVAL 6 DAY) + INTERVAL 13 HOUR, 60,
 3000.00, 5500.00, 100, 15, 75, 12, 'scheduled'),

-- 25-30: Other Domestic Routes
('BG-109', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'SPD'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 6 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 7 HOUR, 60,
 3200.00, 5900.00, 100, 15, 80, 13, 'scheduled'),
('BS-210', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'BZL'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 9 HOUR, 60,
 3400.00, 6300.00, 90, 12, 70, 10, 'scheduled'),
('VQ-308', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'CGP'), (SELECT id FROM airports WHERE code = 'CXB'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 10 HOUR + INTERVAL 30 MINUTE, 30,
 2500.00, 4500.00, 80, 10, 60, 8, 'scheduled'),
('BG-110', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'ZYL'), (SELECT id FROM airports WHERE code = 'DAC'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 18 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 19 HOUR, 60,
 3800.00, 7000.00, 120, 20, 95, 18, 'scheduled'),
('BS-211', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'CXB'), (SELECT id FROM airports WHERE code = 'DAC'),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR, 60,
 4500.00, 8500.00, 120, 20, 100, 17, 'scheduled'),
('VQ-309', (SELECT id FROM airlines WHERE code = 'VQ'), (SELECT id FROM airports WHERE code = 'CGP'), (SELECT id FROM airports WHERE code = 'DAC'),
 DATE_ADD(CURDATE(), INTERVAL 6 DAY) + INTERVAL 19 HOUR, DATE_ADD(CURDATE(), INTERVAL 6 DAY) + INTERVAL 20 HOUR, 60,
 3500.00, 6500.00, 150, 30, 125, 28, 'scheduled'),

-- INTERNATIONAL FLIGHTS (20 flights)
-- 31-36: Dhaka to Dubai
('EK-571', (SELECT id FROM airlines WHERE code = 'EK'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DXB'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 2 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 7 HOUR, 300,
 35000.00, 85000.00, 200, 40, 150, 30, 'scheduled'),
('BG-085', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DXB'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 22 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 3 HOUR, 300,
 32000.00, 75000.00, 180, 35, 140, 28, 'scheduled'),
('EK-572', (SELECT id FROM airlines WHERE code = 'EK'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DXB'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 1 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 6 HOUR, 300,
 36000.00, 87000.00, 200, 40, 160, 32, 'scheduled'),
('BS-401', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DXB'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 21 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 2 HOUR, 300,
 33000.00, 78000.00, 160, 30, 130, 25, 'scheduled'),
('BG-086', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DXB'),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 3 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 8 HOUR, 300,
 34000.00, 80000.00, 180, 35, 145, 30, 'scheduled'),
('EK-573', (SELECT id FROM airlines WHERE code = 'EK'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DXB'),
 DATE_ADD(CURDATE(), INTERVAL 7 DAY) + INTERVAL 23 HOUR, DATE_ADD(CURDATE(), INTERVAL 8 DAY) + INTERVAL 4 HOUR, 300,
 35500.00, 86000.00, 200, 40, 155, 31, 'scheduled'),

-- 37-40: Dhaka to Singapore
('SQ-446', (SELECT id FROM airlines WHERE code = 'SQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'SIN'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 23 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 5 HOUR, 240,
 38000.00, 95000.00, 220, 50, 180, 40, 'scheduled'),
('BG-151', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'SIN'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 22 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 4 HOUR, 240,
 37000.00, 92000.00, 200, 45, 170, 38, 'scheduled'),
('SQ-447', (SELECT id FROM airlines WHERE code = 'SQ'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'SIN'),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 1 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 7 HOUR, 240,
 39000.00, 97000.00, 220, 50, 190, 42, 'scheduled'),
('BS-402', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'SIN'),
 DATE_ADD(CURDATE(), INTERVAL 7 DAY) + INTERVAL 21 HOUR, DATE_ADD(CURDATE(), INTERVAL 8 DAY) + INTERVAL 3 HOUR, 240,
 37500.00, 94000.00, 180, 40, 150, 35, 'scheduled'),

-- 41-44: Dhaka to Bangkok
('TK-713', (SELECT id FROM airlines WHERE code = 'TK'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'BKK'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 1 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 3 HOUR + INTERVAL 30 MINUTE, 150,
 25000.00, 60000.00, 180, 30, 145, 25, 'scheduled'),
('BG-201', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'BKK'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 20 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 23 HOUR + INTERVAL 30 MINUTE, 150,
 24500.00, 58000.00, 160, 28, 130, 23, 'scheduled'),
('TK-714', (SELECT id FROM airlines WHERE code = 'TK'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'BKK'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 2 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 4 HOUR + INTERVAL 30 MINUTE, 150,
 25500.00, 61000.00, 180, 30, 150, 27, 'scheduled'),
('BS-403', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'BKK'),
 DATE_ADD(CURDATE(), INTERVAL 6 DAY) + INTERVAL 19 HOUR, DATE_ADD(CURDATE(), INTERVAL 6 DAY) + INTERVAL 22 HOUR + INTERVAL 30 MINUTE, 150,
 24800.00, 59000.00, 170, 25, 140, 22, 'scheduled'),

-- 45-48: Dhaka to Kolkata
('BG-371', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CCU'),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 15 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 16 HOUR, 60,
 8500.00, 18000.00, 120, 20, 95, 16, 'scheduled'),
('AI-201', (SELECT id FROM airlines WHERE code = 'AI'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CCU'),
 DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 15 HOUR, 60,
 8200.00, 17500.00, 110, 18, 90, 15, 'scheduled'),
('BG-372', (SELECT id FROM airlines WHERE code = 'BG'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CCU'),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR, 60,
 8700.00, 18500.00, 120, 20, 100, 17, 'scheduled'),
('BS-404', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'CCU'),
 DATE_ADD(CURDATE(), INTERVAL 7 DAY) + INTERVAL 13 HOUR, DATE_ADD(CURDATE(), INTERVAL 7 DAY) + INTERVAL 14 HOUR, 60,
 8000.00, 17000.00, 100, 15, 80, 13, 'scheduled'),

-- 49-50: Other International Routes
('BS-311', (SELECT id FROM airlines WHERE code = 'BS'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'KUL'),
 DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 20 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 1 HOUR, 240,
 28000.00, 65000.00, 160, 25, 130, 20, 'scheduled'),
('QR-615', (SELECT id FROM airlines WHERE code = 'QR'), (SELECT id FROM airports WHERE code = 'DAC'), (SELECT id FROM airports WHERE code = 'DOH'),
 DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 4 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 9 HOUR, 300,
 32000.00, 78000.00, 190, 38, 155, 32, 'scheduled');

-- Insert Promotions
INSERT INTO `promotions` (`code`, `description`, `discount_type`, `discount_value`, `valid_from`, `valid_until`, `status`) VALUES
('WELCOME10', 'Welcome offer - 10% off on your first booking', 'percentage', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active'),
('DOMESTIC500', 'Flat ৳500 off on domestic flights', 'fixed', 500.00, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 'active'),
('COXBAZAR15', '15% off on Cox\'s Bazar flights', 'percentage', 15.00, NOW(), DATE_ADD(NOW(), INTERVAL 20 DAY), 'active'),
('EARLYBIRD', 'Early bird discount - Book 7 days in advance', 'percentage', 12.00, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), 'active')
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`);

-- Insert Pricing Rules
INSERT INTO `pricing_rules` (`name`, `rule_type`, `condition_value`, `adjustment_type`, `adjustment_value`, `status`) VALUES
('Early Bird Discount', 'time_based', '7', 'percentage', -10.00, 'active'),
('Last Minute Premium', 'time_based', '2', 'percentage', 25.00, 'active'),
('Low Availability Surge', 'seat_based', '20', 'percentage', 20.00, 'active'),
('High Demand Peak', 'demand_based', '50', 'percentage', 15.00, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert GoZayaan System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('gozayaan_last_sync', NULL),
('gozayaan_sync_enabled', '1'),
('gozayaan_sync_interval', '3600')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- ============================================
-- COMPLETION MESSAGE
-- ============================================
SELECT 'FlyOn Database Setup Complete!' as Status,
       (SELECT COUNT(*) FROM airlines) as Airlines,
       (SELECT COUNT(*) FROM airports) as Airports,
       (SELECT COUNT(*) FROM flights) as Flights,
       (SELECT COUNT(*) FROM users) as Users;
