<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo t('nav_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url('index.php'); ?>"><?php echo t('nav_title'); ?></a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isLoggedIn()): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('index.php'); ?>"><?php echo t('nav_home'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('add.php'); ?>"><?php echo t('nav_add'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('random.php'); ?>"><?php echo t('nav_random'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('history.php'); ?>"><?php echo t('nav_history'); ?></a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo strtoupper(getCurrentLanguage()); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li>
                                    <form action="<?php echo url('includes/change_language.php'); ?>" method="post">
                                        <input type="hidden" name="language" value="en">
                                        <input type="hidden" name="redirect" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                                        <button type="submit" class="dropdown-item <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">
                                            English
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="<?php echo url('includes/change_language.php'); ?>" method="post">
                                        <input type="hidden" name="language" value="de">
                                        <input type="hidden" name="redirect" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                                        <button type="submit" class="dropdown-item <?php echo getCurrentLanguage() === 'de' ? 'active' : ''; ?>">
                                            Deutsch
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('profile.php'); ?>"><?php echo t('nav_profile'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('logout.php'); ?>"><?php echo t('nav_logout'); ?></a>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo strtoupper(getCurrentLanguage()); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li>
                                    <form action="<?php echo url('includes/change_language.php'); ?>" method="post">
                                        <input type="hidden" name="language" value="en">
                                        <input type="hidden" name="redirect" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                                        <button type="submit" class="dropdown-item <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">
                                            English
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="<?php echo url('includes/change_language.php'); ?>" method="post">
                                        <input type="hidden" name="language" value="de">
                                        <input type="hidden" name="redirect" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                                        <button type="submit" class="dropdown-item <?php echo getCurrentLanguage() === 'de' ? 'active' : ''; ?>">
                                            Deutsch
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
