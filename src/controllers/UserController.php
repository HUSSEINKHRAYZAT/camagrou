<?php

require_once 'src/models/User.php';
require_once 'src/models/Image.php';
require_once 'src/models/Friendship.php';
require_once 'src/models/Notification.php';
require_once 'src/models/Message.php';

class UserController {
    private $userModel;
    private $imageModel;
    private $friendshipModel;
    private $notificationModel;
    private $messageModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->imageModel = new Image();
        $this->friendshipModel = new Friendship();
        $this->notificationModel = new Notification();
        $this->messageModel = new Message();
    }
    
    public function profile() {
        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        $viewerId = $_SESSION['user_id'];
        $this->userModel->ensureProfileTable();
        $relationship = $this->friendshipModel->getRelationship($viewerId, $userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Settings update for own profile
            if ($userId == $viewerId && isset($_POST['email_notifications'])) {
                $notifications = isset($_POST['email_notifications']) ? 1 : 0;
                $this->userModel->updateNotificationPreference($viewerId, $notifications);
                $_SESSION['success'] = "Settings updated successfully!";
            }

            // Profile update (bio + avatar)
            if ($userId == $viewerId && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
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

            // Account update (password change only)
            if ($userId == $viewerId && isset($_POST['action']) && $_POST['action'] === 'update_account') {
                $newPassword = $_POST['password'] ?? '';
                $errors = [];
                
                if (empty($newPassword)) {
                    $errors[] = "Password is required.";
                } else {
                    $hasUpper = preg_match('/[A-Z]/', $newPassword);
                    $hasLower = preg_match('/[a-z]/', $newPassword);
                    $hasNum = preg_match('/[0-9]/', $newPassword);
                    if (strlen($newPassword) < 8 || !$hasUpper || !$hasLower || !$hasNum) {
                        $errors[] = "Password must be at least 8 chars and include upper, lower and number.";
                    }
                }
                
                if (empty($errors)) {
                    $this->userModel->updatePassword($viewerId, $newPassword);
                    $_SESSION['success'] = "Password changed successfully!";
                } else {
                    $_SESSION['errors'] = $errors;
                }
                header('Location: index.php?page=profile');
                exit;
            }

            // Friend request trigger when viewing someone else
            if (isset($_POST['action']) && $_POST['action'] === 'send_friend_request' && isset($_POST['recipient_id'])) {
                $recipientId = (int) $_POST['recipient_id'];
                if ($recipientId !== (int) $viewerId) {
                    $result = $this->friendshipModel->sendRequest($viewerId, $recipientId);
                    if ($result['success']) {
                        $_SESSION['success'] = $result['message'];
                        $this->notificationModel->create(
                            $recipientId,
                            'friend_request',
                            "You have a friend request from user #" . $viewerId,
                            $viewerId
                        );
                    } else {
                        $_SESSION['errors'][] = $result['message'];
                    }
                }
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Unfriend
            if (isset($_POST['action']) && $_POST['action'] === 'unfriend' && isset($_POST['friend_id'])) {
                $friendId = (int) $_POST['friend_id'];
                if ($friendId !== (int) $viewerId) {
                    $this->friendshipModel->removeFriend($viewerId, $friendId);
                    $_SESSION['success'] = "Friend removed.";
                }
                header('Location: index.php?page=profile&user_id=' . $friendId);
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

            // Send message to friend
            if (isset($_POST['action']) && $_POST['action'] === 'send_message' && isset($_POST['recipient_id'])) {
                $recipientId = (int) $_POST['recipient_id'];
                if ($recipientId !== (int)$viewerId && $this->friendshipModel->areFriends($viewerId, $recipientId)) {
                    $body = $_POST['message_body'] ?? '';
                    if (!$this->messageModel->send($viewerId, $recipientId, $body)) {
                        $_SESSION['errors'][] = "Message could not be sent.";
                    }
                } else {
                    $_SESSION['errors'][] = "You can only message friends.";
                }
                header('Location: index.php?page=profile&user_id=' . $recipientId);
                exit;
            }
        }
        
        $user = $this->userModel->findById($userId);
        $images = $this->imageModel->getUserImages($userId);
        $suggestions = $this->userModel->findSuggestions($userId, 8);
        $pendingRequests = ($userId == $_SESSION['user_id']) ? $this->friendshipModel->getPendingRequests($userId) : [];
        $canViewStory = true; // Anyone can view stories
        $activeStory = $this->userModel->getActiveStory($userId);
        $headerNotifications = $this->notificationModel->getUnread($_SESSION['user_id']);
        $friends = $this->friendshipModel->getFriends($userId, 100);
        $friendCount = $this->friendshipModel->countFriends($userId);
        $messages = [];
        if ($relationship['status'] === 'friends' && $userId != $viewerId) {
            $messages = $this->messageModel->getConversation($viewerId, $userId, 100);
        }
        
        require_once 'src/views/profile.php';
    }
}
