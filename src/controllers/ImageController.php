<?php

require_once 'src/models/Image.php';
require_once 'src/models/Comment.php';
require_once 'src/models/Like.php';

class ImageController {
    private $imageModel;
    private $commentModel;
    private $likeModel;
    
    public function __construct() {
        $this->imageModel = new Image();
        $this->commentModel = new Comment();
        $this->likeModel = new Like();
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
            $this->commentModel->create($imageId, $_SESSION['user_id'], $comment);
            // In production, send email notification to image owner
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
