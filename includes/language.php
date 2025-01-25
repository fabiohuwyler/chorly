<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Available languages
$availableLanguages = ['de', 'en'];

// Default language
$defaultLanguage = 'de';

// Get current language from session or set default
if (!isset($_SESSION['language']) || !in_array($_SESSION['language'], $availableLanguages)) {
    $_SESSION['language'] = $defaultLanguage;
}

// Load language file
$translations = require __DIR__ . '/languages/' . $_SESSION['language'] . '.php';

// Translation function
function t($key, ...$args) {
    global $translations;
    
    if (!isset($translations[$key])) {
        error_log("Missing translation for key: $key");
        return $key;
    }
    
    if (empty($args)) {
        return $translations[$key];
    }
    
    return vsprintf($translations[$key], $args);
}

// Get current language
function getCurrentLanguage() {
    return $_SESSION['language'];
}

// Switch language
function switchLanguage($lang) {
    global $availableLanguages;
    if (in_array($lang, $availableLanguages)) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}
