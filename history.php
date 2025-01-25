<?php
require_once 'includes/config.php';

requireAuth();

// Get page number from URL
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    // Get total count
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM chore_history ch
        JOIN chores c ON ch.chore_id = c.id
        WHERE ch.completed_at IS NOT NULL
    ");
    $totalCount = $stmt->fetch()['count'];
    $totalPages = ceil($totalCount / $perPage);
    
    // Get history entries
    $stmt = $pdo->prepare("
        SELECT 
            ch.*,
            c.title,
            c.description,
            c.estimated_duration,
            u.username
        FROM chore_history ch
        JOIN chores c ON ch.chore_id = c.id
        LEFT JOIN users u ON ch.user_id = u.id
        WHERE ch.completed_at IS NOT NULL
        ORDER BY ch.completed_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$perPage, $offset]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting history: " . $e->getMessage());
    $history = [];
    $totalPages = 1;
}

$pageTitle = t('nav_history');
include 'includes/header.php';
?>

<div class="container">
    <h2 class="mb-4"><?php echo t('history_title'); ?></h2>
    
    <?php if (empty($history)): ?>
        <div class="alert alert-info">
            <?php echo t('history_empty'); ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?php echo t('history_chore'); ?></th>
                        <th><?php echo t('history_completed_by'); ?></th>
                        <th><?php echo t('history_completed_at'); ?></th>
                        <th><?php echo t('history_duration'); ?></th>
                        <th><?php echo t('history_status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                        <?php
                        $completedDate = new DateTime($entry['completed_at']);
                        $dueDate = new DateTime($entry['due_date']);
                        $isOnTime = $completedDate <= $dueDate;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($entry['title']); ?></strong>
                                <?php if ($entry['description']): ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($entry['description']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($entry['username']); ?></td>
                            <td>
                                <?php echo formatDateTime($entry['completed_at']); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo t('history_due'); ?>: <?php echo formatDate($entry['due_date']); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo $entry['actual_duration']; ?> <?php echo t('time_minutes'); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo t('history_estimated'); ?>: 
                                    <?php echo $entry['estimated_duration']; ?> <?php echo t('time_minutes'); ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($isOnTime): ?>
                                    <span class="badge bg-success">
                                        <?php echo t('history_on_time'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <?php echo t('history_late'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <nav aria-label="<?php echo t('pagination'); ?>">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <?php echo t('pagination_previous'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                <?php echo t('pagination_next'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
