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
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Delete chore history first (due to foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM chore_history WHERE chore_id = ?");
    $stmt->execute([$choreId]);
    
    // Delete the chore
    $stmt = $pdo->prepare("DELETE FROM chores WHERE id = ?");
    $stmt->execute([$choreId]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => t('msg_chore_deleted')
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error deleting chore: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
