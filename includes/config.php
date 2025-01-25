<?php
// Start session before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/error.log');

// Set default timezone
date_default_timezone_set('Europe/Zurich');

// Include path configuration first
require_once __DIR__ . '/path_config.php';

// Include language file
require_once __DIR__ . '/language.php';

// Include functions file (which has the addExampleChores function)
require_once __DIR__ . '/functions.php';

// Define database configuration
if (!isset($dbPath)) {
    $dbPath = PROJECT_ROOT . '/db/chores.db';
    error_log("Database path defined in config.php: " . $dbPath);
}
if (!isset($dsn)) {
    $dsn = 'sqlite:' . $dbPath;
    error_log("DSN defined in config.php: " . $dsn);
}

// Ensure database directory exists with proper permissions
$dbDir = dirname($dbPath);
if (!file_exists($dbDir)) {
    if (!@mkdir($dbDir, 0777, true)) {
        error_log("Failed to create database directory at: " . $dbDir);
    } else {
        error_log("Created database directory at: " . $dbDir);
        @chmod($dbDir, 0777);
    }
}

// Initialize database connection
try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("Database connection established");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}
