<?php
require_once 'includes/config.php';

// Check if setup is already completed
if (!needsSetup()) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';
$success = '';

// Handle setup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = t('auth_error_password_match');
    } else {
        try {
            // Create database tables
            if (!createDatabaseTables($pdo)) {
                throw new Exception('Failed to create database tables');
            }
            
            // Create admin user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
            $stmt->execute([$username, $hashedPassword]);
            $adminId = $pdo->lastInsertId();
            
            // Add example chores
            addExampleChores($pdo, $adminId);
            
            // Set success message
            $success = t('setup_complete');
            
            // Redirect to login after 2 seconds
            header("refresh:2;url=" . url('login.php'));
        } catch (Exception $e) {
            error_log("Setup error: " . $e->getMessage());
            $error = t('setup_error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('setup_title'); ?> - <?php echo t('nav_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h1 class="text-center mb-4"><?php echo t('setup_title'); ?></h1>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php else: ?>
                            <p class="text-center mb-4"><?php echo t('setup_welcome'); ?></p>
                            
                            <form method="post" action="">
                                <h5 class="mb-3"><?php echo t('setup_admin_account'); ?></h5>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label"><?php echo t('auth_username'); ?></label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?php echo t('auth_password'); ?></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label"><?php echo t('auth_confirm_password'); ?></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><?php echo t('btn_confirm'); ?></button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
