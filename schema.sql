-- Artist Portfolio Database Schema
-- MySQL 5.7+ / MariaDB 10.3+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database creation (adjust name as needed)
-- CREATE DATABASE IF NOT EXISTS `artist_portfolio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `artist_portfolio`;

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------

CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL,
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: artworks
-- --------------------------------------------------------

CREATE TABLE `artworks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `year` YEAR,
  `technique` VARCHAR(100),
  `dimensions` VARCHAR(50),
  `price` DECIMAL(10,2) DEFAULT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `thumbnail` VARCHAR(255) NOT NULL,
  `webp_filename` VARCHAR(255),
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_featured` (`is_featured`),
  INDEX `idx_published` (`is_published`),
  INDEX `idx_sort` (`sort_order`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: inquiries
-- --------------------------------------------------------

CREATE TABLE `inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `artwork_title` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `preferred_size` VARCHAR(50),
  `preferred_color` VARCHAR(50),
  `status` ENUM('new', 'contacted', 'completed', 'cancelled') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_created` (`created_at`),
  INDEX `idx_status` (`status`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------

CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `setting_type` ENUM('text', 'number', 'boolean', 'email', 'phone') DEFAULT 'text',
  `description` VARCHAR(255),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Default settings
-- --------------------------------------------------------

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('whatsapp_phone', '+447123456789', 'phone', 'WhatsApp contact number (E.164 format)'),
('artist_email', 'artist@example.com', 'email', 'Artist email for notifications'),
('max_upload_size', '8', 'number', 'Maximum upload size in MB'),
('site_title', 'Artist Portfolio', 'text', 'Website title'),
('site_description', 'Contemporary art portfolio', 'text', 'Website description'),
('artworks_per_page', '12', 'number', 'Artworks to display per page'),
('enable_prices', '1', 'boolean', 'Show prices on gallery'),
('enable_inquiries', '1', 'boolean', 'Enable WhatsApp inquiry form'),
('gallery_columns', '3', 'number', 'Gallery grid columns (desktop)');

-- --------------------------------------------------------
-- Sample data (optional - for testing)
-- --------------------------------------------------------

-- Sample admin (password: admin123)
INSERT INTO `admins` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@arossiartwork.com');

-- Sample artwork (requires actual image files)
-- INSERT INTO `artworks` (`title`, `description`, `year`, `technique`, `dimensions`, `price`, `filename`, `thumbnail`, `webp_filename`, `is_featured`, `is_published`) VALUES
-- ('Sunset Dreams', 'Oil on canvas depicting a vibrant sunset', 2024, 'Oil on Canvas', '60x80 cm', 450.00, 'sunset_dreams.jpg', 'sunset_dreams_thumb.jpg', 'sunset_dreams.webp', 1, 1);

-- --------------------------------------------------------
-- End of schema
-- --------------------------------------------------------
