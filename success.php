<?php
require_once 'config/config.php';

$formSlug = sanitize($_GET['form'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card success-card">
                    <div class="card-body text-center p-5">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <h2 class="mt-4 mb-3">Thank You!</h2>
                        <p class="text-muted mb-4">Your form has been submitted successfully.</p>
                        <p class="text-muted">We have received your information and will get back to you soon.</p>
                        <?php if ($formSlug): ?>
                            <a href="form.php?slug=<?php echo $formSlug; ?>" class="btn btn-primary mt-3">
                                <i class="bi bi-arrow-left me-2"></i>Submit Another Response
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
