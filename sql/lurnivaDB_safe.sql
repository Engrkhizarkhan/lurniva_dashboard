-- Safe Import Script for lurnivaDB
-- This script prevents duplicate table errors and ensures clean import
-- Usage: mysql -u username -p database_name < lurnivaDB_safe.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Drop all tables first to ensure clean import
SET @tables = NULL;
SELECT GROUP_CONCAT(table_name) INTO @tables 
FROM information_schema.tables 
WHERE table_schema = DATABASE();

SET @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);
PREPARE stmt FROM @tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- Table structure for table `activity_logs`
-- ========================================

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ========================================
-- Table structure for table `app_admin`
-- ========================================

CREATE TABLE IF NOT EXISTS `app_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `message_email` varchar(150) DEFAULT NULL,
  `merchant_id` varchar(150) DEFAULT NULL,
  `store_id` varchar(150) DEFAULT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `role` enum('super_admin') DEFAULT 'super_admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verification_code` varchar(10) DEFAULT NULL,
  `code_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Insert default admin data
INSERT IGNORE INTO `app_admin` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `profile_image`, `message_email`, `merchant_id`, `store_id`, `secret_key`, `role`, `status`, `created_at`, `updated_at`, `verification_code`, `code_expires_at`) VALUES
(1, 'usman', 'admin@lurniva.com', '$2y$10$vu1OqO/ZJC/YKARHx0LlRueL/JojWg3AguqTPbOyyXmJl5kcjELeO', NULL, NULL, NULL, 'shayans1215225@gmail.com', NULL, NULL, NULL, 'super_admin', 'active', '2025-09-09 14:08:19', '2025-09-09 14:57:56', '845371', '2025-09-09 17:02:56');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Success message
SELECT 'Database import completed successfully. Please run the main SQL file now.' as Status;
