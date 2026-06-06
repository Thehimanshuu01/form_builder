<?php
require_once '../config/config.php';
requireAuth();

$pdo = getDBConnection();
$formId = intval($_GET['id'] ?? 0);

if (!$formId) {
    redirect(ADMIN_URL . '/forms.php');
}

// Get form details
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$formId]);
$form = $stmt->fetch();

if (!$form) {
    setFlashMessage('danger', 'Form not found');
    redirect(ADMIN_URL . '/forms.php');
}

// Get form fields
$stmt = $pdo->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY display_order ASC");
$stmt->execute([$formId]);
$fields = $stmt->fetchAll();

$errors = [];

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_form'])) {
    verifyCSRF();
    
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'draft');
    
    if (empty($name)) {
        $errors[] = 'Form name is required';
    }
    
    if (empty($errors)) {
        $slug = generateUniqueSlug($name, $formId);
        
        try {
            $stmt = $pdo->prepare("UPDATE forms SET name = ?, description = ?, slug = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $slug, $status, $formId]);
            
            setFlashMessage('success', 'Form updated successfully!');
            redirect(ADMIN_URL . '/edit-form.php?id=' . $formId);
        } catch (PDOException $e) {
            error_log("Form update error: " . $e->getMessage());
            $errors[] = 'Failed to update form';
        }
    }
}

// Handle field addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {
    verifyCSRF();
    
    $label = sanitize($_POST['label'] ?? '');
    $fieldType = sanitize($_POST['field_type'] ?? 'text');
    $placeholder = sanitize($_POST['placeholder'] ?? '');
    $helpText = sanitize($_POST['help_text'] ?? '');
    $required = isset($_POST['required']) ? 1 : 0;
    
    // Validation rules
    $validationRules = [];
    if (!empty($_POST['min_length'])) {
        $validationRules['min_length'] = intval($_POST['min_length']);
    }
    if (!empty($_POST['max_length'])) {
        $validationRules['max_length'] = intval($_POST['max_length']);
    }
    if (!empty($_POST['min_number'])) {
        $validationRules['min_number'] = floatval($_POST['min_number']);
    }
    if (!empty($_POST['max_number'])) {
        $validationRules['max_number'] = floatval($_POST['max_number']);
    }
    
    // Options for dropdown, radio, checkbox
    $optionsJson = null;
    if (in_array($fieldType, ['dropdown', 'radio', 'checkbox']) && !empty($_POST['options'])) {
        $options = array_filter(array_map('trim', explode("\n", $_POST['options'])));
        $optionsJson = json_encode($options);
    }
    
    if (empty($label)) {
        $errors[] = 'Field label is required';
    }
    
    if (empty($errors)) {
        $fieldName = generateFieldName($label);
        
        // Get max display order
        $stmt = $pdo->prepare("SELECT MAX(display_order) as max_order FROM fields WHERE form_id = ?");
        $stmt->execute([$formId]);
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO fields (form_id, label, field_name, field_type, placeholder, help_text, 
                                   options_json, required, validation_rules, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $formId, 
                $label, 
                $fieldName, 
                $fieldType, 
                $placeholder, 
                $helpText, 
                $optionsJson,
                $required, 
                !empty($validationRules) ? json_encode($validationRules) : null,
                $maxOrder + 1
            ]);
            
            setFlashMessage('success', 'Field added successfully!');
            redirect(ADMIN_URL . '/edit-form.php?id=' . $formId);
        } catch (PDOException $e) {
            error_log("Field creation error: " . $e->getMessage());
            $errors[] = 'Failed to add field';
        }
    }
}

$pageTitle = 'Edit Form: ' . $form['name'];
include 'includes/header.php';
?>

