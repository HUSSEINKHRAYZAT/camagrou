# Camagru - Quick Start Guide

## Initial Setup

### 1. Configure Database
Edit `config/database.php` and update your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'camagru');
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password
```

### 2. Configure Email (Optional)
Edit `config/config.php` and update email settings for verification emails:
```php
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

For Gmail, you'll need to:
- Enable 2-factor authentication
- Generate an app-specific password

### 3. Run Database Setup
```bash
php setup.php
```

This will:
- Create the `camagru` database
- Create all necessary tables (users, images, comments, likes)
- Set up proper relationships and indexes

### 4. Start the Server
```bash
php -S localhost:8080
```

### 5. Access the Application
Open your browser and go to:
```
http://localhost:8080
```

## Testing the Application

### Test User Registration
1. Click "Register" in the navigation
2. Create a test account:
   - Username: testuser
   - Email: test@example.com
   - Password: password123
3. You should see a success message

### Test Login
1. Click "Login"
2. Enter your credentials
3. You'll be redirected to the Create page

### Test Photo Creation
1. On the Create page:
   - Click "Start Camera" (allow camera access) OR
   - Click "Upload Photo" to select an image
2. Select a sticker overlay
3. Preview the combined image
4. Click "Save Image"

### Test Gallery
1. Navigate to "Gallery"
2. View all created images
3. Like and comment on images

## Directory Permissions

Make sure the uploads directory is writable:
```bash
chmod 755 public/uploads
```

## Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify database credentials in `config/database.php`
- Ensure the database user has proper permissions

### Camera Not Working
- Use HTTPS or localhost
- Allow camera permissions in browser
- Try a different browser if issues persist

### Images Not Saving
- Check `public/uploads` directory exists and is writable
- Verify `MAX_FILE_SIZE` in config.php
- Check PHP upload limits in php.ini

### Email Verification Not Working
- Configure SMTP settings in `config/config.php`
- For development, you can manually verify users in the database:
  ```sql
  UPDATE users SET verified = 1 WHERE email = 'test@example.com';
  ```

## Project Features

✅ User authentication (register, login, logout)
✅ Email verification
✅ Webcam photo capture
✅ File upload
✅ Image overlays/stickers
✅ Public gallery with pagination
✅ Like and comment system
✅ User profiles
✅ Image deletion
✅ Email notification preferences
✅ Responsive design

## Next Steps

- Add more stickers in `public/stickers/`
- Customize styles in `public/css/style.css`
- Configure production SMTP for email
- Add more security features (rate limiting, CSRF tokens)
- Deploy to a production server

## Development Notes

- All passwords are hashed using bcrypt
- SQL queries use prepared statements
- Session management is implemented
- File uploads are validated
- CORS is handled for camera access

Enjoy using Camagru! 📸
