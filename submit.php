<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL);
}

verifyCSRF();

$formId = intval($_POST['form_id'] ?? 0);

if (!$formId) {
    die('Invalid form submission');
}

$pdo = getDBConnection();

// Get form details
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ? AND status = 'published'");
$stmt->execute([$formId]);
$form = $stmt->fetch();

if (!$form) {
    die('Form not found');
}

// Get form fields
$stmt = $pdo->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY display_order ASC");
$stmt->execute([$formId]);
$fields = $stmt->fetchAll();

// Validate submission
$errors = validateFormSubmission($fields, $_POST, $_FILES);

if (!empty($errors)) {
    $_SESSION['submission_errors'] = $errors;
    $_SESSION['submission_data'] = $_POST;
    redirect(APP_URL . '/form.php?slug=' . $form['slug'] . '&error=1');
}

try {
    $pdo->beginTransaction();
    
    // Create submission record
    $stmt = $pdo->prepare("INSERT INTO submissions (form_id, ip_address) VALUES (?, ?)");
    $stmt->execute([$formId, getClientIP()]);
    $submissionId = $pdo->lastInsertId();
    
    // Save field values
    $stmt = $pdo->prepare("INSERT INTO submission_values (submission_id, field_id, field_value) VALUES (?, ?, ?)");
    
    foreach ($fields as $field) {
        $fieldName = $field['field_name'];
        $fieldValue = '';
        
        if ($field['field_type'] === 'file') {
            // Handle file upload
            if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = uploadFile($_FILES[$fieldName]);
                
                if ($uploadResult['success']) {
                    $fieldValue = $uploadResult['filename'];
                } else {
                    throw new Exception($uploadResult['error']);
                }
            }
        } elseif ($field['field_type'] === 'checkbox') {
            // Handle checkbox (array)
            $fieldValue = isset($_POST[$fieldName]) ? json_encode($_POST[$fieldName]) : '[]';
        } else {
            // Handle regular fields
            $fieldValue = isset($_POST[$fieldName]) ? sanitize($_POST[$fieldName]) : '';
        }
        
        $stmt->execute([$submissionId, $field['id'], $fieldValue]);
    }
    
    $pdo->commit();
    
    // Redirect to success page
    redirect(APP_URL . '/success.php?form=' . $form['slug']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Submission error: " . $e->getMessage());
    die('An error occurred while submitting the form. Please try again.');
}
