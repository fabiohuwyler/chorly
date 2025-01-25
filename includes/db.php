<?php
require_once 'path_config.php';

try {
    // Create database directory if it doesn't exist
    if (!file_exists(DB_DIR)) {
        mkdir(DB_DIR, 0755, true);
    }

    // Connect to SQLite database
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable foreign key support
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please check the error log for details.");
}
