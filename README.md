# 📸 Camagru - Social Media Photo Sharing Application

A modern, Instagram-inspired photo sharing social media platform built with PHP, featuring real-time camera capture, interactive stickers, filters, and social networking capabilities.

![PHP](https://img.shields.io/badge/PHP-8.1-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## ✨ Features

### 📷 Photo Creation & Editing
- **Live Camera Capture** - Real-time webcam access with live preview
- **File Upload** - Drag & drop or browse to upload images
- **Stickers & Overlays** - 28+ stickers (SVG + PNG) with transparent backgrounds
  - Mouse-based resize (Shift + Drag)
  - Mouse-based rotation (Ctrl + Drag)
  - Drag to reposition
- **Emoji Reactions** - 10+ categorized emojis with adjustable sizes
- **Filters** - 7 Instagram-style filters (Mono, Vintage, Sunlit, Neon, Bright, Noir)
- **Real-time Preview** - See changes instantly before saving

### 👥 Social Features
- **User Profiles** - Customizable bio and profile pictures
  - Choose from your own posts or upload new photos
- **Friend System** - Send/accept friend requests
- **Stories** - 24-hour temporary photo stories
- **Photo Gallery** - Grid view of all user posts
- **Likes & Comments** - Engage with posts
- **Direct Messaging** - Chat with friends
- **Friend Suggestions** - Discover new connections

### 🔐 Authentication & Security
- **User Registration** - Email verification with OTP
- **Secure Login** - Password hashing with bcrypt
- **Password Reset** - OTP-based password recovery
- **Email Notifications** - Optional comment notifications
- **Session Management** - Secure session handling

### 🎨 User Experience
- **Responsive Design** - Works on desktop and mobile
- **Dark/Light Theme** - User preference support
- **Search & Filter** - Find stickers, emojis, and filters easily
- **Category Tags** - Organize content by categories (Fun, Retro, etc.)
- **Real-time Updates** - Instant feedback on all actions

## 🚀 Quick Start

### Prerequisites

- **Docker** & **Docker Compose** installed
- **Git** (for cloning the repository)
- Modern web browser with webcam support (optional, for camera features)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd repo
   ```

2. **Build and start the containers**
   ```bash
   make build
   # OR
   docker-compose up -d --build
   ```

3. **Access the application**
   - **Main App**: http://localhost:8080
   - **PHPMyAdmin**: http://localhost:8081 (optional database management)
     - Server: `db`
     - Username: `camagru_user`
     - Password: `camagru_pass`

4. **Create your account**
   - Register with a valid email
   - Verify your email with the OTP code
   - Start creating and sharing photos!

## 🛠️ Technology Stack

### Backend
- **PHP 8.1** - Server-side logic
- **MySQL 8.0** - Database
- **PHPMailer** - Email notifications
- **DotEnv** - Environment configuration

### Frontend
- **Vanilla JavaScript** - Interactive features
- **HTML5 Canvas** - Image manipulation
- **CSS3** - Modern styling
- **MediaDevices API** - Camera access

### DevOps
- **Docker & Docker Compose** - Containerization
- **Apache** - Web server
- **Git** - Version control

## 📁 Project Structure

```
repo/
├── config/                 # Configuration files
│   ├── config.php         # App configuration
│   └── database.php       # Database connection
├── public/                # Public assets
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   ├── js/
│   │   ├── camera.js     # Camera & editing logic
│   │   ├── profile.js    # Profile interactions
│   │   └── theme.js      # Theme switching
│   ├── stickers/         # Sticker assets (SVG/PNG)
│   └── uploads/          # User uploaded images
├── src/
│   ├── controllers/      # Application controllers
│   │   ├── AuthController.php
│   │   ├── GalleryController.php
│   │   ├── ImageController.php
│   │   ├── NotificationController.php
│   │   └── UserController.php
│   ├── models/           # Data models
│   │   ├── User.php
│   │   ├── Image.php
│   │   ├── Comment.php
│   │   ├── Like.php
│   │   ├── Friendship.php
│   │   ├── Message.php
│   │   ├── Story.php
│   │   └── Notification.php
│   ├── services/         # Business logic services
│   │   └── EmailService.php
│   └── views/            # HTML templates
│       ├── header.php
│       ├── footer.php
│       ├── login.php
│       ├── register.php
│       ├── profile.php
│       ├── gallery.php
│       └── create.php
├── docker-compose.yml    # Docker services configuration
├── Dockerfile           # Web container definition
├── Makefile            # Build automation
├── index.php           # Application entry point
└── README.md           # This file
```

## 🎮 Usage Guide

### Creating a Photo

1. **Navigate to Create page** (click "Create" button)
2. **Capture or Upload**:
   - Click "Start Camera" to use webcam
   - Or drag & drop an image file
   - Or click "Browse Files" to select from device
3. **Add Stickers**:
   - Select from Stickers tab
   - Click on canvas to place
   - **Drag** to move
   - **Shift + Drag** to resize
   - **Ctrl + Drag** to rotate
4. **Add Emojis**:
   - Select from Emojis tab
   - Adjust size with slider
   - Click on canvas to place
5. **Apply Filters**:
   - Choose from Filters tab
   - Preview in real-time
6. **Save**: Click "Save Image" to post

### Managing Profile

1. **Click "Edit Profile"**
2. **Update Bio**: Edit your bio text
3. **Change Profile Picture**:
   - **Option 1**: Select from your existing posts
   - **Option 2**: Click "Choose File" to upload new
4. **Save Changes**: Click "Save Changes" button

### Adding Stories

1. **Go to your profile**
2. **Click the "+" button** in Stories section
3. **Select an image** from your device
4. **Story goes live** for 24 hours

### Friend Management

1. **Browse users** in gallery or suggestions
2. **Send friend request**
3. **Accept/Reject** incoming requests in your profile
4. **Message friends** via their profile page

## ⚙️ Configuration

### Environment Variables

Create a `.env` file in the root directory (or configure in Docker):

```env
# Database
DB_HOST=db
DB_NAME=camagru
DB_USER=camagru_user
DB_PASS=camagru_pass

# Email (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM=your-email@gmail.com
SMTP_FROM_NAME=Camagru

# Application
APP_URL=http://localhost:8080
```

### Email Setup

For Gmail SMTP:
1. Enable 2-Factor Authentication
2. Generate an App Password
3. Use the app password in `.env`

## 🐳 Docker Commands

```bash
# Build and start all services
make build
# OR
docker-compose up -d --build

# Stop all services
make stop
# OR
docker-compose stop

# Restart services
make restart
# OR
docker-compose restart

# Rebuild from scratch (clean build)
make rebuild
# OR
docker-compose down && docker-compose up -d --build

# View logs
docker-compose logs -f web

# Access container shell
docker-compose exec web bash

# Clean up everything (including data!)
make clean
# OR
docker-compose down -v
```

## 📊 Database Schema

### Main Tables

- **users** - User accounts and authentication
- **user_profiles** - Profile information (bio, avatar)
- **images** - User-uploaded photos
- **likes** - Photo likes
- **comments** - Photo comments
- **friendships** - Friend connections
- **friend_requests** - Pending friend requests
- **messages** - Direct messages between users
- **stories** - Temporary 24-hour stories
- **notifications** - User notifications
- **password_resets** - Password reset tokens

## 🔧 Development

### Adding New Stickers

1. Place sticker files in `public/stickers/`
2. Edit `src/views/create.php`:
   ```php
   ['file' => 'your-sticker.svg', 'label' => 'Your Label', 'category' => 'fun'],
   ```

### Customizing Filters

Edit `public/js/camera.js`:
```javascript
const filterMap = {
    yourfilter: 'brightness(1.2) contrast(1.1)',
    // Add more CSS filters
};
```

### Adjusting Sticker Background Removal

Edit threshold in `public/js/camera.js`:
```javascript
const threshold = 50; // 0-255, higher = more aggressive
```

## 🐛 Troubleshooting

### Camera Not Working
- Ensure browser has camera permissions
- Use HTTPS (required by most browsers)
- Check browser console for errors

### Email Not Sending
- Verify SMTP credentials in `.env`
- Check firewall/network settings
- Enable "Less secure app access" or use App Password

### Profile Picture Not Updating
- Check file permissions on `public/uploads/`
- Verify file size limits (default 5MB)
- Check Docker logs: `docker-compose logs web`

### Database Connection Issues
- Wait for MySQL health check to pass
- Verify credentials in `.env`
- Check container logs: `docker-compose logs db`

## 📝 License

This project is part of the 42 School curriculum.

## 👥 Authors

- **Your Name** - Initial work

## 🙏 Acknowledgments

- 42 School for the project requirements
- PHPMailer for email functionality
- Canvas API for image manipulation
- Docker community for containerization best practices

## 📮 Support

For issues, questions, or contributions:
1. Check existing issues
2. Create a new issue with detailed description
3. Include error logs and screenshots if applicable

---

**Made with ❤️ for 42 School**
