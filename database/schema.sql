-- Institute Placement & Alumni Tracking Portal
-- Database Schema: placement_alumni_portal

CREATE DATABASE IF NOT EXISTS `placement_alumni_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `placement_alumni_portal`;

-- Drop existing tables in reverse dependency order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `mentorship_requests`;
DROP TABLE IF EXISTS `placements`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `drives`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `alumni`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table (Core Auth & Roles)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'officer', 'student', 'alumni') NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Students Table
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `roll_no` VARCHAR(50) NOT NULL UNIQUE,
    `branch` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL DEFAULT 4,
    `cgpa` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `phone` VARCHAR(25) DEFAULT NULL,
    `resume` VARCHAR(255) DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Alumni Table
CREATE TABLE `alumni` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `graduation_year` INT NOT NULL,
    `branch` VARCHAR(50) NOT NULL,
    `company` VARCHAR(120) DEFAULT NULL,
    `job_role` VARCHAR(120) DEFAULT NULL,
    `location` VARCHAR(120) DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `linkedin` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `available_for_mentorship` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Companies Table
CREATE TABLE `companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(120) NOT NULL,
    `industry` VARCHAR(100) DEFAULT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Drives Table
CREATE TABLE `drives` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `job_role` VARCHAR(120) NOT NULL,
    `package` DECIMAL(10,2) NOT NULL COMMENT 'Package in LPA (Lakhs Per Annum)',
    `location` VARCHAR(120) NOT NULL,
    `drive_date` DATE NOT NULL,
    `deadline` DATE NOT NULL,
    `eligibility_criteria` TEXT DEFAULT NULL,
    `skills` TEXT DEFAULT NULL,
    `vacancies` INT DEFAULT 1,
    `min_cgpa` DECIMAL(3,2) DEFAULT 0.00,
    `eligible_branches` VARCHAR(255) DEFAULT 'All Branches',
    `status` ENUM('upcoming', 'active', 'completed', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_drives_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Applications Table
CREATE TABLE `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `drive_id` INT NOT NULL,
    `applied_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Applied', 'Shortlisted', 'Interview', 'Selected', 'Rejected') DEFAULT 'Applied',
    `notes` TEXT DEFAULT NULL,
    `resume_path` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_drive` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`id`) ON DELETE CASCADE,
    CONSTRAINT `unique_student_drive` UNIQUE (`student_id`, `drive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Placements Table
CREATE TABLE `placements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `company_id` INT NOT NULL,
    `drive_id` INT DEFAULT NULL,
    `package` DECIMAL(10,2) NOT NULL,
    `placement_year` INT NOT NULL,
    `offer_letter` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_placements_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_placements_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_placements_drive` FOREIGN KEY (`drive_id`) REFERENCES `drives` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Mentorship Requests Table
CREATE TABLE `mentorship_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `alumni_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Pending', 'Accepted', 'Rejected', 'Completed') DEFAULT 'Pending',
    `response_message` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mentorship_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mentorship_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for search & filter optimization
CREATE INDEX idx_student_branch ON `students`(`branch`);
CREATE INDEX idx_student_cgpa ON `students`(`cgpa`);
CREATE INDEX idx_alumni_branch ON `alumni`(`branch`);
CREATE INDEX idx_alumni_year ON `alumni`(`graduation_year`);
CREATE INDEX idx_alumni_company ON `alumni`(`company`);
CREATE INDEX idx_drives_deadline ON `drives`(`deadline`);
CREATE INDEX idx_drives_status ON `drives`(`status`);
CREATE INDEX idx_app_status ON `applications`(`status`);
