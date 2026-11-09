-- Sanitized SQL export for Campus Lost & Found Management System
-- Personal user records removed for public repository safety.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `campus_lost_found`;
USE `campus_lost_found`;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Mobile'), (2, 'ID Card'), (3, 'Book'), (4, 'Wallet'),
(5, 'Calculator'), (6, 'Electronics'), (7, 'Other');

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- No real user rows included. Create test accounts through the application.

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `item_date` date DEFAULT NULL,
  `post_type` enum('Lost','Found') DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `fk_items_user` (`user_id`),
  KEY `fk_items_category` (`category_id`),
  CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  CONSTRAINT `fk_items_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `items`
(`item_id`, `description`, `user_id`, `category_id`, `item_name`, `location`, `item_date`, `post_type`, `status`, `created_at`) VALUES
(1, 'Sample mobile phone lost on campus', NULL, 1, 'Sample Mobile', 'Library', '2026-10-15', 'Lost', 'Returned', '2026-10-15 07:08:03'),
(2, 'Sample student ID card found near classroom', NULL, 2, 'Sample ID Card', 'Building A', '2026-10-15', 'Found', 'active', '2026-10-15 07:11:10'),
(3, 'Sample blue bottle lost near library', NULL, 7, 'Blue Water Bottle', 'Central Library', '2026-10-16', 'Lost', 'Active', '2026-10-16 15:13:30');

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `claim_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `claim_status` varchar(20) DEFAULT 'Pending',
  `claim_message` text DEFAULT NULL,
  PRIMARY KEY (`claim_id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
