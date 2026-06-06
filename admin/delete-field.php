<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$fieldId = intval($_GET['id'] ?? 0);
$formId = intval($_GET['form_id'] ?? 0);

if ($fieldId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM fields WHERE id = ?");
        $stmt->execute([$fieldId]);
        
        setFlashMessage('success', 'Field deleted successfully');
    } catch (PDOException $e) {
        error_log("Field deletion error: " . $e->getMessage());
        setFlashMessage('danger', 'Failed to delete field');
    }
}

redirect(ADMIN_URL . '/edit-form.php?id=' . $formId);
