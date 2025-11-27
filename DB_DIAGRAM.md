# Camagru Database Diagram (text)

```
Users
- id (PK)
- username (UNIQUE)
- email (UNIQUE)
- password (hashed)
- verified (boolean)
- verification_token
- email_notifications (boolean)
- bio (text, optional)
- avatar (varchar, optional, path to upload)
- created_at (timestamp)

Images
- id (PK)
- user_id (FK -> Users.id)
- filename
- created_at

Comments
- id (PK)
- image_id (FK -> Images.id)
- user_id (FK -> Users.id)
- comment (text)
- created_at

Likes
- id (PK)
- image_id (FK -> Images.id)
- user_id (FK -> Users.id)
- created_at
- UNIQUE (image_id, user_id)
```
