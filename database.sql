-- FILE: /database.sql
-- SplashDrive Database Schema
-- Multi-tenant SaaS File Storage Platform

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS splashdrive CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splashdrive;

-- ============================================================================
-- Table: tenants
-- Stores organization/company information
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: users
-- Stores user accounts
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'user', 'read_only') DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_role` (`role`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: subscription_plans
-- Stores available subscription plans
-- ============================================================================
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `price_monthly` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `price_yearly` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `max_storage_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `max_users` INT UNSIGNED NOT NULL DEFAULT 0,
  `max_files` INT UNSIGNED NOT NULL DEFAULT 0,
  `features` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: tenant_subscriptions
-- Stores tenant subscription information
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tenant_subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly',
  `status` ENUM('active', 'inactive', 'trial', 'cancelled', 'expired') DEFAULT 'trial',
  `trial_ends_at` TIMESTAMP NULL DEFAULT NULL,
  `current_period_start` TIMESTAMP NULL DEFAULT NULL,
  `current_period_end` TIMESTAMP NULL DEFAULT NULL,
  `cancelled_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_plan_id` (`plan_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: folders
-- Stores folder hierarchy
-- ============================================================================
CREATE TABLE IF NOT EXISTS `folders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `path` VARCHAR(1024) NOT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_parent_id` (`parent_id`),
  INDEX `idx_created_by` (`created_by`),
  INDEX `idx_is_deleted` (`is_deleted`),
  INDEX `idx_path` (`path`(255)),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: files
-- Stores file metadata
-- ============================================================================
CREATE TABLE IF NOT EXISTS `files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `folder_id` INT UNSIGNED DEFAULT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `stored_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(1024) NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `extension` VARCHAR(20) DEFAULT NULL,
  `uploaded_by` INT UNSIGNED NOT NULL,
  `visibility` ENUM('private', 'tenant', 'shared_link') DEFAULT 'private',
  `download_count` INT UNSIGNED DEFAULT 0,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_folder_id` (`folder_id`),
  INDEX `idx_uploaded_by` (`uploaded_by`),
  INDEX `idx_is_deleted` (`is_deleted`),
  INDEX `idx_mime_type` (`mime_type`),
  INDEX `idx_created_at` (`created_at`),
  FULLTEXT INDEX `idx_fulltext_filename` (`original_filename`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: file_shares
-- Stores public sharing links
-- ============================================================================
CREATE TABLE IF NOT EXISTS `file_shares` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `file_id` INT UNSIGNED DEFAULT NULL,
  `folder_id` INT UNSIGNED DEFAULT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `share_type` ENUM('file', 'folder') NOT NULL,
  `permissions` ENUM('view', 'download') DEFAULT 'download',
  `password` VARCHAR(255) DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `access_count` INT UNSIGNED DEFAULT 0,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`token`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_file_id` (`file_id`),
  INDEX `idx_folder_id` (`folder_id`),
  INDEX `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: invoices
-- Stores billing invoices
-- ============================================================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `status` ENUM('pending', 'paid', 'cancelled', 'overdue') DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_subscription_id` (`subscription_id`),
  INDEX `idx_invoice_number` (`invoice_number`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: payments
-- Stores payment records
-- ============================================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'credit_card',
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
  `metadata` TEXT DEFAULT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_invoice_id` (`invoice_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: activity_logs
-- Stores audit trail of all actions
-- ============================================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action_type` VARCHAR(50) NOT NULL,
  `target_type` VARCHAR(50) DEFAULT NULL,
  `target_id` INT UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `metadata` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_action_type` (`action_type`),
  INDEX `idx_target_type` (`target_type`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: api_keys
-- Stores API keys for tenant authentication
-- ============================================================================
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `key_name` VARCHAR(255) NOT NULL,
  `api_key` VARCHAR(64) NOT NULL UNIQUE,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_api_key` (`api_key`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_is_active` (`is_active`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table: notifications
-- Stores notification logs (simulated email)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `recipient_email` VARCHAR(255) NOT NULL,
  `is_sent` TINYINT(1) DEFAULT 0,
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_is_sent` (`is_sent`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Insert subscription plans
INSERT INTO `subscription_plans` (`name`, `slug`, `description`, `price_monthly`, `price_yearly`, `max_storage_bytes`, `max_users`, `max_files`, `features`, `is_active`, `sort_order`) VALUES
('Free', 'free', 'Perfect for individuals and small teams', 0.00, 0.00, 1073741824, 3, 100, 'Basic file storage, 1GB storage, Up to 3 users, 100 files limit', 1, 1),
('Starter', 'starter', 'Great for growing teams', 19.99, 199.99, 21474836480, 10, 1000, 'Advanced file storage, 20GB storage, Up to 10 users, 1000 files limit, Priority support', 1, 2),
('Professional', 'professional', 'For professional teams', 49.99, 499.99, 107374182400, 50, 10000, 'Professional file storage, 100GB storage, Up to 50 users, 10000 files limit, Advanced sharing, Priority support, API access', 1, 3),
('Enterprise', 'enterprise', 'For large organizations', 199.99, 1999.99, 1099511627776, 500, 100000, 'Enterprise file storage, 1TB storage, Up to 500 users, 100000 files limit, Advanced sharing, Custom integrations, Dedicated support, API access, Advanced analytics', 1, 4);

-- Insert platform admin user
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `status`) VALUES
(NULL, 'Platform Admin', 'admin@splashdrive.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', 'active');
-- Password: password

-- Insert first tenant: TechCorp Solutions
INSERT INTO `tenants` (`name`, `slug`, `email`, `phone`, `address`, `website`, `status`) VALUES
('TechCorp Solutions', 'techcorp-solutions', 'contact@techcorp.com', '+1-555-0100', '123 Tech Street, Silicon Valley, CA 94000', 'https://techcorp.com', 'active');
SET @tenant1_id = LAST_INSERT_ID();

-- Insert second tenant: Creative Agency Inc
INSERT INTO `tenants` (`name`, `slug`, `email`, `phone`, `address`, `website`, `status`) VALUES
('Creative Agency Inc', 'creative-agency', 'hello@creativeagency.com', '+1-555-0200', '456 Design Avenue, New York, NY 10001', 'https://creativeagency.com', 'active');
SET @tenant2_id = LAST_INSERT_ID();

-- Insert subscriptions for tenants
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `billing_cycle`, `status`, `trial_ends_at`, `current_period_start`, `current_period_end`) VALUES
(@tenant1_id, 3, 'monthly', 'active', NULL, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH)),
(@tenant2_id, 2, 'yearly', 'trial', DATE_ADD(NOW(), INTERVAL 14 DAY), NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Insert users for TechCorp Solutions (Tenant 1)
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `status`) VALUES
(@tenant1_id, 'John Smith', 'john@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active'),
(@tenant1_id, 'Sarah Johnson', 'sarah@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
(@tenant1_id, 'Mike Davis', 'mike@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
(@tenant1_id, 'Emily Brown', 'emily@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'read_only', 'active');
SET @tenant1_admin_id = LAST_INSERT_ID() - 3;
SET @tenant1_user1_id = LAST_INSERT_ID() - 2;
SET @tenant1_user2_id = LAST_INSERT_ID() - 1;
SET @tenant1_user3_id = LAST_INSERT_ID();

-- Insert users for Creative Agency Inc (Tenant 2)
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `status`) VALUES
(@tenant2_id, 'Alice Williams', 'alice@creativeagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active'),
(@tenant2_id, 'Bob Martinez', 'bob@creativeagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
(@tenant2_id, 'Carol Garcia', 'carol@creativeagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active');
SET @tenant2_admin_id = LAST_INSERT_ID() - 2;
SET @tenant2_user1_id = LAST_INSERT_ID() - 1;
SET @tenant2_user2_id = LAST_INSERT_ID();

-- Insert folders for TechCorp Solutions (Tenant 1)
INSERT INTO `folders` (`tenant_id`, `parent_id`, `name`, `path`, `created_by`) VALUES
(@tenant1_id, NULL, 'Documents', '/Documents', @tenant1_admin_id),
(@tenant1_id, NULL, 'Projects', '/Projects', @tenant1_admin_id),
(@tenant1_id, NULL, 'Marketing', '/Marketing', @tenant1_admin_id);
SET @tenant1_folder1_id = LAST_INSERT_ID() - 2;
SET @tenant1_folder2_id = LAST_INSERT_ID() - 1;
SET @tenant1_folder3_id = LAST_INSERT_ID();

INSERT INTO `folders` (`tenant_id`, `parent_id`, `name`, `path`, `created_by`) VALUES
(@tenant1_id, @tenant1_folder2_id, 'Project Alpha', '/Projects/Project Alpha', @tenant1_user1_id),
(@tenant1_id, @tenant1_folder2_id, 'Project Beta', '/Projects/Project Beta', @tenant1_user2_id),
(@tenant1_id, @tenant1_folder1_id, 'Contracts', '/Documents/Contracts', @tenant1_admin_id);
SET @tenant1_folder4_id = LAST_INSERT_ID() - 2;
SET @tenant1_folder5_id = LAST_INSERT_ID() - 1;
SET @tenant1_folder6_id = LAST_INSERT_ID();

-- Insert folders for Creative Agency Inc (Tenant 2)
INSERT INTO `folders` (`tenant_id`, `parent_id`, `name`, `path`, `created_by`) VALUES
(@tenant2_id, NULL, 'Client Projects', '/Client Projects', @tenant2_admin_id),
(@tenant2_id, NULL, 'Brand Assets', '/Brand Assets', @tenant2_admin_id);
SET @tenant2_folder1_id = LAST_INSERT_ID() - 1;
SET @tenant2_folder2_id = LAST_INSERT_ID();

INSERT INTO `folders` (`tenant_id`, `parent_id`, `name`, `path`, `created_by`) VALUES
(@tenant2_id, @tenant2_folder1_id, 'Acme Corp', '/Client Projects/Acme Corp', @tenant2_user1_id),
(@tenant2_id, @tenant2_folder1_id, 'Global Industries', '/Client Projects/Global Industries', @tenant2_user2_id);

-- Insert files for TechCorp Solutions (Tenant 1)
INSERT INTO `files` (`tenant_id`, `folder_id`, `original_filename`, `stored_filename`, `file_path`, `mime_type`, `file_size`, `extension`, `uploaded_by`, `visibility`) VALUES
(@tenant1_id, @tenant1_folder1_id, 'Company_Overview.pdf', 'file_1_company_overview.pdf', '/storage/uploads/tenant_' + @tenant1_id + '/file_1_company_overview.pdf', 'application/pdf', 2457600, 'pdf', @tenant1_admin_id, 'tenant'),
(@tenant1_id, @tenant1_folder1_id, 'Employee_Handbook.pdf', 'file_2_employee_handbook.pdf', '/storage/uploads/tenant_' + @tenant1_id + '/file_2_employee_handbook.pdf', 'application/pdf', 5242880, 'pdf', @tenant1_admin_id, 'tenant'),
(@tenant1_id, @tenant1_folder6_id, 'Service_Agreement.docx', 'file_3_service_agreement.docx', '/storage/uploads/tenant_' + @tenant1_id + '/file_3_service_agreement.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 1048576, 'docx', @tenant1_admin_id, 'private'),
(@tenant1_id, @tenant1_folder4_id, 'Alpha_Requirements.docx', 'file_4_alpha_requirements.docx', '/storage/uploads/tenant_' + @tenant1_id + '/file_4_alpha_requirements.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 3145728, 'docx', @tenant1_user1_id, 'tenant'),
(@tenant1_id, @tenant1_folder4_id, 'Alpha_Design_Mockup.png', 'file_5_alpha_design_mockup.png', '/storage/uploads/tenant_' + @tenant1_id + '/file_5_alpha_design_mockup.png', 'image/png', 8388608, 'png', @tenant1_user1_id, 'tenant'),
(@tenant1_id, @tenant1_folder5_id, 'Beta_Timeline.xlsx', 'file_6_beta_timeline.xlsx', '/storage/uploads/tenant_' + @tenant1_id + '/file_6_beta_timeline.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 2097152, 'xlsx', @tenant1_user2_id, 'tenant'),
(@tenant1_id, @tenant1_folder3_id, 'Campaign_Plan_Q1.pptx', 'file_7_campaign_plan_q1.pptx', '/storage/uploads/tenant_' + @tenant1_id + '/file_7_campaign_plan_q1.pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 15728640, 'pptx', @tenant1_user1_id, 'shared_link');
SET @tenant1_file7_id = LAST_INSERT_ID();

-- Insert files for Creative Agency Inc (Tenant 2)
INSERT INTO `files` (`tenant_id`, `folder_id`, `original_filename`, `stored_filename`, `file_path`, `mime_type`, `file_size`, `extension`, `uploaded_by`, `visibility`) VALUES
(@tenant2_id, @tenant2_folder2_id, 'Logo_Primary.png', 'file_8_logo_primary.png', '/storage/uploads/tenant_' + @tenant2_id + '/file_8_logo_primary.png', 'image/png', 524288, 'png', @tenant2_admin_id, 'tenant'),
(@tenant2_id, @tenant2_folder2_id, 'Brand_Guidelines.pdf', 'file_9_brand_guidelines.pdf', '/storage/uploads/tenant_' + @tenant2_id + '/file_9_brand_guidelines.pdf', 'application/pdf', 10485760, 'pdf', @tenant2_admin_id, 'tenant'),
(@tenant2_id, LAST_INSERT_ID() - 3, 'Acme_Proposal.docx', 'file_10_acme_proposal.docx', '/storage/uploads/tenant_' + @tenant2_id + '/file_10_acme_proposal.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 4194304, 'docx', @tenant2_user1_id, 'private');
SET @tenant2_file10_id = LAST_INSERT_ID();

-- Insert shared links
INSERT INTO `file_shares` (`tenant_id`, `file_id`, `folder_id`, `token`, `share_type`, `permissions`, `expires_at`, `created_by`) VALUES
(@tenant1_id, @tenant1_file7_id, NULL, 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 'file', 'download', DATE_ADD(NOW(), INTERVAL 30 DAY), @tenant1_user1_id),
(@tenant2_id, @tenant2_file10_id, NULL, 'z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4', 'file', 'view', DATE_ADD(NOW(), INTERVAL 7 DAY), @tenant2_user1_id),
(@tenant1_id, NULL, @tenant1_folder3_id, 'q1w2e3r4t5y6u7i8o9p0a1s2d3f4g5h6', 'folder', 'download', NULL, @tenant1_admin_id);

-- Insert API keys for tenants
INSERT INTO `api_keys` (`tenant_id`, `key_name`, `api_key`, `is_active`, `created_by`) VALUES
(@tenant1_id, 'TechCorp API Key', 'sk_test_techcorp_1234567890abcdef1234567890abcdef12345678', 1, @tenant1_admin_id),
(@tenant2_id, 'Creative Agency API Key', 'sk_test_creative_9876543210fedcba9876543210fedcba98765432', 1, @tenant2_admin_id);

-- Insert activity logs
INSERT INTO `activity_logs` (`tenant_id`, `user_id`, `action_type`, `target_type`, `target_id`, `description`, `ip_address`) VALUES
(@tenant1_id, @tenant1_admin_id, 'user_login', NULL, NULL, 'User logged in', '192.168.1.100'),
(@tenant1_id, @tenant1_admin_id, 'folder_create', 'folder', @tenant1_folder1_id, 'Created folder: Documents', '192.168.1.100'),
(@tenant1_id, @tenant1_admin_id, 'folder_create', 'folder', @tenant1_folder2_id, 'Created folder: Projects', '192.168.1.100'),
(@tenant1_id, @tenant1_admin_id, 'file_upload', 'file', @tenant1_file7_id - 6, 'Uploaded file: Company_Overview.pdf', '192.168.1.100'),
(@tenant1_id, @tenant1_user1_id, 'user_login', NULL, NULL, 'User logged in', '192.168.1.101'),
(@tenant1_id, @tenant1_user1_id, 'folder_create', 'folder', @tenant1_folder4_id, 'Created folder: Project Alpha', '192.168.1.101'),
(@tenant1_id, @tenant1_user1_id, 'file_upload', 'file', @tenant1_file7_id - 3, 'Uploaded file: Alpha_Requirements.docx', '192.168.1.101'),
(@tenant1_id, @tenant1_user1_id, 'file_upload', 'file', @tenant1_file7_id - 2, 'Uploaded file: Alpha_Design_Mockup.png', '192.168.1.101'),
(@tenant1_id, @tenant1_user1_id, 'share_create', 'file', @tenant1_file7_id, 'Created share link for: Campaign_Plan_Q1.pptx', '192.168.1.101'),
(@tenant2_id, @tenant2_admin_id, 'user_login', NULL, NULL, 'User logged in', '10.0.0.50'),
(@tenant2_id, @tenant2_admin_id, 'folder_create', 'folder', @tenant2_folder1_id, 'Created folder: Client Projects', '10.0.0.50'),
(@tenant2_id, @tenant2_user1_id, 'file_upload', 'file', @tenant2_file10_id, 'Uploaded file: Acme_Proposal.docx', '10.0.0.51'),
(@tenant2_id, @tenant2_user1_id, 'share_create', 'file', @tenant2_file10_id, 'Created share link for: Acme_Proposal.docx', '10.0.0.51');

-- Insert invoices
INSERT INTO `invoices` (`tenant_id`, `subscription_id`, `invoice_number`, `amount`, `currency`, `status`, `due_date`, `paid_at`, `description`) VALUES
(@tenant1_id, 1, 'INV-2025-001', 49.99, 'USD', 'paid', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY), 'Professional Plan - Monthly'),
(@tenant1_id, 1, 'INV-2025-002', 49.99, 'USD', 'pending', DATE_ADD(NOW(), INTERVAL 15 DAY), NULL, 'Professional Plan - Monthly'),
(@tenant2_id, 2, 'INV-2025-003', 199.99, 'USD', 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), 'Starter Plan - Yearly');
SET @invoice1_id = LAST_INSERT_ID() - 2;
SET @invoice3_id = LAST_INSERT_ID();

-- Insert payments
INSERT INTO `payments` (`tenant_id`, `invoice_id`, `payment_method`, `transaction_id`, `amount`, `currency`, `status`, `paid_at`) VALUES
(@tenant1_id, @invoice1_id, 'credit_card', 'txn_1234567890abcdef', 49.99, 'USD', 'success', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(@tenant2_id, @invoice3_id, 'credit_card', 'txn_abcdef1234567890', 199.99, 'USD', 'success', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- Insert notifications
INSERT INTO `notifications` (`tenant_id`, `user_id`, `type`, `subject`, `message`, `recipient_email`, `is_sent`, `sent_at`) VALUES
(@tenant1_id, @tenant1_admin_id, 'welcome', 'Welcome to SplashDrive!', 'Thank you for joining SplashDrive. Your account has been created successfully.', 'john@techcorp.com', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@tenant1_id, @tenant1_admin_id, 'storage_warning', 'Storage limit warning - 80% used', 'Your organization has used 80% of available storage. Consider upgrading your plan.', 'john@techcorp.com', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@tenant2_id, @tenant2_admin_id, 'welcome', 'Welcome to SplashDrive!', 'Thank you for joining SplashDrive. Your account has been created successfully.', 'alice@creativeagency.com', 1, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(@tenant2_id, @tenant2_admin_id, 'trial_ending', 'Your trial is ending soon', 'Your 14-day trial will end in 3 days. Please add a payment method to continue using SplashDrive.', 'alice@creativeagency.com', 0, NULL);

-- ============================================================================
-- END OF DATABASE SCHEMA
-- ============================================================================
