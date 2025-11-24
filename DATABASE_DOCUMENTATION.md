# 📊 CAMAGRU ENHANCED DATABASE DOCUMENTATION

## 🎯 Overview
This document describes the enhanced social media database schema for Camagru, including all tables, relationships, and features.

---

## 📋 Database Tables

### 1. **users** (Core Authentication)
Primary user authentication and account table.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Unique user identifier |
| username | VARCHAR(50) | Unique username |
| email | VARCHAR(100) | Unique email address |
| password | VARCHAR(255) | Hashed password (bcrypt) |
| verified | BOOLEAN | Email verification status |
| verification_token | VARCHAR(255) | Email verification token |
| email_notifications | BOOLEAN | Email notification preference |
| created_at | TIMESTAMP | Account creation date |
| updated_at | TIMESTAMP | Last update timestamp |

---

### 2. **user_profiles** (Profile Information)
Extended user profile data.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Profile ID |
| user_id | INT (FK → users) | Reference to user |
| profile_picture | VARCHAR(255) | Profile picture filename |
| bio | TEXT | User biography |
| full_name | VARCHAR(100) | Full display name |
| location | VARCHAR(100) | User location |
| website | VARCHAR(255) | Personal website URL |
| birth_date | DATE | Date of birth |
| gender | ENUM | Gender (male/female/other/prefer_not_to_say) |
| is_private | BOOLEAN | Private profile setting |
| created_at | TIMESTAMP | Profile creation date |
| updated_at | TIMESTAMP | Last update timestamp |

**Features:**
- ✅ Profile customization
- ✅ Privacy controls
- ✅ Automatic creation for new users

---

### 3. **friendships** (Friend Connections)
Bidirectional friend relationships.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Friendship ID |
| user_id | INT (FK → users) | First user |
| friend_id | INT (FK → users) | Second user |
| created_at | TIMESTAMP | Friendship creation date |

**Features:**
- ✅ Bidirectional relationships (both ways stored)
- ✅ Fast friend list queries
- ✅ CASCADE DELETE (auto-cleanup)

---

### 4. **friend_requests** (Pending Friend Requests)
Manages friend request workflow.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Request ID |
| sender_id | INT (FK → users) | User who sent request |
| receiver_id | INT (FK → users) | User who received request |
| status | ENUM | Status (pending/accepted/rejected) |
| created_at | TIMESTAMP | Request creation date |
| updated_at | TIMESTAMP | Last status update |

**Features:**
- ✅ Three-state workflow
- ✅ Prevents duplicate requests
- ✅ Tracks request history

---

### 5. **images** (User Photos/Gallery)
Enhanced image storage with privacy controls.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Image ID |
| user_id | INT (FK → users) | Image owner |
| filename | VARCHAR(255) | Stored filename |
| caption | TEXT | Image caption/description |
| filter | VARCHAR(50) | Applied filter name |
| is_public | BOOLEAN | Public visibility setting |
| created_at | TIMESTAMP | Upload timestamp |

**New Features:**
- ✅ Captions
- ✅ Privacy controls
- ✅ Filter tracking

---

### 6. **stories** (24-Hour Expiring Content)
Instagram-style temporary stories.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Story ID |
| user_id | INT (FK → users) | Story creator |
| media_type | ENUM | Type (image/video) |
| filename | VARCHAR(255) | Media filename |
| caption | TEXT | Story caption |
| expires_at | TIMESTAMP | Expiration timestamp (24h) |
| created_at | TIMESTAMP | Creation timestamp |

**Features:**
- ✅ Auto-expires after 24 hours
- ✅ View tracking
- ✅ Support for images and videos

---

### 7. **story_views** (Story View Tracking)
Tracks who viewed each story.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | View record ID |
| story_id | INT (FK → stories) | Story that was viewed |
| viewer_id | INT (FK → users) | User who viewed |
| viewed_at | TIMESTAMP | View timestamp |

**Features:**
- ✅ One view per user per story
- ✅ Viewer list for story creators

---

### 8. **likes** (Image Likes)
Track likes on images.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Like ID |
| image_id | INT (FK → images) | Liked image |
| user_id | INT (FK → users) | User who liked |
| created_at | TIMESTAMP | Like timestamp |

---

### 9. **comments** (Image Comments)
Comments on images.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Comment ID |
| image_id | INT (FK → images) | Commented image |
| user_id | INT (FK → users) | Comment author |
| comment | TEXT | Comment text |
| created_at | TIMESTAMP | Comment timestamp |

---

### 10. **notifications** (User Notifications)
Activity notifications system.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Notification ID |
| user_id | INT (FK → users) | Notification recipient |
| type | ENUM | Type (like/comment/friend_request/etc) |
| related_user_id | INT (FK → users) | Related user (optional) |
| related_item_id | INT | Related item ID (optional) |
| message | TEXT | Notification message |
| is_read | BOOLEAN | Read status |
| created_at | TIMESTAMP | Creation timestamp |

**Notification Types:**
- `like` - Someone liked your image
- `comment` - Someone commented on your image
- `friend_request` - New friend request
- `friend_accepted` - Friend request accepted
- `story_view` - Someone viewed your story
- `mention` - Someone mentioned you

---

### 11. **albums** (Gallery Organization)
Organize images into albums.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Album ID |
| user_id | INT (FK → users) | Album owner |
| name | VARCHAR(100) | Album name |
| description | TEXT | Album description |
| cover_image_id | INT (FK → images) | Cover image |
| is_public | BOOLEAN | Public visibility |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

---

### 12. **album_images** (Album Contents)
Links images to albums.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Record ID |
| album_id | INT (FK → albums) | Parent album |
| image_id | INT (FK → images) | Image in album |
| position | INT | Display order |
| added_at | TIMESTAMP | Addition timestamp |

