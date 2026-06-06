<?php
/**
 * Helper Functions
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Generate a URL-friendly slug
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Check if slug is unique
 */
function isSlugUnique($slug, $excludeId = null) {
    $pdo = getDBConnection();
    
    if ($excludeId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM forms WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $excludeId]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM forms WHERE slug = ?");
        $stmt->execute([$slug]);
    }
    
    return $stmt->fetchColumn() == 0;
}

/**
 * Generate unique slug
 */
function generateUniqueSlug($string, $excludeId = null) {
    $slug = generateSlug($string);
    $originalSlug = $slug;
    $counter = 1;
    
    while (!isSlugUnique($slug, $excludeId)) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('M d, Y h:i A', strtotime($date));
}

/**
 * Upload file securely
 */
function uploadFile($file) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error occurred'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit'];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)];
    }
    
    // Generate unique filename
    $newFilename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $newFilename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $newFilename, 'path' => $uploadPath];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Display flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Generate field name from label
 */
function generateFieldName($label) {
    $name = strtolower(trim($label));
    $name = preg_replace('/[^a-z0-9_]/', '_', $name);
    $name = preg_replace('/_+/', '_', $name);
    return trim($name, '_');
}
