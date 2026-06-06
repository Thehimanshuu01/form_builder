<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();
    
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'draft');
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Form name is required';
    }
    
    if (empty($errors)) {
        // Generate unique slug
        $slug = generateUniqueSlug($name);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO forms (name, description, slug, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $slug, $status]);
            
            $formId = $pdo->lastInsertId();
            
            setFlashMessage('success', 'Form created successfully!');
            redirect(ADMIN_URL . '/edit-form.php?id=' . $formId);
        } catch (PDOException $e) {
            error_log("Form creation error: " . $e->getMessage());
            $errors[] = 'Failed to create form. Please try again.';
        }
    }
}

$pageTitle = 'Create Form';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Form</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Form Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $_POST['name'] ?? ''; ?>" required>
                        <div class="form-text">This will be displayed as the form title</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3"><?php echo $_POST['description'] ?? ''; ?></textarea>
                        <div class="form-text">Optional form description</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?php echo (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                        <div class="form-text">Draft forms are not publicly accessible</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="forms.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Next step:</strong> After creating the form, you'll be able to add fields to it.
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
