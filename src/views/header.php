<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Camagru'; ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-inner">
            <a href="index.php?page=home" class="logo">
                <span class="logo-mark">C</span>
                <span class="logo-text">
                    Camagru
                    <small>Capture &amp; Create</small>
                </span>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=create" class="btn btn-primary nav-cta">+ New Canvas</a>
                    <a href="index.php?page=logout" class="subtle">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=register" class="btn btn-primary nav-cta">Join Camagru</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

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
