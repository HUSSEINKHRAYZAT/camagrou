<?php

require_once 'src/models/Notification.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new Notification();
    }

    public function clearAll() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $this->notificationModel->markAllRead($_SESSION['user_id']);

        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=home';
        header('Location: ' . $redirect);
        exit;
    }
}
