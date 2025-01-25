<?php
// Base directory of the application
define('PROJECT_ROOT', dirname(__DIR__));
define('BASE_DIR', PROJECT_ROOT);

// Database directory and file
define('DB_DIR', PROJECT_ROOT . '/db');
define('DB_FILE', DB_DIR . '/chores.db');

// URL helper function
function url($path = '') {
    $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', PROJECT_ROOT);
    return $basePath . '/' . ltrim($path, '/');
}
