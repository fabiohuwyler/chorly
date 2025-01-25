<?php
require_once 'includes/config.php';

requireAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $estimatedDuration = $_POST['estimated_duration'] ?? null;
    $dueDate = $_POST['due_date'] ?? null;
    $isRecurring = isset($_POST['is_recurring']);
    $recurringInterval = $isRecurring ? ($_POST['recurring_interval'] ?? null) : null;
    
    if (empty($title)) {
        $error = 'Title is required';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO chores (
                    title, description, estimated_duration, next_due_date,
                    is_recurring, recurring_interval, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title,
                $description,
                $estimatedDuration,
                $dueDate,
                $isRecurring,
                $recurringInterval,
                $_SESSION['user_id']
            ]);
            
            $success = t('msg_chore_added');
            
            // Redirect after 2 seconds
            header("refresh:2;url=" . url('index.php'));
        } catch (PDOException $e) {
            error_log("Error adding chore: " . $e->getMessage());
            $error = t('msg_error');
        }
    }
}

$pageTitle = t('chore_add');
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4"><?php echo t('chore_add'); ?></h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label"><?php echo t('chore_title'); ?></label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label"><?php echo t('chore_description'); ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="estimated_duration" class="form-label"><?php echo t('chore_duration'); ?></label>
                                <input type="number" class="form-control" id="estimated_duration" name="estimated_duration" min="1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="due_date" class="form-label"><?php echo t('chore_due_date'); ?></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring">
                                    <label class="form-check-label" for="is_recurring">
                                        <?php echo t('chore_recurring'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-4" id="interval_container" style="display: none;">
                                <label for="recurring_interval" class="form-label"><?php echo t('chore_recurring_interval'); ?></label>
                                <input type="number" class="form-control" id="recurring_interval" name="recurring_interval" min="1">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><?php echo t('btn_save'); ?></button>
                                <a href="<?php echo url('index.php'); ?>" class="btn btn-outline-secondary"><?php echo t('btn_cancel'); ?></a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
