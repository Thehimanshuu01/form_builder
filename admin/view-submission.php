<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$submissionId = intval($_GET['id'] ?? 0);

if (!$submissionId) {
    redirect(ADMIN_URL . '/submissions.php');
}

// Get submission details
$stmt = $pdo->prepare("
    SELECT s.*, f.name as form_name, f.slug as form_slug 
    FROM submissions s 
    JOIN forms f ON s.form_id = f.id 
    WHERE s.id = ?
");
$stmt->execute([$submissionId]);
$submission = $stmt->fetch();

if (!$submission) {
    setFlashMessage('danger', 'Submission not found');
    redirect(ADMIN_URL . '/submissions.php');
}

// Get submission values with field details
$stmt = $pdo->prepare("
    SELECT sv.*, f.label, f.field_type, f.field_name 
    FROM submission_values sv 
    JOIN fields f ON sv.field_id = f.id 
    WHERE sv.submission_id = ? 
    ORDER BY f.display_order ASC
");
$stmt->execute([$submissionId]);
$values = $stmt->fetchAll();

$pageTitle = 'View Submission #' . $submissionId;
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-eye me-2"></i>Submission Details</h4>
    <div>
        <a href="delete-submission.php?id=<?php echo $submissionId; ?>" 
           class="btn btn-danger"
           onclick="return confirm('Are you sure you want to delete this submission?')">
            <i class="bi bi-trash me-2"></i>Delete Submission
        </a>
        <a href="submissions.php?form_id=<?php echo $submission['form_id']; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Submissions
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Submission Info</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th>Submission ID:</th>
                        <td>#<?php echo $submission['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Form Name:</th>
                        <td><?php echo sanitize($submission['form_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Form Slug:</th>
                        <td><code><?php echo sanitize($submission['form_slug']); ?></code></td>
                    </tr>
                    <tr>
                        <th>IP Address:</th>
                        <td><?php echo sanitize($submission['ip_address']); ?></td>
                    </tr>
                    <tr>
                        <th>Submitted At:</th>
                        <td><?php echo formatDate($submission['submitted_at']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Submitted Data</h5>
            </div>
            <div class="card-body">
                <?php if (empty($values)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No data submitted</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($values as $value): ?>
                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold text-muted small mb-1">
                                <?php echo sanitize($value['label']); ?>
                            </label>
                            <div class="submission-value">
                                <?php
                                if ($value['field_type'] === 'file') {
                                    // Handle file display
                                    if (!empty($value['field_value'])) {
                                        $filePath = '../uploads/' . $value['field_value'];
                                        if (file_exists($filePath)) {
                                            echo '<a href="../uploads/' . sanitize($value['field_value']) . '" target="_blank" class="btn btn-sm btn-outline-primary">';
                                            echo '<i class="bi bi-download me-2"></i>Download File';
                                            echo '</a>';
                                            echo '<p class="small text-muted mt-2 mb-0">Filename: ' . sanitize($value['field_value']) . '</p>';
                                        } else {
                                            echo '<span class="text-danger">File not found</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">No file uploaded</span>';
                                    }
                                } elseif ($value['field_type'] === 'checkbox') {
                                    // Handle checkbox display
                                    $checkboxValues = json_decode($value['field_value'], true);
                                    if (!empty($checkboxValues)) {
                                        echo '<ul class="mb-0">';
                                        foreach ($checkboxValues as $cbValue) {
                                            echo '<li>' . sanitize($cbValue) . '</li>';
                                        }
                                        echo '</ul>';
                                    } else {
                                        echo '<span class="text-muted">None selected</span>';
                                    }
                                } elseif ($value['field_type'] === 'textarea') {
                                    // Handle textarea with line breaks
                                    echo '<div class="p-3 bg-light rounded">' . nl2br(sanitize($value['field_value'])) . '</div>';
                                } else {
                                    // Handle regular text fields
                                    if (!empty($value['field_value'])) {
                                        echo '<p class="mb-0">' . sanitize($value['field_value']) . '</p>';
                                    } else {
                                        echo '<span class="text-muted">Not provided</span>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
