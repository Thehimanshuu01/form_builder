-- =============================================
-- Dynamic Form Builder - Database Schema
-- MySQL 8+
-- =============================================

-- Create database
CREATE DATABASE IF NOT EXISTS form_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE form_builder;

-- =============================================
-- Table: admins
-- Purpose: Store admin user credentials
-- =============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: forms
-- Purpose: Store form metadata
-- =============================================
CREATE TABLE IF NOT EXISTS forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    slug VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: fields
-- Purpose: Store form fields configuration
-- =============================================
CREATE TABLE IF NOT EXISTS fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form_id INT UNSIGNED NOT NULL,
    label VARCHAR(255) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'email', 'number', 'textarea', 'dropdown', 'radio', 'checkbox', 'file') NOT NULL,
    placeholder VARCHAR(255),
    help_text TEXT,
    options_json JSON,
    required TINYINT(1) DEFAULT 0,
    validation_rules JSON,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    INDEX idx_form_id (form_id),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: submissions
-- Purpose: Store form submission metadata
-- =============================================
CREATE TABLE IF NOT EXISTS submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    INDEX idx_form_id (form_id),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: submission_values
-- Purpose: Store actual field values for submissions
-- =============================================
CREATE TABLE IF NOT EXISTS submission_values (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id INT UNSIGNED NOT NULL,
    field_id INT UNSIGNED NOT NULL,
    field_value TEXT,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE,
    INDEX idx_submission_id (submission_id),
    INDEX idx_field_id (field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insert Default Admin User
-- Username: admin
-- Password: admin123
-- NOTE: Change this password immediately after first login!
-- =============================================
-- To generate a new password hash, use generate-password-hash.php
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@formbuilder.com', '$2y$10$8K1p/a0dL3FKaFevjHIdHO9kfDCqF8/Mq8XYPt0p5lZ/Z.t.5qLXG');
-- Password: admin123 (hashed using PHP password_hash with PASSWORD_DEFAULT)

-- =============================================
-- Sample Form Data (Optional)
-- =============================================
INSERT INTO forms (name, description, slug, status) VALUES 
('Contact Form', 'Get in touch with us', 'contact-form', 'published');

SET @form_id = LAST_INSERT_ID();

INSERT INTO fields (form_id, label, field_name, field_type, placeholder, help_text, required, display_order) VALUES 
(@form_id, 'Full Name', 'full_name', 'text', 'Enter your full name', 'Please provide your complete name', 1, 1),
(@form_id, 'Email Address', 'email', 'email', 'your@email.com', 'We will never share your email', 1, 2),
(@form_id, 'Phone Number', 'phone', 'number', '1234567890', 'Optional contact number', 0, 3),
(@form_id, 'Message', 'message', 'textarea', 'Your message here...', 'Tell us what you need', 1, 4);

-- =============================================
-- End of Schema
-- =============================================
