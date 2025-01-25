<?php
require_once 'includes/config.php';

// Ensure user is logged in
requireAuth();

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword)) {
        $error = t('profile_current_password_required');
    } elseif (empty($newPassword)) {
        $error = t('profile_new_password_required');
    } elseif ($newPassword !== $confirmPassword) {
        $error = t('profile_passwords_not_match');
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!password_verify($currentPassword, $user['password'])) {
            $error = t('profile_current_password_wrong');
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            $success = t('profile_password_updated');
        }
    }
}

// Get user statistics
try {
    // Total completed chores
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chore_history
        WHERE user_id = ? AND completed_at IS NOT NULL
    ");
    $stmt->execute([$userId]);
    $totalCompleted = $stmt->fetch()['count'];
    
    // Completed on time
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chore_history
        WHERE user_id = ? 
        AND completed_at IS NOT NULL
        AND completed_at <= due_date
    ");
    $stmt->execute([$userId]);
    $completedOnTime = $stmt->fetch()['count'];
    
    // Average completion time
    $stmt = $pdo->prepare("
        SELECT AVG(actual_duration) as avg_duration
        FROM chore_history
        WHERE user_id = ? AND completed_at IS NOT NULL
    ");
    $stmt->execute([$userId]);
    $avgDuration = round($stmt->fetch()['avg_duration'] ?? 0);
    
    // Calculate streak
    $stmt = $pdo->prepare("
        SELECT DATE(completed_at) as completion_date,
               DATE(due_date) as due_date
        FROM chore_history
        WHERE user_id = ? 
        AND completed_at IS NOT NULL
        ORDER BY completed_at DESC
    ");
    $stmt->execute([$userId]);
    $completions = $stmt->fetchAll();
    
    $streak = 0;
    $currentDate = new DateTime('today');
    $lastDate = null;
    
    foreach ($completions as $completion) {
        $completionDate = new DateTime($completion['completion_date']);
        $dueDate = new DateTime($completion['due_date']);
        
        if ($lastDate === null) {
            if ($currentDate->diff($completionDate)->days > 1) {
                break;
            }
            $lastDate = $completionDate;
        } else {
            if ($lastDate->diff($completionDate)->days > 1) {
                break;
            }
        }
        
        if ($completionDate <= $dueDate) {
            $streak++;
        }
        
        $lastDate = $completionDate;
    }
    
} catch (PDOException $e) {
    error_log("Error getting user stats: " . $e->getMessage());
    $totalCompleted = 0;
    $completedOnTime = 0;
    $avgDuration = 0;
    $streak = 0;
}

// Calculate on-time percentage
$onTimePercentage = $totalCompleted > 0 
    ? round(($completedOnTime / $totalCompleted) * 100) 
    : 0;

// Get today's progress
try {
    // Get total chores due today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM chores c
        LEFT JOIN chore_history ch ON c.id = ch.chore_id 
            AND DATE(ch.due_date) = DATE('now')
            AND ch.completed_at IS NULL
        WHERE (
            (c.next_due_date = DATE('now') AND ch.id IS NULL)
            OR DATE(ch.due_date) = DATE('now')
        )
        AND (ch.user_id = ? OR ch.user_id IS NULL)
    ");
    $stmt->execute([$userId]);
    $totalToday = $stmt->fetch()['total'];
    
    // Get completed chores for today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed
        FROM chore_history ch
        WHERE DATE(ch.completed_at) = DATE('now')
        AND ch.user_id = ?
    ");
    $stmt->execute([$userId]);
    $completedToday = $stmt->fetch()['completed'];
} catch (PDOException $e) {
    error_log("Error getting today's progress: " . $e->getMessage());
    $totalToday = 0;
    $completedToday = 0;
}

// Get user's name
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$username = $stmt->fetch()['username'];

// Include header
$pageTitle = t('nav_profile');
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1><?php echo t('nav_profile'); ?></h1>
    
    <!-- Statistics Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0"><?php echo t('profile_stats'); ?></h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="h2 mb-0"><?php echo $totalCompleted; ?></h3>
                            <p class="text-muted mb-0"><?php echo t('profile_total_completed'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="h2 mb-0"><?php echo $onTimePercentage; ?>%</h3>
                            <p class="text-muted mb-0"><?php echo t('profile_completed_on_time'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="h2 mb-0"><?php echo $streak; ?></h3>
                            <p class="text-muted mb-0"><?php echo t('stats_current_streak'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="h2 mb-0"><?php echo $completedToday; ?>/<?php echo $totalToday; ?></h3>
                            <p class="text-muted mb-0"><?php echo t('stats_todays_tasks'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Password Change Section -->
    <div class="card">
        <div class="card-header">
            <h2 class="h5 mb-0"><?php echo t('profile_change_password'); ?></h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="current_password" class="form-label"><?php echo t('profile_current_password'); ?></label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label"><?php echo t('profile_new_password'); ?></label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label"><?php echo t('profile_confirm_password'); ?></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo t('profile_update_password'); ?></button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
