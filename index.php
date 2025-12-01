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
    
    case 'forgot_password':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->forgotPassword();
        break;

    case 'reset_password':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resetPassword();
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
    
    case 'verify_otp':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->verifyOTP();
        break;
    
    case 'resend_otp':
        require_once 'src/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resendOTP();
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

    case 'notifications-clear':
        if (!$isLoggedIn) {
            header('Location: index.php?page=login');
            exit;
        }
        require_once 'src/controllers/NotificationController.php';
        $controller = new NotificationController();
        $controller->clearAll();
        break;
        
    default:
        header('HTTP/1.0 404 Not Found');
        echo '404 - Page not found';
        break;
}
