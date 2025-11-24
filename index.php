<?php

require_once 'config/config.php';

// Simple routing
$page = $_GET['page'] ?? 'home';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Route to appropriate page
switch ($page) {
    case 'home':
    case 'gallery':
        require_once 'src/controllers/GalleryController.php';
        $controller = new GalleryController();
        $controller->index();
        break;
        
    case 'register':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;
        
    case 'login':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'logout':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case 'verify':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->verify();
        break;
        
    case 'create':
        if (!$isLoggedIn) {
            header('Location: index.php?page=login');
            exit;
        }
        require_once 'src/controllers/ImageController.php';
        $controller = new ImageController();
        $controller->create();
        break;
        
    case 'profile':
        if (!$isLoggedIn) {
            header('Location: index.php?page=login');
            exit;
        }
        require_once 'src/controllers/UserController.php';
        $controller = new UserController();
        $controller->profile();
        break;
        
    default:
        header('HTTP/1.0 404 Not Found');
        echo '404 - Page not found';
        break;
}
