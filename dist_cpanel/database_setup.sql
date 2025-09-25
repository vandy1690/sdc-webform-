-- SDC Webform Database Setup
-- Run this SQL script in your cPanel MySQL database

-- Create bid_requests table
CREATE TABLE IF NOT EXISTS `bid_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `project_type` varchar(50) NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `budget` varchar(50) NOT NULL,
  `timeline` varchar(50) NOT NULL,
  `services` json DEFAULT NULL,
  `referral` varchar(50) DEFAULT NULL,
  `status` enum('new','reviewing','quoted','accepted','rejected') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rate_limits table for rate limiting functionality
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_created` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional - remove this section if you don't want sample data)
-- INSERT INTO `bid_requests`
-- (`first_name`, `last_name`, `email`, `phone`, `company`, `project_type`,
--  `project_title`, `description`, `budget`, `timeline`, `services`, `referral`, `status`)
-- VALUES
-- ('John', 'Doe', 'john.doe@example.com', '555-0123', 'Acme Corp', 'web-design',
--  'Company Website Redesign', 'We need a modern, responsive website that showcases our products and services.',
--  '10k-25k', '2-3-months', '["web-design", "ui-ux"]', 'search', 'new'),
-- ('Jane', 'Smith', 'jane.smith@example.com', '555-0124', 'StartupXYZ', 'brand-identity',
--  'Complete Brand Identity Package', 'Looking for a complete brand identity including logo, colors, and style guide.',
--  '5k-10k', '1-month', '["brand-identity", "print-design"]', 'referral', 'reviewing');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_project_type` ON `bid_requests` (`project_type`);
CREATE INDEX IF NOT EXISTS `idx_budget` ON `bid_requests` (`budget`);
CREATE INDEX IF NOT EXISTS `idx_timeline` ON `bid_requests` (`timeline`);

-- Show tables to confirm creation
SHOW TABLES;

-- Show structure of bid_requests table
DESCRIBE `bid_requests`;

-- Show structure of rate_limits table
DESCRIBE `rate_limits`;