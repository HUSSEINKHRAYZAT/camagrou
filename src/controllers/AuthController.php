<?php

require_once 'src/models/User.php';
require_once 'src/services/EmailService.php';

class AuthController {
    private $userModel;
    private $emailService;
    
    public function __construct() {
        $this->userModel = new User();
        $this->emailService = new EmailService();
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
                $otpCode = $this->userModel->create($username, $email, $password);
                
                if ($otpCode) {
                    // Send verification email with OTP
                    $emailSent = $this->emailService->sendVerificationEmail($email, $username, $otpCode);
                    
                    // Store email in session for verification page
                    $_SESSION['pending_verification_email'] = $email;
                    $_SESSION['pending_otp_code'] = $otpCode; // Store for display if email fails
                    
                    if ($emailSent) {
                        $_SESSION['success'] = "Registration successful! Please check your email for the 6-digit verification code.";
                    } else {
                        $_SESSION['warning'] = "Registration successful! Email could not be sent (SMTP not configured).<br><br>🔑 <strong>Your verification code is: <span style='font-size: 24px; color: #667eea; letter-spacing: 5px; font-family: monospace;'>" . $otpCode . "</span></strong><br><br>Enter this code on the next page to verify your account.<br><small>To enable email, run: <code>./setup_email.sh</code></small>";
                    }
                    
                    header('Location: index.php?page=verify_otp');
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
                    $user = $this->userModel->findByEmail($email);
                    if ($user) {
                        $this->emailService->sendPasswordResetEmail($email, $user['username'], $token);
                    }
                }
                // Always show success message (security best practice - don't reveal if email exists)
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
    
    public function verifyOTP() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $otpCode = trim($_POST['otp_code'] ?? '');
            
            if (empty($email) || empty($otpCode)) {
                $_SESSION['errors'] = ["Please enter your email and verification code."];
            } elseif (!preg_match('/^\d{6}$/', $otpCode)) {
                $_SESSION['errors'] = ["Verification code must be 6 digits."];
            } else {
                if ($this->userModel->verifyOTP($email, $otpCode)) {
                    unset($_SESSION['pending_verification_email']);
                    $_SESSION['success'] = "Email verified successfully! You can now log in.";
                    header('Location: index.php?page=login');
                    exit;
                } else {
                    $_SESSION['errors'] = ["Invalid or expired verification code. Please try again or request a new code."];
                }
            }
        }
        
        require_once 'src/views/verify_otp.php';
    }
    
    public function resendOTP() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                $_SESSION['errors'] = ["Please enter your email address."];
            } else {
                $otpCode = $this->userModel->resendOTP($email);
                
                if ($otpCode) {
                    $user = $this->userModel->findByEmail($email);
                    if ($user) {
                        $emailSent = $this->emailService->sendVerificationEmail($email, $user['username'], $otpCode);
                        
                        if ($emailSent) {
                            $_SESSION['success'] = "A new verification code has been sent to your email.";
                        } else {
                            $_SESSION['success'] = "New code generated. Your OTP: " . $otpCode;
                        }
                    }
                } else {
                    $_SESSION['errors'] = ["No unverified account found with this email, or account is already verified."];
                }
            }
            
            header('Location: index.php?page=verify_otp');
            exit;
        }
        
        require_once 'src/views/verify_otp.php';
    }
}
