# Testing Unverified User Login Flow

## What Was Changed

When an unverified user tries to login, instead of just showing an error message, the system now:
1. Generates a new OTP code
2. Sends it via email (or displays it if email is not configured)
3. Redirects the user to the OTP verification page
4. Allows the user to complete verification without re-registering

## Test Scenarios

### Scenario 1: Register and Don't Verify
1. Go to `http://localhost:8080/index.php?page=register`
2. Register with:
   - Username: `testuser1`
   - Email: `test1@example.com`
   - Password: `Password123`
3. You'll be shown an OTP code (since email isn't configured)
4. **DON'T enter the code** - just close the page or navigate away
5. The user is now registered but unverified

### Scenario 2: Try to Login as Unverified User
1. Go to `http://localhost:8080/index.php?page=login`
2. Try to login with:
   - Username/Email: `testuser1` (or `test1@example.com`)
   - Password: `Password123`
3. **Expected Result**: 
   - You should be redirected to the OTP verification page
   - A NEW OTP code will be generated and displayed
   - The email field will be pre-filled
   - You can now enter the OTP code to verify your account

### Scenario 3: Verify the Account
1. After being redirected from login (Scenario 2), you'll see:
   - Email pre-filled
   - A 6-digit verification code displayed (if email not configured)
   - Input field for the OTP code
2. Enter the 6-digit code shown
3. Click "Verify Email"
4. **Expected Result**:
   - Success message: "Email verified successfully! You can now log in."
   - Redirected to login page
   - Now you CAN login successfully

### Scenario 4: Resend OTP Code
1. While on the OTP verification page
2. Click the "Resend Code" button at the bottom
3. **Expected Result**:
   - A new OTP code is generated
   - Message: "A new verification code has been sent to your email"
   - The new code is displayed (if email not configured)
   - You can use the new code to verify

### Scenario 5: Login as Verified User
1. After verifying (Scenario 3), go to login page
2. Login with the same credentials
3. **Expected Result**:
   - Login successful
   - Redirected to the create page (canvas)
   - User is now fully authenticated

## Files Changed

1. **src/controllers/AuthController.php**
   - Modified `login()` method to detect unverified users
   - Generates new OTP and redirects to verification page
   - Sends email or displays code on screen

2. **src/views/header.php**
   - Added support for warning alerts (yellow/orange)
   - Displays HTML-formatted messages (for showing OTP codes)

3. **public/css/style.css**
   - Added `.alert-warning` styles
   - Yellow/orange theme matching the warning color

4. **src/models/User.php**
   - Enhanced `create()` method with better error logging
   - Checks username/email existence before inserting

5. **migrate.php**
   - Added check and creation of `otp_expiry` column
   - Ensures database schema is complete

## Verification Checklist

- [ ] Unverified users are redirected to OTP page when trying to login
- [ ] New OTP code is generated on login attempt
- [ ] OTP code is displayed on screen (when email not configured)
- [ ] Email field is pre-filled on OTP verification page
- [ ] Resend button generates and displays new OTP
- [ ] After verification, user can login successfully
- [ ] Warning messages display in yellow/orange style
- [ ] Success messages display in green style
- [ ] Error messages display in red style

## Database Check

To verify the database has the correct structure:

```bash
docker compose exec db mysql -u camagru_user -pcamagru_pass camagru -e "DESCRIBE users;"
```

Expected columns:
- `otp_expiry` (TIMESTAMP) ✅
- `verification_token` (VARCHAR) ✅
- `verified` (TINYINT) ✅

## Notes

- OTP codes expire after 15 minutes for security
- Users can request new OTP codes unlimited times
- Once verified, users won't see the OTP page again
- Email configuration is optional - codes display on screen if email fails
