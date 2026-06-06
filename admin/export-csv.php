<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$formId = intval($_GET['form_id'] ?? 0);

if (!$formId) {
    die('Form ID is required');
}

// Get form details
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$formId]);
$form = $stmt->fetch();

if (!$form) {
    die('Form not found');
}

// Get form fields
$stmt = $pdo->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY display_order ASC");
$stmt->execute([$formId]);
$fields = $stmt->fetchAll();

// Get all submissions for this form
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE form_id = ? ORDER BY submitted_at DESC");
$stmt->execute([$formId]);
$submissions = $stmt->fetchAll();

// Prepare CSV headers
$headers = ['Submission ID', 'IP Address', 'Submitted At'];
foreach ($fields as $field) {
    $headers[] = $field['label'];
}

// Set CSV headers
$filename = 'form_' . $form['slug'] . '_submissions_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
fputcsv($output, $headers);

// Write data rows
foreach ($submissions as $submission) {
    $row = [
        $submission['id'],
        $submission['ip_address'],
        $submission['submitted_at']
    ];
    
    // Get submission values for this submission
    $stmt = $pdo->prepare("
        SELECT sv.*, f.field_type 
        FROM submission_values sv 
        JOIN fields f ON sv.field_id = f.id 
        WHERE sv.submission_id = ? 
        ORDER BY f.display_order ASC
    ");
    $stmt->execute([$submission['id']]);
    $values = $stmt->fetchAll();
    
    // Create a map of field_id => value
    $valueMap = [];
    foreach ($values as $value) {
        if ($value['field_type'] === 'checkbox') {
            $checkboxValues = json_decode($value['field_value'], true);
            $valueMap[$value['field_id']] = !empty($checkboxValues) ? implode(', ', $checkboxValues) : '';
        } else {
            $valueMap[$value['field_id']] = $value['field_value'];
        }
    }
    
    // Add field values in correct order
    foreach ($fields as $field) {
        $row[] = $valueMap[$field['id']] ?? '';
    }
    
    fputcsv($output, $row);
}

fclose($output);
exit;
