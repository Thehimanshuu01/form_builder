<?php
require_once 'config/config.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    die('Form not found');
}

$pdo = getDBConnection();

// Get form details
$stmt = $pdo->prepare("SELECT * FROM forms WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$form = $stmt->fetch();

if (!$form) {
    die('Form not found or not published');
}

// Get form fields
$stmt = $pdo->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY display_order ASC");
$stmt->execute([$form['id']]);
$fields = $stmt->fetchAll();

if (empty($fields)) {
    die('This form has no fields');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($form['name']); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .form-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
        }
        .required-indicator {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container form-container">
        <div class="card form-card">
            <div class="form-header">
                <h2 class="mb-2"><?php echo sanitize($form['name']); ?></h2>
                <?php if ($form['description']): ?>
                    <p class="mb-0 opacity-75"><?php echo sanitize($form['description']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-4">
                <form method="POST" action="submit.php" id="dynamicForm" enctype="multipart/form-data" novalidate>
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="form_id" value="<?php echo $form['id']; ?>">
                    
                    <?php foreach ($fields as $field): ?>
                        <div class="mb-4">
                            <label for="field_<?php echo $field['id']; ?>" class="form-label">
                                <?php echo sanitize($field['label']); ?>
                                <?php if ($field['required']): ?>
                                    <span class="required-indicator">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php
                            $fieldName = $field['field_name'];
                            $fieldId = 'field_' . $field['id'];
                            $required = $field['required'] ? 'required' : '';
                            $placeholder = $field['placeholder'] ? 'placeholder="' . sanitize($field['placeholder']) . '"' : '';
                            
                            switch ($field['field_type']):
                                case 'text':
                                case 'email':
                                case 'number':
                                    $type = $field['field_type'];
                                    echo "<input type=\"$type\" class=\"form-control\" id=\"$fieldId\" name=\"$fieldName\" $placeholder $required>";
                                    break;
                                    
                                case 'textarea':
                                    echo "<textarea class=\"form-control\" id=\"$fieldId\" name=\"$fieldName\" rows=\"4\" $placeholder $required></textarea>";
                                    break;
                                    
                                case 'dropdown':
                                    $options = json_decode($field['options_json'], true) ?? [];
                                    echo "<select class=\"form-select\" id=\"$fieldId\" name=\"$fieldName\" $required>";
                                    echo "<option value=\"\">-- Select an option --</option>";
                                    foreach ($options as $option) {
                                        echo "<option value=\"" . sanitize($option) . "\">" . sanitize($option) . "</option>";
                                    }
                                    echo "</select>";
                                    break;
                                    
                                case 'radio':
                                    $options = json_decode($field['options_json'], true) ?? [];
                                    foreach ($options as $index => $option) {
                                        $radioId = $fieldId . '_' . $index;
                                        echo "<div class=\"form-check\">";
                                        echo "<input class=\"form-check-input\" type=\"radio\" id=\"$radioId\" name=\"$fieldName\" value=\"" . sanitize($option) . "\" $required>";
                                        echo "<label class=\"form-check-label\" for=\"$radioId\">" . sanitize($option) . "</label>";
                                        echo "</div>";
                                    }
                                    break;
                                    
                                case 'checkbox':
                                    $options = json_decode($field['options_json'], true) ?? [];
                                    foreach ($options as $index => $option) {
                                        $checkId = $fieldId . '_' . $index;
                                        echo "<div class=\"form-check\">";
                                        echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"$checkId\" name=\"{$fieldName}[]\" value=\"" . sanitize($option) . "\">";
                                        echo "<label class=\"form-check-label\" for=\"$checkId\">" . sanitize($option) . "</label>";
                                        echo "</div>";
                                    }
                                    break;
                                    
                                case 'file':
                                    echo "<input type=\"file\" class=\"form-control\" id=\"$fieldId\" name=\"$fieldName\" $required>";
                                    echo "<small class=\"form-text text-muted\">Max size: 5MB. Allowed: PDF, JPG, PNG, DOC, DOCX</small>";
                                    break;
                            endswitch;
                            ?>
                            
                            <?php if ($field['help_text']): ?>
                                <div class="form-text"><?php echo sanitize($field['help_text']); ?></div>
                            <?php endif; ?>
                            
                            <div class="invalid-feedback"></div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>Submit Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <small class="text-white">Powered by <?php echo APP_NAME; ?></small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.getElementById('dynamicForm').addEventListener('submit', function(e) {
            let isValid = true;
            const form = this;
            
            // Clear previous validation
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validate required fields
            form.querySelectorAll('[required]').forEach(field => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    const name = field.name.replace('[]', '');
                    const group = form.querySelectorAll(`[name="${field.name}"]`);
                    const checked = Array.from(group).some(cb => cb.checked);
                    
                    if (!checked) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        const feedback = field.closest('.mb-4').querySelector('.invalid-feedback');
                        if (feedback) feedback.textContent = 'This field is required';
                    }
                } else if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    const feedback = field.closest('.mb-4').querySelector('.invalid-feedback');
                    if (feedback) feedback.textContent = 'This field is required';
                }
            });
            
            // Validate email fields
            form.querySelectorAll('[type="email"]').forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    const feedback = field.closest('.mb-4').querySelector('.invalid-feedback');
                    if (feedback) feedback.textContent = 'Please enter a valid email address';
                }
            });
            
            // Validate file uploads
            form.querySelectorAll('[type="file"]').forEach(field => {
                if (field.files.length > 0) {
                    const file = field.files[0];
                    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                    const extension = file.name.split('.').pop().toLowerCase();
                    
                    if (!allowedExtensions.includes(extension)) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        const feedback = field.closest('.mb-4').querySelector('.invalid-feedback');
                        if (feedback) feedback.textContent = 'Invalid file type';
                    }
                    
                    if (file.size > 5242880) { // 5MB
                        isValid = false;
                        field.classList.add('is-invalid');
                        const feedback = field.closest('.mb-4').querySelector('.invalid-feedback');
                        if (feedback) feedback.textContent = 'File size exceeds 5MB';
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
    </script>
</body>
</html>
