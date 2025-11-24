# Camagru

A web application for creating and sharing photo montages with pre-defined image overlays/stickers.

## Features

- User registration and authentication with email verification
- Photo capture using webcam or file upload
- Apply pre-defined stickers/overlays to photos
- Public gallery of all user-created images
- Commenting and liking system
- User profiles
- Email notifications for comments
- Responsive design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser with webcam support

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd camagru
```

2. Configure the database in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'camagru');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
```

3. Configure email settings in `config/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

4. Set up the database:
```bash
php setup.php
```

5. Start the development server:
```bash
php -S localhost:8080
```

6. Open your browser and navigate to `http://localhost:8080`

## Project Structure

```
camagru/
├── config/
│   ├── config.php          # General configuration
│   └── database.php        # Database configuration
├── public/
│   ├── css/
│   │   └── style.css       # Styles
│   ├── js/
│   │   └── camera.js       # Camera functionality
│   ├── uploads/            # User-uploaded images
│   └── stickers/           # Pre-defined stickers
├── src/
│   ├── controllers/        # Application controllers
│   ├── models/             # Database models
│   └── views/              # HTML templates
├── index.php               # Main entry point
├── setup.php               # Database setup script
└── README.md
```

## Usage

### Registration
1. Click "Register" in the navigation
2. Fill in username, email, and password
3. Check your email for verification link
4. Click the verification link

### Login
1. Click "Login" in the navigation
2. Enter your email and password
3. Click "Login"

### Creating Photos
1. Navigate to "Create" page
2. Click "Start Camera" to use webcam or "Upload Photo" to upload
3. Capture/select your photo
4. Choose a sticker overlay
5. Preview the result
6. Click "Save Image"

### Gallery
- View all user-created images
- Like and comment on images
- Click on usernames to view profiles

### Profile
- View your uploaded images
- Delete your own images
- Toggle email notification preferences

## Security Features

- Password hashing using bcrypt
- SQL injection prevention using prepared statements
- CSRF protection
- Email verification
- Session management
- File upload validation

## Technologies

- PHP (Backend)
- MySQL (Database)
- HTML5 (Structure)
- CSS3 (Styling)
- JavaScript (Client-side functionality)
- MediaDevices API (Webcam access)
- Canvas API (Image manipulation)

## License

This project is created as part of 42 School curriculum.

## Author

Created by [Your Name]