---

### 13. **tags** (Hashtags/Tags)
Global tag dictionary.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Tag ID |
| name | VARCHAR(50) | Tag name (unique) |
| created_at | TIMESTAMP | First use timestamp |

---

### 14. **image_tags** (Image Tagging)
Links tags to images.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Record ID |
| image_id | INT (FK → images) | Tagged image |
| tag_id | INT (FK → tags) | Applied tag |
| created_at | TIMESTAMP | Tag application time |

---

### 15. **user_mentions** (User Mentions)
Track @mentions in content.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Mention ID |
| image_id | INT (FK → images) | Mentioned in image (optional) |
| comment_id | INT (FK → comments) | Mentioned in comment (optional) |
| story_id | INT (FK → stories) | Mentioned in story (optional) |
| mentioned_user_id | INT (FK → users) | User who was mentioned |
| mentioner_user_id | INT (FK → users) | User who mentioned |
| created_at | TIMESTAMP | Mention timestamp |

---

## 🔗 Entity Relationships

```
users (1) ──→ (1) user_profiles
users (1) ──→ (N) images
users (1) ──→ (N) stories
users (1) ──→ (N) albums
users (1) ──→ (N) comments
users (1) ──→ (N) likes
users (1) ──→ (N) notifications

users (N) ←──→ (N) users [friendships]
users (N) ←──→ (N) users [friend_requests]

images (1) ──→ (N) likes
images (1) ──→ (N) comments
images (N) ←──→ (N) tags [image_tags]
images (N) ←──→ (N) albums [album_images]

stories (1) ──→ (N) story_views
stories (1) ──→ (N) user_mentions
```

---

## 📁 Directory Structure

```
public/uploads/
├── images/          # User uploaded images
├── profiles/        # Profile pictures (NEW)
├── stories/         # Story media (NEW)
└── albums/          # Album organization (NEW)
```

---

## 🔐 Privacy & Security Features

### Profile Privacy
- **is_private**: Restricts profile visibility to friends only
- Controls: Stories, Images, Friend list visibility

### Image Privacy
- **is_public**: Per-image privacy control
- Public images appear in gallery
- Private images only visible to friends

### Cascade Deletion
All relationships use `ON DELETE CASCADE`:
- Delete user → deletes all their content
- Delete image → removes all likes/comments
- Delete story → removes all views

---

## 🚀 Key Features Implemented

### 1. Social Networking
- ✅ Friend requests (send/accept/reject)
- ✅ Friend suggestions based on mutual friends
- ✅ Friend list management

### 2. Stories System
- ✅ 24-hour auto-expiring content
- ✅ View tracking
- ✅ Privacy-aware (respects friend/private settings)

### 3. Enhanced Gallery
- ✅ Albums for organization
- ✅ Tags/hashtags
- ✅ Captions and filters
- ✅ Privacy controls per image

### 4. Notifications
- ✅ Real-time activity tracking
- ✅ Multiple notification types
- ✅ Read/unread status

### 5. User Profiles
- ✅ Extended profile information
- ✅ Profile pictures
- ✅ Bio and personal details
- ✅ Privacy settings

### 6. User Mentions
- ✅ @mention support in images, comments, stories
- ✅ Notification on mention

---

## 📊 Statistics & Analytics

Each profile can display:
- Image count
- Friend count
- Total likes received
- Story views
- Album count

---

## 🔄 Automated Tasks

### Story Cleanup (MySQL Event)
```sql
CREATE EVENT cleanup_expired_stories
ON SCHEDULE EVERY 1 HOUR
DO DELETE FROM stories WHERE expires_at < NOW();
```

Automatically deletes expired stories every hour.

---

## 💾 Storage Optimization

### Indexes
All foreign keys are indexed for fast queries:
- `user_id` indexes on all user-related tables
- `created_at` indexes for timeline queries
- Composite indexes for frequently joined tables

### Unique Constraints
- Prevents duplicate friendships
- Prevents duplicate friend requests
- Prevents duplicate likes
- One profile per user

---

## 🛠️ Usage Examples

### Get User Profile with Stats
```php
$profile = new UserProfile();
$profileData = $profile->getByUserId($userId);
$stats = $profile->getStats($userId);
```

### Send Friend Request
```php
$friendship = new Friendship();
$result = $friendship->sendRequest($senderId, $receiverId);
```

### Create Story
```php
$story = new Story();
$story->create($userId, 'story.jpg', 'My story caption');
```

### Get Friend's Stories
```php
$story = new Story();
$stories = $story->getFriendsStories($userId);
```

---

## 📈 Future Enhancements

Potential additions:
- Direct messaging system
- Group chats
- Video support for stories
- Live streaming
- Story reactions
- Story replies
- Image editing filters
- GIF support
- Voice messages

---

## ⚡ Performance Considerations

1. **Indexing**: All foreign keys and frequently queried columns are indexed
2. **Cascade Deletes**: Automatic cleanup prevents orphaned data
3. **Story Cleanup**: Automated event keeps stories table lean
4. **Denormalization**: Friend count, image count cached in profile stats
5. **Pagination**: All list queries support LIMIT/OFFSET

---

## 🎓 Database Diagram

```
┌─────────┐
│  USERS  │
└────┬────┘
     │
     ├──→ user_profiles (1:1)
     ├──→ images (1:N)
     ├──→ stories (1:N)
     ├──→ albums (1:N)
     ├──→ comments (1:N)
     ├──→ likes (1:N)
     ├──→ notifications (1:N)
     └──→ friendships (N:N)
```

---

**Last Updated:** November 24, 2025  
**Database Version:** 2.0 (Enhanced Social Features)
