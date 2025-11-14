# Deploy Roster Email Feature - UPDATED FIX

## Files Changed (UPLOAD THESE TO YOUR LIVE SERVER)
The following files need to be uploaded to your live server:

1. **app/Http/Controllers/Admin/FitnessClassController.php**
   - Added missing imports (Mail, Log, InstructorClassRoster)
   - Added sendRosterEmail() method with proper email sending
   - Added detailed logging
   - **FIXED: Email now sends immediately, not queued**

2. **resources/views/admin/classes/show.blade.php**
   - Added "Send Roster Email" button
   - Added email input modal
   - Added JavaScript functions
   - **FIXED: Form now properly captures and submits email address**

3. **routes/web.php**
   - Added route: `admin.classes.send-roster`

## IMPORTANT: What Was Fixed
- **Email input was not being submitted with the form** - Now uses hidden input and onsubmit handler
- **Email sending matches the working pattern** used in BookingController
- Form validation added to ensure email is not empty

## Deployment Steps

### Option 1: Using FTP/SFTP (Recommended)
1. Connect to your live server via FTP/SFTP
2. Upload these 3 files to their exact locations:
   - `app/Http/Controllers/Admin/FitnessClassController.php`
   - `resources/views/admin/classes/show.blade.php`
   - `routes/web.php`

3. SSH into your server and run:
   ```bash
   cd /path/to/your/live/site
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan route:cache
   php artisan config:cache
   ```

### Option 2: Using Git (If you have Git setup)
1. Commit the changes:
   ```bash
   git add app/Http/Controllers/Admin/FitnessClassController.php
   git add resources/views/admin/classes/show.blade.php
   git add routes/web.php
   git commit -m "Add roster email feature with custom email input"
   git push
   ```

2. SSH into your live server:
   ```bash
   cd /path/to/your/live/site
   git pull
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan route:cache
   php artisan config:cache
   ```

### Option 3: Quick Deploy Script
Run this on your live server after uploading the files:

```bash
#!/bin/bash
echo "🚀 Deploying roster email feature..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan route:cache
php artisan config:cache
echo "✅ Deployment complete!"
```

## Testing After Deployment

1. Go to: https://gym.made-reg.co.uk/admin/classes/72
2. Click "Send Roster Email" button
3. Enter your email address
4. Click "Send Email"
5. Check your email inbox

## Troubleshooting

If email still doesn't send, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Look for entries with "Attempting to send roster email"
3. Check your .env file has correct MAIL_* settings

The logs will now show detailed information about what's happening.
