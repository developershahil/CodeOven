-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 08, 2025 at 12:32 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `editor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_files`
--

DROP TABLE IF EXISTS `tbl_files`;
CREATE TABLE IF NOT EXISTS `tbl_files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('html','css','js') NOT NULL,
  `file_content` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `line_numbers` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`file_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_preferences`
--

DROP TABLE IF EXISTS `tbl_preferences`;
CREATE TABLE IF NOT EXISTS `tbl_preferences` (
  `preference_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `layout` varchar(200) NOT NULL DEFAULT 'default',
  `word_wrap` tinyint(1) NOT NULL DEFAULT '1',
  `show_line_numbers` tinyint(1) NOT NULL DEFAULT '1',
  `auto_save` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`preference_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` char(60) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `failed_login_attempts` int NOT NULL DEFAULT '0',
  `last_login_at` datetime DEFAULT NULL,
  `locked_until` datetime DEFAULT NULL,
  `password_updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'xyz', 'xyz@gmail.com', '$2y$10$VZEvLMr9lbTdZVFTJWGRe.2JiAHZ//dQrgrHbKs/wDk.ZpWhloxE2', '2025-09-06 17:04:59'),
(2, 'abc', 'abc@gmail.com', '$2y$10$1LD7LLGXfrzvxyp..SEi0uP7K7gmb80XxAFWI8N8p32GJEQ2xjIyS', '2025-09-06 17:15:50');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_files`
--
ALTER TABLE `tbl_files`
  ADD CONSTRAINT `tbl_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_preferences`
--
ALTER TABLE `tbl_preferences`
  ADD CONSTRAINT `tbl_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

DROP TABLE IF EXISTS `tbl_user_workspaces`;
CREATE TABLE IF NOT EXISTS `tbl_user_workspaces` (
  `workspace_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `workspace_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`workspace_id`),
  UNIQUE KEY `uniq_workspace_user` (`user_id`),
  CONSTRAINT `fk_workspace_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- CREATE TABLE IF NOT EXISTS `tbl_users` (
--   `user_id` int NOT NULL AUTO_INCREMENT,
--   `username` varchar(50) NOT NULL,
--   `email` varchar(255) NOT NULL,
--   `password_hash` char(60) NOT NULL,
--   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   PRIMARY KEY (`user_id`),
--   UNIQUE KEY `username` (`username`),
--   UNIQUE KEY `email` (`email`)
-- ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;