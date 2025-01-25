<?php
require_once 'includes/config.php';

// Check if setup is needed
if (needsSetup()) {
    header('Location: ' . url('setup.php'));
    exit;
}

requireAuth();

// Get chores grouped by urgency
try {
    $chores = getChoresByUrgency($pdo);
} catch (PDOException $e) {
    error_log("Error getting chores: " . $e->getMessage());
    $chores = [
        'overdue' => [],
        'today' => [],
        'upcoming' => []
    ];
}

// Calculate completion stats
try {
    $stats = calculateCompletionStats($pdo);
} catch (PDOException $e) {
    error_log("Error calculating stats: " . $e->getMessage());
    $stats = [
        'total_completed' => 0,
        'completed_on_time' => 0,
        'active_days' => 0,
        'streak' => 0
    ];
}

// Calculate today's progress
try {
    $todayProgress = calculateTodayProgress($pdo);
} catch (PDOException $e) {
    error_log("Error calculating today's progress: " . $e->getMessage());
    $todayProgress = [
        'completed' => 0,
        'total' => 0
    ];
}

$pageTitle = t('nav_home');
include 'includes/header.php';
?>

<div class="container">
    <!-- Stats Overview -->
    <div class="stats-overview">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo t('stats_30_day_overview'); ?></h5>
                        <div class="stats-item">
                            <div class="stats-info">
                                <div class="stats-label"><?php echo t('stats_on_time_completion'); ?></div>
                                <div class="stats-value">
                                    <?php 
                                    $percentage = $stats['total_completed'] > 0 
                                        ? round(($stats['completed_on_time'] / $stats['total_completed']) * 100) 
                                        : 0;
                                    echo $percentage . '%';
                                    ?>
                                </div>
                                <div class="stats-subtitle">
                                    <?php echo sprintf(t('stats_tasks_completed_on_time'), 
                                        $stats['completed_on_time'], 
                                        $stats['total_completed']); ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($stats['streak'] > 0): ?>
                            <div class="stats-item">
                                <div class="stats-info">
                                    <div class="stats-label"><?php echo t('stats_current_streak'); ?></div>
                                    <div class="stats-value">
                                        <?php echo $stats['streak']; ?> <?php echo t('stats_days'); ?>
                                    </div>
                                    <div class="stats-subtitle"><?php echo t('stats_completing_on_time'); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo t('stats_todays_tasks'); ?></h5>
                        <div class="stats-item">
                            <div class="stats-info">
                                <div class="stats-label"><?php echo t('stats_progress'); ?></div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo ($todayProgress['total'] > 0) 
                                            ? ($todayProgress['completed'] / $todayProgress['total'] * 100) 
                                            : 0; ?>%">
                                    </div>
                                </div>
                                <div class="stats-subtitle">
                                    <?php echo sprintf(t('stats_tasks_completed'), 
                                        $todayProgress['completed'], 
                                        $todayProgress['total']); ?>
                                </div>
                            </div>
                        </div>
                        <?php if (count($chores['overdue']) > 0): ?>
                            <div class="stats-item">
                                <div class="stats-info">
                                    <div class="stats-label"><?php echo t('stats_overdue_tasks'); ?></div>
                                    <div class="stats-value text-danger">
                                        <?php echo count($chores['overdue']); ?>
                                    </div>
                                    <div class="stats-subtitle"><?php echo t('stats_tasks_need_attention'); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions my-4">
        <div class="row g-3">
            <div class="col-4">
                <a href="<?php echo url('add.php'); ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-plus-circle"></i>
                    <span><?php echo t('nav_add'); ?></span>
                </a>
            </div>
            <div class="col-4">
                <a href="<?php echo url('random.php'); ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-shuffle"></i>
                    <span><?php echo t('nav_random'); ?></span>
                </a>
            </div>
            <div class="col-4">
                <a href="<?php echo url('history.php'); ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-clock-history"></i>
                    <span><?php echo t('nav_history'); ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Chores List -->
    <div class="chores-list">
        <?php foreach ($chores as $urgency => $urgencyChores): ?>
            <?php if (!empty($urgencyChores)): ?>
                <div class="urgency-group">
                    <div class="urgency-label">
                        <?php 
                        $icon = '';
                        switch($urgency) {
                            case 'overdue':
                                $icon = '<i class="bi bi-exclamation-triangle text-danger"></i>';
                                break;
                            case 'today':
                                $icon = '<i class="bi bi-calendar-check text-warning"></i>';
                                break;
                            case 'upcoming':
                                $icon = '<i class="bi bi-calendar text-info"></i>';
                                break;
                        }
                        echo $icon . ' ' . t('urgency_' . $urgency);
                        ?>
                    </div>
                    <?php foreach ($urgencyChores as $chore): ?>
                        <div class="chore-card <?php echo $chore['in_progress'] ? 'in-progress' : ''; ?>" 
                             data-chore-id="<?php echo $chore['id']; ?>">
                            <div class="chore-header">
                                <h4><?php echo htmlspecialchars($chore['title']); ?></h4>
                                <?php if ($chore['in_progress']): ?>
                                    <span class="badge bg-info"><?php echo t('status_in_progress'); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($chore['description'])): ?>
                                <p class="chore-description">
                                    <?php echo nl2br(htmlspecialchars($chore['description'])); ?>
                                </p>
                            <?php endif; ?>
                            <div class="chore-meta">
                                <span>
                                    <i class="bi bi-clock"></i> 
                                    <?php echo $chore['estimated_duration']; ?> <?php echo t('time_minutes'); ?>
                                </span>
                                <span>
                                    <i class="bi bi-calendar"></i> 
                                    <?php echo formatDate($chore['next_due_date']); ?>
                                </span>
                            </div>
                            <div class="chore-actions">
                                <?php if (!$chore['in_progress']): ?>
                                    <button type="button" class="btn-icon btn-start" onclick="startChore(<?php echo $chore['id']; ?>)" 
                                            title="<?php echo t('chore_start'); ?>">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-icon btn-complete" onclick="completeChore(<?php echo $chore['id']; ?>)" 
                                            title="<?php echo t('chore_complete'); ?>">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo url('edit.php?id=' . $chore['id']); ?>" class="btn-icon btn-edit" 
                                   title="<?php echo t('chore_edit'); ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-icon btn-delete" onclick="deleteChore(<?php echo $chore['id']; ?>)" 
                                        title="<?php echo t('chore_delete'); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<div id="toast" class="toast" role="alert">
    <div class="toast-content">
        <i class="toast-icon bi"></i>
        <span class="toast-message"></span>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
