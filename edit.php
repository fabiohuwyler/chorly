<?php
require_once 'includes/config.php';

requireAuth();

$error = '';
$success = '';
$chore = null;

// Get chore ID from URL
$choreId = $_GET['id'] ?? null;

if (!$choreId) {
    header('Location: ' . url('index.php'));
    exit;
}

// Get chore details
try {
    $stmt = $pdo->prepare("SELECT * FROM chores WHERE id = ?");
    $stmt->execute([$choreId]);
    $chore = $stmt->fetch();
    
    if (!$chore) {
        header('Location: ' . url('index.php'));
        exit;
    }
} catch (PDOException $e) {
    error_log("Error getting chore: " . $e->getMessage());
    header('Location: ' . url('index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $estimatedDuration = $_POST['estimated_duration'] ?? null;
    $dueDate = $_POST['due_date'] ?? null;
    $isRecurring = isset($_POST['is_recurring']);
    $recurringInterval = $isRecurring ? ($_POST['recurring_interval'] ?? null) : null;
    
    if (empty($title)) {
        $error = t('msg_title_required');
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE chores 
                SET title = ?, description = ?, estimated_duration = ?,
                    next_due_date = ?, is_recurring = ?, recurring_interval = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title,
                $description,
                $estimatedDuration,
                $dueDate,
                $isRecurring,
                $recurringInterval,
                $choreId
            ]);
            
            $success = t('msg_chore_updated');
            
            // Refresh chore data
            $stmt = $pdo->prepare("SELECT * FROM chores WHERE id = ?");
            $stmt->execute([$choreId]);
            $chore = $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error updating chore: " . $e->getMessage());
            $error = t('msg_error');
        }
    }
}

$pageTitle = t('chore_edit');
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4"><?php echo t('chore_edit'); ?></h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label"><?php echo t('chore_title'); ?></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($chore['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label"><?php echo t('chore_description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                    ><?php echo htmlspecialchars($chore['description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estimated_duration" class="form-label"><?php echo t('chore_duration'); ?></label>
                            <input type="number" class="form-control" id="estimated_duration" name="estimated_duration" 
                                   value="<?php echo $chore['estimated_duration']; ?>" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="due_date" class="form-label"><?php echo t('chore_due_date'); ?></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   value="<?php echo $chore['next_due_date']; ?>"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring"
                                       <?php echo $chore['is_recurring'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_recurring">
                                    <?php echo t('chore_recurring'); ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4" id="interval_container" style="display: <?php echo $chore['is_recurring'] ? 'block' : 'none'; ?>;">
                            <label for="recurring_interval" class="form-label"><?php echo t('chore_recurring_interval'); ?></label>
                            <input type="number" class="form-control" id="recurring_interval" name="recurring_interval" 
                                   value="<?php echo $chore['recurring_interval']; ?>" min="1">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><?php echo t('btn_save'); ?></button>
                            <a href="<?php echo url('index.php'); ?>" class="btn btn-outline-secondary"><?php echo t('btn_cancel'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
