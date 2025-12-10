<?php

require_once 'src/models/Image.php';
require_once 'src/models/Comment.php';
require_once 'src/models/Like.php';
require_once 'src/models/User.php';
require_once 'src/services/EmailService.php';

class ImageController {
    private $imageModel;
    private $commentModel;
    private $likeModel;
    private $userModel;
    private $emailService;
    
    public function __construct() {
        $this->imageModel = new Image();
        $this->commentModel = new Comment();
        $this->likeModel = new Like();
        $this->userModel = new User();
        $this->emailService = new EmailService();
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'save_image') {
                $this->saveImage();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
                $this->addComment();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
                $this->toggleLike();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_image') {
                $this->deleteImage();
            }
        }
        
        require_once 'src/views/create.php';
    }
    
    private function saveImage() {
        $imageData = $_POST['image_data'] ?? '';
        
        if (!empty($imageData)) {
            // Remove data URI prefix
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = base64_decode($imageData);
            
            // Generate unique filename
            $filename = uniqid() . '.png';
            $filepath = UPLOAD_DIR . $filename;
            
            if (file_put_contents($filepath, $imageData)) {
                $this->imageModel->create($_SESSION['user_id'], $filename);
                $_SESSION['success'] = "Image saved successfully!";
            } else {
                $_SESSION['errors'] = ["Failed to save image."];
            }
        }
        
        header('Location: index.php?page=create');
        exit;
    }
    
    private function addComment() {
        $imageId = $_POST['image_id'] ?? 0;
        $comment = trim($_POST['comment'] ?? '');
        
        if (!empty($comment) && $imageId > 0) {
            // Save the comment
            $this->commentModel->create($imageId, $_SESSION['user_id'], $comment);
            
            // MANDATORY FEATURE: Send email notification to image owner
            try {
                // Get image owner information
                $imageOwner = $this->imageModel->getImageOwner($imageId);
                
                // Only send notification if:
                // 1. Image owner exists
                // 2. Commenter is not the image owner (don't notify yourself)
                // 3. Owner has email notifications enabled
                if ($imageOwner && 
                    $imageOwner['id'] != $_SESSION['user_id'] && 
                    $imageOwner['email_notifications']) {
                    
                    // Get commenter information
                    $commenter = $this->userModel->findById($_SESSION['user_id']);
                    
                    // Send the notification email
                    $emailSent = $this->emailService->sendCommentNotification(
                        $imageOwner['email'],
                        $imageOwner['username'],
                        $commenter['username'],
                        $comment,
                        $imageId
                    );
                    
                    if ($emailSent) {
                        error_log("✅ Comment notification sent to {$imageOwner['username']} about comment from {$commenter['username']}");
                    } else {
                        error_log("⚠️ Failed to send comment notification email (SMTP may not be configured)");
                    }
                }
            } catch (Exception $e) {
                error_log("❌ Error sending comment notification: " . $e->getMessage());
                // Don't stop the process if email fails - comment is still saved
            }
            
            $_SESSION['success'] = "Comment added successfully!";
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    private function toggleLike() {
        $imageId = $_POST['image_id'] ?? 0;
        
        if ($imageId > 0) {
            $this->likeModel->toggle($imageId, $_SESSION['user_id']);
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    private function deleteImage() {
        $imageId = $_POST['image_id'] ?? 0;
        
        if ($imageId > 0) {
            $this->imageModel->delete($imageId, $_SESSION['user_id']);
            $_SESSION['success'] = "Image deleted successfully!";
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
