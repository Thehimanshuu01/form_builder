-- =============================================
-- Add New Admin User Script
-- Dynamic PHP Form Builder
-- =============================================

-- INSTRUCTIONS:
-- 1. Generate a password hash first using PHP:
--    <?php echo password_hash('YourPassword', PASSWORD_DEFAULT); ?>
-- 2. Replace the hash below with your generated hash
-- 3. Update username and email as needed
-- 4. Run this SQL script

USE form_builder;

-- =============================================
-- Example: Add new admin user
-- Default password: admin123
-- =============================================
INSERT INTO admins (username, email, password) VALUES 
('newadmin', 'newadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- =============================================
-- To change existing admin password:
-- =============================================
-- UPDATE admins 
-- SET password = '$2y$10$YourNewHashHere' 
-- WHERE username = 'admin';

-- =============================================
-- To view all admins:
-- =============================================
-- SELECT id, username, email, created_at FROM admins;

-- =============================================
-- To delete an admin:
-- =============================================
-- DELETE FROM admins WHERE username = 'unwanted_admin';

-- =============================================
-- Password Hash Generator (PHP)
-- Save this as generate-hash.php and run it:
-- =============================================
-- <?php
-- if (isset($_POST['password'])) {
--     echo password_hash($_POST['password'], PASSWORD_DEFAULT);
-- }
-- ?>
-- <form method="POST">
--     <input type="text" name="password" placeholder="Enter password">
--     <button type="submit">Generate Hash</button>
-- </form>
