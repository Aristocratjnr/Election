-- Create login_logs table for tracking user login activities
CREATE TABLE IF NOT EXISTS `login_logs` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `studentID` VARCHAR(50) NOT NULL,
    `user_role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `location_info` VARCHAR(255) DEFAULT NULL,
    `browser_info` VARCHAR(255) DEFAULT NULL,
    `is_successful` BOOLEAN DEFAULT TRUE,
    `session_id` VARCHAR(128) DEFAULT NULL,
    PRIMARY KEY (`log_id`),
    KEY `idx_student_id` (`studentID`),
    KEY `idx_login_time` (`login_time`),
    KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for better performance
ALTER TABLE `login_logs` 
ADD INDEX `idx_student_role` (`studentID`, `user_role`),
ADD INDEX `idx_time_ip` (`login_time`, `ip_address`);