<div class="row">
    <!-- Form Details -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Form Settings</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-sm">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="update_form" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Form Name</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo sanitize($form['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo sanitize($form['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" value="<?php echo sanitize($form['slug']); ?>" disabled>
                        <small class="text-muted">Auto-generated from name</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="draft" <?php echo $form['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $form['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Update Form
                    </button>
                </form>
                
                <?php if ($form['status'] === 'published'): ?>
                    <hr>
                    <div class="alert alert-success mb-0">
                        <strong><i class="bi bi-link-45deg"></i> Public URL:</strong>
                        <div class="input-group input-group-sm mt-2">
                            <input type="text" class="form-control" id="publicUrl" 
                                   value="<?php echo APP_URL; ?>/form.php?slug=<?php echo $form['slug']; ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyUrl()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Field Builder -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-plus-square me-2"></i>Add New Field</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="addFieldForm">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="add_field" value="1">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="label" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Type</label>
                            <select class="form-select" name="field_type" id="fieldType">
                                <option value="text">Text</option>
                                <option value="email">Email</option>
                                <option value="number">Number</option>
                                <option value="textarea">Textarea</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="file">File Upload</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placeholder</label>
                            <input type="text" class="form-control" name="placeholder">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Help Text</label>
                            <input type="text" class="form-control" name="help_text">
                        </div>
                    </div>
                    
                    <!-- Options for dropdown/radio/checkbox -->
                    <div class="mb-3 d-none" id="optionsContainer">
                        <label class="form-label">Options (one per line)</label>
                        <textarea class="form-control" name="options" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    </div>
                    
                    <!-- Validation Rules -->
                    <div class="row">
                        <div class="col-md-3 mb-3" id="minLengthContainer">
                            <label class="form-label">Min Length</label>
                            <input type="number" class="form-control" name="min_length" min="0">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="maxLengthContainer">
                            <label class="form-label">Max Length</label>
                            <input type="number" class="form-control" name="max_length" min="0">
                        </div>
                        
                        <div class="col-md-3 mb-3 d-none" id="minNumberContainer">
                            <label class="form-label">Min Value</label>
                            <input type="number" class="form-control" name="min_number" step="any">
                        </div>
                        
                        <div class="col-md-3 mb-3 d-none" id="maxNumberContainer">
                            <label class="form-label">Max Value</label>
                            <input type="number" class="form-control" name="max_number" step="any">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="required" id="required">
                                <label class="form-check-label" for="required">
                                    Required
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Add Field
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Existing Fields -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Form Fields (<?php echo count($fields); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($fields)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No fields added yet</p>
                        <p class="small">Add fields using the form above</p>
                    </div>
                <?php else: ?>
                    <div id="sortableFields" class="list-group">
                        <?php foreach ($fields as $field): ?>
                            <div class="list-group-item" data-field-id="<?php echo $field['id']; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-grip-vertical text-muted me-2" style="cursor: move;"></i>
                                            <h6 class="mb-1">
                                                <?php echo sanitize($field['label']); ?>
                                                <?php if ($field['required']): ?>
                                                    <span class="badge bg-danger">Required</span>
                                                <?php endif; ?>
                                            </h6>
                                        </div>
                                        <div class="small text-muted">
                                            <span class="badge bg-secondary"><?php echo strtoupper($field['field_type']); ?></span>
                                            <code class="ms-2"><?php echo $field['field_name']; ?></code>
                                            <?php if ($field['placeholder']): ?>
                                                <span class="ms-2">Placeholder: <?php echo sanitize($field['placeholder']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="delete-field.php?id=<?php echo $field['id']; ?>&form_id=<?php echo $formId; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete this field?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>Drag and drop fields to reorder them
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Field type change handler
document.getElementById('fieldType').addEventListener('change', function() {
    const type = this.value;
    const optionsContainer = document.getElementById('optionsContainer');
    const minLengthContainer = document.getElementById('minLengthContainer');
    const maxLengthContainer = document.getElementById('maxLengthContainer');
    const minNumberContainer = document.getElementById('minNumberContainer');
    const maxNumberContainer = document.getElementById('maxNumberContainer');
    
    // Show/hide options
    if (['dropdown', 'radio', 'checkbox'].includes(type)) {
        optionsContainer.classList.remove('d-none');
    } else {
        optionsContainer.classList.add('d-none');
    }
    
    // Show/hide number validations
    if (type === 'number') {
        minLengthContainer.classList.add('d-none');
        maxLengthContainer.classList.add('d-none');
        minNumberContainer.classList.remove('d-none');
        maxNumberContainer.classList.remove('d-none');
    } else {
        minLengthContainer.classList.remove('d-none');
        maxLengthContainer.classList.remove('d-none');
        minNumberContainer.classList.add('d-none');
        maxNumberContainer.classList.add('d-none');
    }
});

// Copy URL function
function copyUrl() {
    const input = document.getElementById('publicUrl');
    input.select();
    document.execCommand('copy');
    alert('URL copied to clipboard!');
}

// Sortable fields
<?php if (!empty($fields)): ?>
$(function() {
    $("#sortableFields").sortable({
        handle: ".bi-grip-vertical",
        placeholder: "sortable-placeholder",
        update: function(event, ui) {
            const order = [];
            $('#sortableFields .list-group-item').each(function(index) {
                order.push({
                    id: $(this).data('field-id'),
                    order: index + 1
                });
            });
            
            // Save order via AJAX
            $.ajax({
                url: '../ajax/save-field-order.php',
                method: 'POST',
                data: {
                    order: JSON.stringify(order),
                    csrf_token: '<?php echo generateCSRFToken(); ?>'
                },
                success: function(response) {
                    console.log('Order saved');
                },
                error: function() {
                    alert('Failed to save field order');
                }
            });
        }
    });
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
