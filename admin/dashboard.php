<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM forms");
$totalForms = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM forms WHERE status = 'published'");
$publishedForms = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM submissions");
$totalSubmissions = $stmt->fetch()['total'];

// Get recent submissions
$stmt = $pdo->query("
    SELECT s.*, f.name as form_name 
    FROM submissions s 
    JOIN forms f ON s.form_id = f.id 
    ORDER BY s.submitted_at DESC 
    LIMIT 5
");
$recentSubmissions = $stmt->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Forms</h6>
                        <h2 class="mb-0"><?php echo $totalForms; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Published Forms</h6>
                        <h2 class="mb-0"><?php echo $publishedForms; ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Submissions</h6>
                        <h2 class="mb-0"><?php echo $totalSubmissions; ?></h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="bi bi-envelope-paper text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Submissions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentSubmissions)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No submissions yet</p>
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
                                <?php foreach ($recentSubmissions as $submission): ?>
                                    <tr>
                                        <td>#<?php echo $submission['id']; ?></td>
                                        <td><?php echo sanitize($submission['form_name']); ?></td>
                                        <td><?php echo sanitize($submission['ip_address']); ?></td>
                                        <td><?php echo formatDate($submission['submitted_at']); ?></td>
                                        <td>
                                            <a href="view-submission.php?id=<?php echo $submission['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="submissions.php" class="btn btn-outline-primary">View All Submissions</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
