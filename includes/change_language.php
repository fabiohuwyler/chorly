<?php
require_once 'path_config.php';
require_once 'language.php';

$language = $_POST['language'] ?? null;
$redirect = $_POST['redirect'] ?? 'index.php';

if ($language && in_array($language, ['en', 'de'])) {
    $_SESSION['language'] = $language;
}

header('Location: ' . url($redirect));
exit;
