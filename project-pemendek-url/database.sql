-- =============================================
-- Database: pemendek_url
-- URL Shortener Database Schema
-- =============================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `pemendek_url` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `pemendek_url`;

-- =============================================
-- Table: users
-- Stores user authentication and profile data
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: urls
-- Stores shortened URLs and analytics
-- =============================================
CREATE TABLE IF NOT EXISTS `urls` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NULL,
  `alias` VARCHAR(10) NOT NULL UNIQUE,
  `original_url` TEXT NOT NULL,
  `clicks` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_clicked_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_alias` (`alias`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_alias` (`alias`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_clicks` (`clicks`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: click_analytics
-- Detailed click tracking for analytics
-- =============================================
CREATE TABLE IF NOT EXISTS `click_analytics` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `url_id` INT(11) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `referer` TEXT NULL,
  `country` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `device_type` VARCHAR(50) NULL,
  `browser` VARCHAR(50) NULL,
  `os` VARCHAR(50) NULL,
  `clicked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_url_id` (`url_id`),
  INDEX `idx_clicked_at` (`clicked_at`),
  FOREIGN KEY (`url_id`) REFERENCES `urls`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `users` (`username`, `email`, `password`, `role`) 
VALUES (
  'admin', 
  'admin@urlshortener.com', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  'admin'
) ON DUPLICATE KEY UPDATE `username`=`username`;

INSERT INTO `users` (`username`, `email`, `password`, `role`) 
VALUES (
  'user', 
  'user@example.com', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  'user'
) ON DUPLICATE KEY UPDATE `username`=`username`;


INSERT INTO `urls` (`user_id`, `alias`, `original_url`, `clicks`) VALUES
(1, 'google', 'https://www.google.com', 150),
(1, 'github', 'https://github.com', 89),
(2, 'youtube', 'https://www.youtube.com', 234),
(NULL, 'example', 'https://www.example.com', 45)
ON DUPLICATE KEY UPDATE `alias`=`alias`;




CREATE OR REPLACE VIEW `v_top_urls` AS
SELECT 
  u.id,
  u.alias,
  u.original_url,
  u.clicks,
  u.created_at,
  us.username,
  us.email
FROM urls u
LEFT JOIN users us ON u.user_id = us.id
WHERE u.is_active = 1
ORDER BY u.clicks DESC
LIMIT 100;


CREATE OR REPLACE VIEW `v_user_stats` AS
SELECT 
  u.id,
  u.username,
  u.email,
  u.role,
  COUNT(url.id) as total_urls,
  COALESCE(SUM(url.clicks), 0) as total_clicks,
  u.created_at
FROM users u
LEFT JOIN urls url ON u.id = url.user_id
GROUP BY u.id;



DELIMITER $$


CREATE PROCEDURE IF NOT EXISTS `sp_get_url_stats`(IN url_id INT)
BEGIN
  SELECT 
    u.*,
    us.username,
    us.email,
    COUNT(ca.id) as detailed_clicks
  FROM urls u
  LEFT JOIN users us ON u.user_id = us.id
  LEFT JOIN click_analytics ca ON u.id = ca.url_id
  WHERE u.id = url_id
  GROUP BY u.id;
END$$


CREATE PROCEDURE IF NOT EXISTS `sp_clean_old_analytics`()
BEGIN
  DELETE FROM click_analytics 
  WHERE clicked_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
END$$

DELIMITER ;



DELIMITER $$


CREATE TRIGGER IF NOT EXISTS `tr_update_last_clicked` 
BEFORE UPDATE ON `urls`
FOR EACH ROW
BEGIN
  IF NEW.clicks > OLD.clicks THEN
    SET NEW.last_clicked_at = CURRENT_TIMESTAMP;
  END IF;
END$$

DELIMITER ;

