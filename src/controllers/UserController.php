<?php

require_once 'src/models/User.php';
require_once 'src/models/Image.php';

class UserController {
    private $userModel;
    private $imageModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->imageModel = new Image();
    }
    
    public function profile() {
        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId == $_SESSION['user_id']) {
            $notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $this->userModel->updateNotificationPreference($userId, $notifications);
            $_SESSION['success'] = "Settings updated successfully!";
        }
        
        $user = $this->userModel->findById($userId);
        $images = $this->imageModel->getUserImages($userId);
        
        require_once 'src/views/profile.php';
    }
}
