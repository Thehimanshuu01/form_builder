<?php
require_once 'config/config.php';

// Set your desired password here
$newPassword = 'admin123';

try {
    $pdo = getDBConnection();
    
    // Generate proper hash
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashedPassword]);
    
    echo "✅ Success! Admin password has been updated.\n";
    echo "Username: admin\n";
    echo "Password: " . $newPassword . "\n";
    echo "\n⚠️  Please delete this file (fix-admin-password.php) after use!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPossible issues:\n";
    echo "1. Database connection failed - check config/database.php\n";
    echo "2. Admin user doesn't exist - run database.sql first\n";
    echo "3. Database 'form_builder' doesn't exist\n";
}
