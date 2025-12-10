# Quick Start Guide - Camagru

## 🚀 Get Started in 3 Steps

### 1. Start the Application
```bash
make build
```

### 2. Open Your Browser
```
http://localhost:8080
```

### 3. Register & Start Creating!

---

## 🎯 Key Features

| Feature | Description |
|---------|-------------|
| 📷 **Camera** | Capture photos with your webcam |
| 🖼️ **Stickers** | 28+ stickers - drag, resize (Shift), rotate (Ctrl) |
| 😊 **Emojis** | 10+ emojis with size control |
| 🎨 **Filters** | 7 Instagram-style filters |
| 👥 **Social** | Friends, stories, likes, comments, messages |
| 🔐 **Secure** | OTP verification, password reset |

---

## ⌨️ Keyboard Shortcuts

### In Create Page (Canvas)
- **Click** - Place sticker/emoji
- **Drag** - Move sticker/emoji
- **Shift + Drag** - Resize sticker
- **Ctrl + Drag** - Rotate sticker

---

## 🐳 Common Commands

```bash
# Start
make build          # First time setup
make start          # Start services

# Manage
make stop           # Stop services
make restart        # Restart services
make rebuild        # Clean rebuild

# Debug
make logs           # View application logs
make shell          # Access container shell

# Clean
make clean          # Remove everything (including data!)
```

---

## 🌐 URLs

| Service | URL |
|---------|-----|
| **Application** | http://localhost:8080 |
| **PHPMyAdmin** | http://localhost:8081 |

**Database Credentials:**
- Username: `camagru_user`
- Password: `camagru_pass`
- Database: `camagru`

---

## 📁 Important Directories

```
public/stickers/    # Add your custom stickers here
public/uploads/     # User uploaded images (auto-created)
config/            # Configuration files
src/views/         # HTML templates
```

---

## 🔧 Configuration

Edit `.env` file for:
- Database credentials
- SMTP email settings
- Application URL

---

## ⚠️ Common Issues

### Camera not working?
- Allow browser camera permissions
- Use HTTPS or localhost

### Email not sending?
- Check SMTP settings in `.env`
- Use Gmail App Password (not regular password)

### Profile picture not updating?
- Check file permissions: `chmod 755 public/uploads/`
- Verify file size under 5MB

### Container issues?
```bash
make rebuild    # Clean rebuild
make logs      # Check error messages
```

---

## 💡 Tips

1. **Add Custom Stickers**: Drop SVG/PNG files in `public/stickers/`
2. **Black Backgrounds**: Stickers with black background auto-remove darkness
3. **Stories**: Last 24 hours, add multiple per day
4. **Friends Only**: Message and see stories only from friends

---

## 📞 Need Help?

Check the full [README.md](README.md) for detailed documentation.

---

**Happy Creating! 📸**
