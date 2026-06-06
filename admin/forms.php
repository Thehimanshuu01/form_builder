<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();

// Get all forms with submission count
$stmt = $pdo->query("
    SELECT f.*, COUNT(s.id) as submission_count 
    FROM forms f 
    LEFT JOIN submissions s ON f.id = s.form_id 
    GROUP BY f.id 
    ORDER BY f.created_at DESC
");
$forms = $stmt->fetchAll();

$pageTitle = 'Forms';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-file-earmark-text me-2"></i>All Forms</h4>
    <a href="create-form.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Create New Form
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($forms)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-x" style="font-size: 4rem;"></i>
                <p class="mt-3 h5">No forms created yet</p>
                <p>Create your first form to get started</p>
                <a href="create-form.php" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-2"></i>Create Form
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Form Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Submissions</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): ?>
                            <tr>
                                <td>#<?php echo $form['id']; ?></td>
                                <td>
                                    <strong><?php echo sanitize($form['name']); ?></strong>
                                    <?php if ($form['description']): ?>
                                        <br><small class="text-muted"><?php echo sanitize(substr($form['description'], 0, 50)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo sanitize($form['slug']); ?></code></td>
                                <td>
                                    <?php if ($form['status'] === 'published'): ?>
                                        <span class="badge bg-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $form['submission_count']; ?></span>
                                </td>
                                <td><?php echo formatDate($form['created_at']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($form['status'] === 'published'): ?>
                                            <a href="<?php echo APP_URL; ?>/form.php?slug=<?php echo $form['slug']; ?>" 
                                               class="btn btn-outline-success" target="_blank" title="View Form">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="edit-form.php?id=<?php echo $form['id']; ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="submissions.php?form_id=<?php echo $form['id']; ?>" 
                                           class="btn btn-outline-info" title="Submissions">
                                            <i class="bi bi-inbox"></i>
                                        </a>
                                        <a href="delete-form.php?id=<?php echo $form['id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this form? All submissions will also be deleted.')"
                                           title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
