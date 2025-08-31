-- Payment Transactions Table
-- This table stores all payment transaction records with comprehensive validation and security

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(100) NOT NULL UNIQUE,
  `customer_email` varchar(255) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `organization` varchar(100) DEFAULT NULL,
  `plan_type` enum('team', 'enterprise') NOT NULL,
  `billing_frequency` enum('monthly', 'annual') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'GHS',
  `payment_method` enum('credit-card', 'mobile-money', 'paypal') NOT NULL,
  `payment_details` text DEFAULT NULL COMMENT 'JSON stored payment details (encrypted)',
  `gateway_transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `fraud_score` tinyint(3) DEFAULT 0 COMMENT 'Fraud detection score 0-100',
  `validation_errors` text DEFAULT NULL COMMENT 'JSON stored validation errors if any',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_customer_email` (`customer_email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_plan_billing` (`plan_type`, `billing_frequency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Attempts Log Table
-- This table logs all payment attempts for fraud detection and analytics

CREATE TABLE IF NOT EXISTS `payment_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `email_attempted` varchar(255) DEFAULT NULL,
  `plan_type` varchar(20) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `status` enum('validation_failed', 'processing', 'success', 'failed') NOT NULL,
  `error_details` text DEFAULT NULL,
  `fraud_indicators` text DEFAULT NULL COMMENT 'JSON stored fraud indicators',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_email_attempted` (`email_attempted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fraud Detection Rules Table
-- This table stores dynamic fraud detection rules

CREATE TABLE IF NOT EXISTS `fraud_detection_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(100) NOT NULL,
  `rule_type` enum('ip_limit', 'email_pattern', 'velocity_check', 'amount_limit', 'custom') NOT NULL,
  `rule_config` text NOT NULL COMMENT 'JSON configuration for the rule',
  `severity` tinyint(3) DEFAULT 1 COMMENT 'Rule severity 1-10',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rule_type` (`rule_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blocked IPs Table
-- This table stores temporarily or permanently blocked IP addresses

CREATE TABLE IF NOT EXISTS `blocked_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `block_type` enum('temporary', 'permanent') DEFAULT 'temporary',
  `reason` varchar(255) NOT NULL,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `attempts_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip` (`ip_address`),
  KEY `idx_blocked_until` (`blocked_until`),
  KEY `idx_block_type` (`block_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Subscriptions Table
-- This table tracks active subscriptions created from successful payments

CREATE TABLE IF NOT EXISTS `customer_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_email` varchar(255) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `plan_type` enum('team', 'enterprise') NOT NULL,
  `billing_frequency` enum('monthly', 'annual') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('active', 'suspended', 'cancelled', 'expired') DEFAULT 'active',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `features` text DEFAULT NULL COMMENT 'JSON stored plan features',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_email` (`customer_email`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_date` (`end_date`),
  FOREIGN KEY (`transaction_id`) REFERENCES `payment_transactions` (`transaction_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default fraud detection rules

INSERT INTO `fraud_detection_rules` (`rule_name`, `rule_type`, `rule_config`, `severity`, `is_active`) VALUES
('Max Daily Attempts Per IP', 'ip_limit', '{"max_attempts": 10, "time_window": 86400}', 5, 1),
('Suspicious Email Patterns', 'email_pattern', '{"patterns": ["^[0-9]+@", "@[0-9]+\\.", "\\.\\."], "action": "flag"}', 3, 1),
('High Velocity Transactions', 'velocity_check', '{"max_transactions": 3, "time_window": 300}', 7, 1),
('Large Amount Threshold', 'amount_limit', '{"threshold": 1000, "require_verification": true}', 4, 1),
('Temporary Email Domains', 'email_pattern', '{"domains": ["tempmail.com", "10minutemail.com", "guerrillamail.com"], "action": "block"}', 8, 1);

-- Create indexes for better performance

CREATE INDEX idx_payment_transactions_customer_plan ON payment_transactions(customer_email, plan_type);
CREATE INDEX idx_payment_attempts_ip_time ON payment_attempts(ip_address, created_at);
CREATE INDEX idx_customer_subscriptions_active ON customer_subscriptions(status, end_date) WHERE status = 'active';

-- Add comments to tables

ALTER TABLE `payment_transactions` COMMENT = 'Stores all payment transaction records with comprehensive validation and security';
ALTER TABLE `payment_attempts` COMMENT = 'Logs all payment attempts for fraud detection and analytics';
ALTER TABLE `fraud_detection_rules` COMMENT = 'Stores dynamic fraud detection rules';
ALTER TABLE `blocked_ips` COMMENT = 'Stores temporarily or permanently blocked IP addresses';
ALTER TABLE `customer_subscriptions` COMMENT = 'Tracks active subscriptions created from successful payments';
