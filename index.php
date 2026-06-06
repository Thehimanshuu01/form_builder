<?php
require_once 'config/config.php';

$pdo = getDBConnection();

// Get published forms
$stmt = $pdo->query("SELECT * FROM forms WHERE status = 'published' ORDER BY created_at DESC");
$forms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .hero {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        .form-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transition: transform 0.3s;
            height: 100%;
        }
        .form-card:hover {
            transform: translateY(-5px);
        }
        .admin-link {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            text-decoration: none;
            color: #667eea;
            font-weight: bold;
            transition: all 0.3s;
        }
        .admin-link:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 30px rgba(0,0,0,0.4);
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1 class="display-3 fw-bold mb-3">
                <i class="bi bi-layers-fill"></i> <?php echo APP_NAME; ?>
            </h1>
            <p class="lead">Create, manage, and publish custom forms without writing code</p>
        </div>
        
        <?php if (empty($forms)): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card form-card text-center">
                        <div class="card-body p-5">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 5rem;"></i>
                            <h3 class="mt-4 mb-3">No Forms Available</h3>
                            <p class="text-muted mb-4">There are currently no published forms.</p>
                            <a href="admin/login.php" class="btn btn-primary">
                                <i class="bi bi-shield-lock me-2"></i>Login to Admin Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="text-center">
                        <h4 class="text-white mb-4">Available Forms</h4>
                    </div>
                </div>
                
                <?php foreach ($forms as $form): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card form-card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?php echo sanitize($form['name']); ?></h5>
                                    <span class="badge bg-success">Published</span>
                                </div>
                                <?php if ($form['description']): ?>
                                    <p class="card-text text-muted mb-4"><?php echo sanitize($form['description']); ?></p>
                                <?php endif; ?>
                                <a href="form.php?slug=<?php echo $form['slug']; ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-pencil-square me-2"></i>Fill Out Form
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-5">
            <p class="text-white small">
                <i class="bi bi-info-circle me-2"></i>
                Powered by <?php echo APP_NAME; ?> - A secure and flexible form builder
            </p>
        </div>
    </div>
    
    <a href="admin/login.php" class="admin-link">
        <i class="bi bi-shield-lock me-2"></i>Admin Login
    </a>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
