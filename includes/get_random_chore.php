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
    // Get duration preference if set
    $duration = isset($_GET['duration']) ? (int)$_GET['duration'] : null;
    
    // Build the query based on whether duration is specified
    $sql = "
        SELECT * FROM chores 
        WHERE next_due_date >= DATE('now')
        AND id NOT IN (
            SELECT chore_id FROM chore_history 
            WHERE completed_at IS NULL
        )
    ";
    
    $params = [];
    
    if ($duration) {
        $sql .= " AND estimated_duration <= :duration";
        $params[':duration'] = $duration;
    }
    
    $sql .= " ORDER BY RANDOM() LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $chore = $stmt->fetch();
    
    if ($chore) {
        echo json_encode([
            'success' => true,
            'chore' => $chore
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No chores available' . ($duration ? ' within ' . $duration . ' minutes' : '')
        ]);
    }
} catch (PDOException $e) {
    error_log("Error getting random chore: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => t('msg_error')
    ]);
}
