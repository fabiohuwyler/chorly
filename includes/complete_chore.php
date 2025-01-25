<?php
require_once 'path_config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'language.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $choreId = $data['chore_id'] ?? null;
    
    if (!$choreId) {
        throw new Exception('Invalid chore ID');
    }
    
    // Get chore and its history
    $stmt = $pdo->prepare("
        SELECT c.*, ch.id as history_id, ch.started_at
        FROM chores c
        LEFT JOIN chore_history ch ON c.id = ch.chore_id AND ch.completed_at IS NULL
        WHERE c.id = ?
    ");
    $stmt->execute([$choreId]);
    $result = $stmt->fetch();
    
    if (!$result || !$result['history_id']) {
        throw new Exception('Chore is not in progress');
    }
    
    // Calculate actual duration
    $startTime = strtotime($result['started_at']);
    $actualDuration = round((time() - $startTime) / 60); // Convert to minutes
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Complete the chore
    $stmt = $pdo->prepare("
        UPDATE chore_history
        SET completed_at = CURRENT_TIMESTAMP,
            actual_duration = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $actualDuration,
        $result['history_id']
    ]);
    
    // If chore is recurring, set next due date
    if ($result['is_recurring'] && $result['recurring_interval']) {
        $nextDueDate = date('Y-m-d', strtotime($result['next_due_date'] . ' + ' . $result['recurring_interval'] . ' days'));
        
        $stmt = $pdo->prepare("
            UPDATE chores
            SET next_due_date = ?
            WHERE id = ?
        ");
        $stmt->execute([$nextDueDate, $choreId]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => t('msg_chore_completed')
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error completing chore: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
