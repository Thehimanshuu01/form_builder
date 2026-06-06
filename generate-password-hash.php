<?php
/**
 * Password Hash Generator
 * Use this script to generate password hashes for admin users
 * 
 * SECURITY WARNING: Delete this file after use!
 */

$generatedHash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (empty($password)) {
        $error = 'Please enter a password';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $generatedHash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .hash-output {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4">🔐 Password Hash Generator</h3>
                        
                        <div class="warning">
                            <strong>⚠️ Security Warning:</strong>
                            <p class="mb-0 mt-2">Delete this file (<code>generate-password-hash.php</code>) immediately after generating your password hash. This file should never be accessible on a production server.</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="password" class="form-label">Enter Password</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="password" 
                                       name="password" 
                                       placeholder="YourSecurePassword123" 
                                       required>
                                <div class="form-text">Enter the password you want to hash. Minimum 6 characters.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Generate Hash
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($generatedHash): ?>
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Generated Hash:</h5>
                            <div class="hash-output mb-3">
                                <?php echo htmlspecialchars($generatedHash); ?>
                            </div>
                            
                            <button class="btn btn-success w-100 mb-3" onclick="copyHash()">
                                📋 Copy Hash to Clipboard
                            </button>
                            
                            <div class="alert alert-info">
                                <strong>Next Steps:</strong>
                                <ol class="mb-0 mt-2 small">
                                    <li>Copy the hash above</li>
                                    <li>Use it in SQL:
                                        <pre class="mt-2 mb-0 p-2 bg-white rounded"><code>INSERT INTO admins (username, email, password) 
VALUES ('username', 'email@example.com', 'PASTE_HASH_HERE');</code></pre>
                                    </li>
                                    <li>Or update existing:
                                        <pre class="mt-2 mb-0 p-2 bg-white rounded"><code>UPDATE admins 
SET password = 'PASTE_HASH_HERE' 
WHERE username = 'admin';</code></pre>
                                    </li>
                                </ol>
                            </div>
                            
                            <script>
                                function copyHash() {
                                    const hash = `<?php echo $generatedHash; ?>`;
                                    navigator.clipboard.writeText(hash).then(function() {
                                        alert('Hash copied to clipboard!');
                                    }).catch(function(err) {
                                        // Fallback for older browsers
                                        const textarea = document.createElement('textarea');
                                        textarea.value = hash;
                                        document.body.appendChild(textarea);
                                        textarea.select();
                                        document.execCommand('copy');
                                        document.body.removeChild(textarea);
                                        alert('Hash copied to clipboard!');
                                    });
                                }
                            </script>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <h6 class="mb-3">Common Password Examples</h6>
                            <div class="small text-muted">
                                <p class="mb-1">admin123 → Use for testing only</p>
                                <p class="mb-1">SecurePass2024! → Good for production</p>
                                <p class="mb-1">MyC0mpl3x@Pass → Strong password</p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="alert alert-danger mb-0">
                            <strong>🗑️ Remember to Delete This File!</strong>
                            <p class="mb-2 mt-2 small">After generating your hash, delete this file from your server:</p>
                            <code class="small">rm generate-password-hash.php</code>
                            <p class="mb-0 mt-2 small">Or on Windows:</p>
                            <code class="small">del generate-password-hash.php</code>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">Dynamic PHP Form Builder - Password Hash Generator</small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
