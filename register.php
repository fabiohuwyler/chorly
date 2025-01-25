<?php
require_once 'includes/config.php';

// Redirect to setup if needed
if (needsSetup()) {
    header('Location: ' . url('setup.php'));
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = t('auth_error_register');
    } elseif ($password !== $confirmPassword) {
        $error = t('auth_error_password_match');
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()['count'] > 0) {
                $error = 'Username already exists';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashedPassword]);
                
                // Log in the user
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['role'] = 'user';
                
                header('Location: ' . url('index.php'));
                exit;
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = t('auth_error_register');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('auth_register'); ?> - <?php echo t('nav_title'); ?></title>
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
                        <h1 class="text-center mb-4"><?php echo t('auth_register'); ?></h1>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
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
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary"><?php echo t('auth_register'); ?></button>
                            </div>
                            
                            <p class="text-center mb-0">
                                <?php echo t('auth_have_account'); ?>
                                <a href="<?php echo url('login.php'); ?>"><?php echo t('auth_login'); ?></a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
