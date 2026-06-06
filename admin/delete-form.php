<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$formId = intval($_GET['id'] ?? 0);

if ($formId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
        $stmt->execute([$formId]);
        
        setFlashMessage('success', 'Form deleted successfully');
    } catch (PDOException $e) {
        error_log("Form deletion error: " . $e->getMessage());
        setFlashMessage('danger', 'Failed to delete form');
    }
}

redirect(ADMIN_URL . '/forms.php');
