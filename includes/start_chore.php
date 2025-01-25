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
    
    // Check if chore exists
    $stmt = $pdo->prepare("SELECT * FROM chores WHERE id = ?");
    $stmt->execute([$choreId]);
    $chore = $stmt->fetch();
    
    if (!$chore) {
        throw new Exception('Chore not found');
    }
    
    // Check if chore is already in progress
    $stmt = $pdo->prepare("
        SELECT * FROM chore_history 
        WHERE chore_id = ? AND completed_at IS NULL
    ");
    $stmt->execute([$choreId]);
    if ($stmt->fetch()) {
        throw new Exception('Chore is already in progress');
    }
    
    // Start the chore
    $stmt = $pdo->prepare("
        INSERT INTO chore_history (chore_id, user_id, due_date)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $choreId,
        $_SESSION['user_id'],
        $chore['next_due_date']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => t('msg_chore_started')
    ]);
} catch (Exception $e) {
    error_log("Error starting chore: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
