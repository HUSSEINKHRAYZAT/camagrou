<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Camagru'; ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body data-theme="light">
    <nav class="navbar">
        <div class="container nav-inner">
            <a href="index.php?page=home" class="logo">
                <img src="public/logo/logo.png" alt="Camagrou logo" class="logo-img">
            </a>
            <div class="nav-controls">
                <ul class="nav-links">
                    <li><a href="index.php?page=gallery">Gallery</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="index.php?page=profile">Profile</a></li>
                    <?php else: ?>
                        <li><a href="index.php?page=login">Login</a></li>
                        <li><a href="index.php?page=register">Register</a></li>
                    <?php endif; ?>
                </ul>
                <div class="theme-switcher" role="group" aria-label="Theme controls">
                    <label for="themeSelect">Theme</label>
                    <select id="themeSelect" class="theme-select">
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                        <option value="amoled">AMOLED</option>
                        <option value="colorful">Colorful</option>
                    </select>
                    <input id="accentPicker" class="accent-picker" type="color" value="#2F8CFF" aria-label="Accent color">
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=create" class="btn btn-primary nav-cta">+ New Canvas</a>
                    <a href="index.php?page=logout" class="subtle">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=register" class="btn btn-primary nav-cta">Join Camagru</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php 
    $sessionNotes = $_SESSION['notifications'] ?? [];
    $dbNotes = $headerNotifications ?? [];
    $allNotes = array_merge($sessionNotes, $dbNotes);
    ?>
    <?php if (!empty($allNotes)): ?>
        <div class="notification-bar">
            <div class="container notification-bar__inner">
                <?php foreach ($allNotes as $note): ?>
                    <div class="notification-chip">
                        <span class="notification-chip__type"><?php echo htmlspecialchars($note['type'] ?? 'info'); ?></span>
                        <span><?php echo htmlspecialchars($note['message'] ?? ''); ?></span>
                        <small><?php echo htmlspecialchars($note['timestamp'] ?? ($note['created_at'] ?? '')); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php unset($_SESSION['notifications']); ?>
    <?php endif; ?>

    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-error">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>
