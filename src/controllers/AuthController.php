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
                    
                    if ($emailSent) {
                        $_SESSION['success'] = "Registration successful! Please check your email for the 6-digit verification code.";
                        header('Location: index.php?page=verify_otp');
                        exit;
                    } else {
                        // Email failed - show error and don't proceed
                        $errors[] = "Registration failed: Unable to send verification email. Please contact administrator to configure SMTP settings.";
                        error_log("❌ CRITICAL: SMTP not configured! Cannot send verification email to: " . $email);
                    }
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
                    // User is not verified - resend OTP and redirect to verification
                    $otpCode = $this->userModel->resendOTP($user['email']);
                    
                    if ($otpCode) {
                        // Store email in session for verification page
                        $_SESSION['pending_verification_email'] = $user['email'];
                        
                        // Try to send email
                        $emailSent = $this->emailService->sendVerificationEmail($user['email'], $user['username'], $otpCode);
                        
                        if ($emailSent) {
                            $_SESSION['success'] = "Your account is not verified. A new verification code has been sent to your email.";
                            header('Location: index.php?page=verify_otp');
                            exit;
                        } else {
                            $_SESSION['errors'] = ["Unable to send verification email. Please contact administrator to configure SMTP settings."];
                            error_log("❌ CRITICAL: SMTP not configured! Cannot send verification email to: " . $user['email']);
                        }
                    } else {
                        $_SESSION['errors'] = ["Unable to generate verification code. Please contact support."];
                    }
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
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    // Generate OTP code
                    $otpCode = $this->userModel->createResetOTP($email);
                    
                    if ($otpCode) {
                        // Try to send email
                        $emailSent = $this->emailService->sendPasswordResetOTP($email, $user['username'], $otpCode);
                        
                        // Store email in session for verification
                        $_SESSION['reset_email'] = $email;
                        
                        if ($emailSent) {
                            $_SESSION['success'] = "A 6-digit verification code has been sent to your email.";
                            header('Location: index.php?page=verify_reset_otp');
                            exit;
                        } else {
                            $_SESSION['errors'] = ["Unable to send password reset email. Please contact administrator to configure SMTP settings."];
                            error_log("❌ CRITICAL: SMTP not configured! Cannot send password reset email to: " . $email);
                        }
                    }
                }
                
                // Always show success message (security best practice - don't reveal if email exists)
                $_SESSION['success'] = "If the email exists, a verification code has been sent.";
                header('Location: index.php?page=verify_reset_otp');
                exit;
            }
        }
        require_once 'src/views/forgot_password.php';
    }
    
    public function verifyResetOTP() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $otpCode = trim($_POST['otp_code'] ?? '');
            
            if (empty($email) || empty($otpCode)) {
                $_SESSION['errors'] = ["Please enter your email and verification code."];
            } elseif (!preg_match('/^\d{6}$/', $otpCode)) {
                $_SESSION['errors'] = ["Verification code must be 6 digits."];
            } else {
                $user = $this->userModel->verifyResetOTP($email, $otpCode);
                
                if ($user) {
                    // OTP verified - store in session for password reset page
                    $_SESSION['verified_reset_email'] = $email;
                    $_SESSION['verified_reset_user_id'] = $user['id'];
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_otp_code']);
                    
                    $_SESSION['success'] = "Code verified! Please enter your new password.";
                    header('Location: index.php?page=reset_password');
                    exit;
                } else {
                    $_SESSION['errors'] = ["Invalid or expired verification code. Please try again."];
                }
            }
        }
        
        require_once 'src/views/verify_reset_otp.php';
    }
    
    public function resendResetOTP() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                $_SESSION['errors'] = ["Please enter your email address."];
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    $otpCode = $this->userModel->createResetOTP($email);
                    
                    if ($otpCode) {
                        $emailSent = $this->emailService->sendPasswordResetOTP($email, $user['username'], $otpCode);
                        
                        if ($emailSent) {
                            $_SESSION['success'] = "A new verification code has been sent to your email.";
                        } else {
                            $_SESSION['errors'] = ["Unable to send verification email. Please contact administrator."];
                            error_log("❌ CRITICAL: SMTP not configured! Cannot resend password reset OTP to: " . $email);
                        }
                    }
                } else {
                    $_SESSION['success'] = "If the email exists, a new code has been sent.";
                }
            }
            
            header('Location: index.php?page=verify_reset_otp');
            exit;
        }
        
        require_once 'src/views/verify_reset_otp.php';
    }

    public function resetPassword() {
        // Check if user has verified OTP
        if (!isset($_SESSION['verified_reset_email']) || !isset($_SESSION['verified_reset_user_id'])) {
            $_SESSION['errors'] = ["Please verify your email first."];
            header('Location: index.php?page=forgot_password');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            if ($password !== $confirm) {
                $_SESSION['errors'] = ["Passwords do not match."];
            } else {
                $hasUpper = preg_match('/[A-Z]/', $password);
                $hasLower = preg_match('/[a-z]/', $password);
                $hasNum = preg_match('/[0-9]/', $password);
                
                if (strlen($password) < 8 || !$hasUpper || !$hasLower || !$hasNum) {
                    $_SESSION['errors'] = ["Password must be at least 8 chars and include upper, lower and number."]; 
                } else {
                    // Update password
                    $this->userModel->updatePasswordById($_SESSION['verified_reset_user_id'], $password);
                    
                    // Clear OTP from database
                    $this->userModel->clearOTP($_SESSION['verified_reset_email']);
                    
                    // Clear session variables
                    unset($_SESSION['verified_reset_email']);
                    unset($_SESSION['verified_reset_user_id']);
                    
                    $_SESSION['success'] = "Password updated successfully! You can now log in.";
                    header('Location: index.php?page=login');
                    exit;
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
                            $_SESSION['errors'] = ["Unable to send verification email. Please contact administrator to configure SMTP settings."];
                            error_log("❌ CRITICAL: SMTP not configured! Cannot resend OTP to: " . $email);
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
