<?php

require_once 'config/config.php';
require_once 'src/models/Like.php';
require_once 'src/models/Comment.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'toggle_like':
        $imageId = (int)($_POST['image_id'] ?? 0);
        if ($imageId > 0) {
            $likeModel = new Like();
            $liked = $likeModel->toggle($imageId, $_SESSION['user_id']);
            echo json_encode(['success' => true, 'liked' => $liked]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid image ID']);
        }
        break;
        
    case 'add_comment':
        $imageId = (int)($_POST['image_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if ($imageId > 0 && !empty($comment)) {
            $commentModel = new Comment();
            $success = $commentModel->create($imageId, $_SESSION['user_id'], $comment);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
        }
        break;
        
    case 'delete_comment':
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId > 0) {
            $commentModel = new Comment();
            $success = $commentModel->delete($commentId, $_SESSION['user_id']);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid comment ID']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
