<?php

require_once 'src/models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            // Validation
            if (empty($username) || strlen($username) < 3) {
                $errors[] = "Username must be at least 3 characters long.";
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email address.";
            }
            
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters long.";
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = "Passwords do not match.";
            }
            
            if (empty($errors)) {
                $token = $this->userModel->create($username, $email, $password);
                
                if ($token) {
                    // Send verification email
                    $this->sendVerificationEmail($email, $token);
                    
                    $_SESSION['success'] = "Registration successful! Please check your email to verify your account.";
                    header('Location: index.php?page=login');
                    exit;
                } else {
                    $errors[] = "Username or email already exists.";
                }
            }
            
            $_SESSION['errors'] = $errors;
        }
        
        require_once 'src/views/register.php';
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $user = $this->userModel->findByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                if (!$user['verified']) {
                    $_SESSION['errors'] = ["Please verify your email before logging in."];
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: index.php?page=create');
                    exit;
                }
            } else {
                $_SESSION['errors'] = ["Invalid email or password."];
            }
        }
        
        require_once 'src/views/login.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: index.php?page=home');
        exit;
    }
    
    public function verify() {
        $token = $_GET['token'] ?? '';
        
        if ($this->userModel->verifyEmail($token)) {
            $_SESSION['success'] = "Email verified successfully! You can now log in.";
        } else {
            $_SESSION['errors'] = ["Invalid or expired verification token."];
        }
        
        header('Location: index.php?page=login');
        exit;
    }
    
    private function sendVerificationEmail($email, $token) {
        $verificationUrl = BASE_URL . "/index.php?page=verify&token=" . $token;
        $subject = "Verify your Camagru account";
        $message = "Click the following link to verify your account:\n\n" . $verificationUrl;
        $headers = "From: " . SMTP_FROM;
        
        // In production, use PHPMailer or similar library
        mail($email, $subject, $message, $headers);
    }
}
