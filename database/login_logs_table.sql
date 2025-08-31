-- Create login_logs table for tracking user login activities with detailed information
CREATE TABLE IF NOT EXISTS `login_logs` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `studentID` VARCHAR(50) NOT NULL,
    `user_role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `browser_info` VARCHAR(500) DEFAULT NULL,
    `location_info` VARCHAR(500) DEFAULT NULL,
    `login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_successful` BOOLEAN DEFAULT TRUE,
    `session_id` VARCHAR(128) DEFAULT NULL,
    PRIMARY KEY (`log_id`),
    KEY `idx_student_id` (`studentID`),
    KEY `idx_login_time` (`login_time`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_student_role` (`studentID`, `user_role`),
    KEY `idx_time_ip` (`login_time`, `ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to existing table if they don't exist
ALTER TABLE `login_logs` 
ADD COLUMN IF NOT EXISTS `browser_info` VARCHAR(500) DEFAULT NULL AFTER `user_agent`,
ADD COLUMN IF NOT EXISTS `location_info` VARCHAR(500) DEFAULT NULL AFTER `browser_info`;

-- Update existing indexes
ALTER TABLE `login_logs` 
ADD INDEX IF NOT EXISTS `idx_browser_info` (`browser_info`(255)),
ADD INDEX IF NOT EXISTS `idx_location_info` (`location_info`(255));
