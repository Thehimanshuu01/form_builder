<?php
/**
 * Authentication Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/admin/login.php');
    }
}

/**
 * Login admin user
 */
function loginAdmin($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        
        return true;
    }
    
    return false;
}

/**
 * Logout admin user
 */
function logoutAdmin() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Get logged in admin info
 */
function getAdminInfo() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email']
        ];
    }
    return null;
}
