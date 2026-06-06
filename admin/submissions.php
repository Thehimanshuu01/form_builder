<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$formId = intval($_GET['form_id'] ?? 0);

// Build query based on filter
if ($formId) {
    $stmt = $pdo->prepare("
        SELECT s.*, f.name as form_name, f.slug as form_slug
        FROM submissions s 
        JOIN forms f ON s.form_id = f.id 
        WHERE s.form_id = ?
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$formId]);
    
    // Get form details for header
    $formStmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
    $formStmt->execute([$formId]);
    $formDetails = $formStmt->fetch();
} else {
    $stmt = $pdo->query("
        SELECT s.*, f.name as form_name, f.slug as form_slug
        FROM submissions s 
        JOIN forms f ON s.form_id = f.id 
        ORDER BY s.submitted_at DESC
    ");
}

$submissions = $stmt->fetchAll();

// Get all forms for filter dropdown
$formsStmt = $pdo->query("SELECT id, name FROM forms ORDER BY name ASC");
$allForms = $formsStmt->fetchAll();

$pageTitle = 'Submissions' . ($formId ? ' - ' . ($formDetails['name'] ?? '') : '');
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-inbox me-2"></i>Form Submissions</h4>
    <div>
        <?php if ($formId && isset($formDetails)): ?>
            <a href="export-csv.php?form_id=<?php echo $formId; ?>" class="btn btn-success me-2">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
        <?php endif; ?>
        <a href="forms.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Forms
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="form_id" class="form-label">Filter by Form</label>
                <select class="form-select" id="form_id" name="form_id" onchange="this.form.submit()">
                    <option value="">All Forms</option>
                    <?php foreach ($allForms as $formOption): ?>
                        <option value="<?php echo $formOption['id']; ?>" 
                                <?php echo $formId == $formOption['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($formOption['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <?php if ($formId): ?>
                    <a href="submissions.php" class="btn btn-outline-secondary w-100">Clear Filter</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($submissions)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 4rem;"></i>
                <p class="mt-3 h5">No submissions yet</p>
                <p>Submissions will appear here once users fill out your forms</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Form Name</th>
                            <th>IP Address</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>#<?php echo $submission['id']; ?></td>
                                <td>
                                    <strong><?php echo sanitize($submission['form_name']); ?></strong>
                                    <br><small class="text-muted">Slug: <?php echo sanitize($submission['form_slug']); ?></small>
                                </td>
                                <td><?php echo sanitize($submission['ip_address']); ?></td>
                                <td><?php echo formatDate($submission['submitted_at']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="view-submission.php?id=<?php echo $submission['id']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="delete-submission.php?id=<?php echo $submission['id']; ?><?php echo $formId ? '&form_id='.$formId : ''; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this submission?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="alert alert-info mb-0 mt-3">
                <i class="bi bi-info-circle me-2"></i>
                Total Submissions: <strong><?php echo count($submissions); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
