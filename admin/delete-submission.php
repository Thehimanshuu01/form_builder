<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$submissionId = intval($_GET['id'] ?? 0);
$formId = intval($_GET['form_id'] ?? 0);

if ($submissionId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
        $stmt->execute([$submissionId]);
        
        setFlashMessage('success', 'Submission deleted successfully');
    } catch (PDOException $e) {
        error_log("Submission deletion error: " . $e->getMessage());
        setFlashMessage('danger', 'Failed to delete submission');
    }
}

$redirectUrl = $formId ? ADMIN_URL . '/submissions.php?form_id=' . $formId : ADMIN_URL . '/submissions.php';
redirect($redirectUrl);
