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
            
            $hasUpper = preg_match('/[A-Z]/', $password);
            $hasLower = preg_match('/[a-z]/', $password);
            $hasNum = preg_match('/[0-9]/', $password);
            if (strlen($password) < 8 || !$hasUpper || !$hasLower || !$hasNum) {
                $errors[] = "Password must be at least 8 chars and include upper, lower and number.";
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
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $user = $this->userModel->findByIdentifier($identifier);
            
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
                $_SESSION['errors'] = ["Invalid username/email or password."];
            }
        }
        
        require_once 'src/views/login.php';
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['errors'] = ["Enter a valid email."];
            } else {
                $token = $this->userModel->createResetToken($email);
                if ($token) {
                    $resetUrl = BASE_URL . "/index.php?page=reset_password&token=" . $token;
                    $subject = "Reset your Camagru password";
                    $message = "Click to reset your password:\n\n" . $resetUrl;
                    mail($email, $subject, $message, "From: " . SMTP_FROM);
                }
                $_SESSION['success'] = "If the email exists, a reset link was sent.";
                header('Location: index.php?page=login');
                exit;
            }
        }
        require_once 'src/views/forgot_password.php';
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if ($password !== $confirm) {
                $_SESSION['errors'] = ["Passwords do not match."];
            } else {
                $check = $this->userModel->validateResetToken($token);
                if ($check) {
                    $hasUpper = preg_match('/[A-Z]/', $password);
                    $hasLower = preg_match('/[a-z]/', $password);
                    $hasNum = preg_match('/[0-9]/', $password);
                    if (strlen($password) < 8 || !$hasUpper || !$hasLower || !$hasNum) {
                        $_SESSION['errors'] = ["Password must be at least 8 chars and include upper, lower and number."]; 
                    } else {
                        $this->userModel->updatePasswordById($check['user_id'], $password);
                        $_SESSION['success'] = "Password updated. Please login.";
                        header('Location: index.php?page=login');
                        exit;
                    }
                } else {
                    $_SESSION['errors'] = ["Invalid or expired reset link."];
                }
            }
        }
        require_once 'src/views/reset_password.php';
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
