<?php

require_once 'src/models/User.php';
require_once 'src/models/Image.php';
require_once 'src/models/Friendship.php';
require_once 'src/models/Notification.php';

class UserController {
    private $userModel;
    private $imageModel;
    private $friendshipModel;
    private $notificationModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->imageModel = new Image();
        $this->friendshipModel = new Friendship();
        $this->notificationModel = new Notification();
    }
    
    public function profile() {
        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        $this->userModel->ensureProfileTable();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Settings update for own profile
            if ($userId == $_SESSION['user_id'] && isset($_POST['email_notifications'])) {
                $notifications = isset($_POST['email_notifications']) ? 1 : 0;
                $this->userModel->updateNotificationPreference($userId, $notifications);
                $_SESSION['success'] = "Settings updated successfully!";
            }

            // Profile update (bio + avatar)
            if ($userId == $_SESSION['user_id'] && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
                $bio = trim($_POST['bio'] ?? '');
                $avatarPath = null;

                if (!empty($_FILES['avatar_file']['name'])) {
                    $file = $_FILES['avatar_file'];
                    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= MAX_FILE_SIZE) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        if (in_array($mime, ALLOWED_TYPES)) {
                            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                            $filename = uniqid('avatar_', true) . '.' . $ext;
                            $filepath = UPLOAD_DIR . $filename;
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $avatarPath = 'public/uploads/' . $filename;
                            } else {
                                $_SESSION['errors'][] = "Failed to upload avatar.";
                            }
                        } else {
                            $_SESSION['errors'][] = "Invalid avatar file type.";
                        }
                    } else {
                        $_SESSION['errors'][] = "Avatar upload error.";
                    }
                }

                $this->userModel->updateProfile($userId, $bio, $avatarPath);
                if (empty($_SESSION['errors'])) {
                    $_SESSION['success'] = "Profile updated.";
                }
                header('Location: index.php?page=profile');
                exit;
            }

            // Story update
            if ($userId == $_SESSION['user_id'] && isset($_POST['action']) && $_POST['action'] === 'update_story') {
                if (!empty($_FILES['story_file']['name'])) {
                    $file = $_FILES['story_file'];
                    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= MAX_FILE_SIZE) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        if (in_array($mime, ALLOWED_TYPES)) {
                            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                            $filename = uniqid('story_', true) . '.' . $ext;
                            $filepath = UPLOAD_DIR . $filename;
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $publicPath = 'public/uploads/' . $filename;
                                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                                $this->userModel->updateStory($userId, $publicPath, $expires);
                                $_SESSION['success'] = "Story added.";
                            } else {
                                $_SESSION['errors'][] = "Failed to upload story.";
                            }
                        } else {
                            $_SESSION['errors'][] = "Invalid story file type.";
                        }
                    } else {
                        $_SESSION['errors'][] = "Story upload error.";
                    }
                }
                header('Location: index.php?page=profile');
                exit;
            }

            // Friend request trigger when viewing someone else
            if (isset($_POST['action']) && $_POST['action'] === 'send_friend_request' && isset($_POST['recipient_id'])) {
                $recipientId = (int) $_POST['recipient_id'];
                if ($recipientId !== (int) $_SESSION['user_id']) {
                    $result = $this->friendshipModel->sendRequest($_SESSION['user_id'], $recipientId);
                    if ($result['success']) {
                        $_SESSION['success'] = $result['message'];
                        $this->notificationModel->create(
                            $recipientId,
                            'friend_request',
                            "You have a friend request from user #" . $_SESSION['user_id'],
                            $_SESSION['user_id']
                        );
                    } else {
                        $_SESSION['errors'][] = $result['message'];
                    }
                }
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Accept / reject friend request
            if (isset($_POST['action']) && $_POST['action'] === 'respond_friend_request' && isset($_POST['request_id'])) {
                $requestId = (int) $_POST['request_id'];
                $decision = $_POST['decision'] ?? 'reject';
                if ($decision === 'accept') {
                    $result = $this->friendshipModel->acceptRequest($requestId, $_SESSION['user_id']);
                    $_SESSION['success'] = $result['message'];
                    // notify sender
                    $this->notificationModel->create(
                        $result['sender_id'] ?? 0,
                        'friend_accepted',
                        "Your friend request was accepted.",
                        $_SESSION['user_id']
                    );
                } else {
                    $this->friendshipModel->rejectRequest($requestId, $_SESSION['user_id']);
                    $_SESSION['success'] = "Friend request rejected.";
                }
                header('Location: index.php?page=profile');
                exit;
            }
        }
        
        $user = $this->userModel->findById($userId);
        $images = $this->imageModel->getUserImages($userId);
        $suggestions = $this->userModel->findSuggestions($userId, 8);
        $pendingRequests = ($userId == $_SESSION['user_id']) ? $this->friendshipModel->getPendingRequests($userId) : [];
        $activeStory = $this->userModel->getActiveStory($userId);
        $headerNotifications = $this->notificationModel->getUnread($_SESSION['user_id']);
        $friends = $this->friendshipModel->getFriends($userId, 100);
        $friendCount = $this->friendshipModel->countFriends($userId);
        
        require_once 'src/views/profile.php';
    }
}
