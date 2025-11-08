# Deploy Phone Number Feature to Live Server

## What This Feature Does
- Forces all users to provide a phone number upon login
- Validates phone numbers with regex
- Blocks access to the application until phone is provided

## Files Changed/Added

### Database
- `database/migrations/2025_11_08_114715_add_phone_to_users_table.php` - Adds phone column

### Controllers
- `app/Http/Controllers/Auth/CompletePhoneController.php` - Handles phone collection

### Middleware
- `app/Http/Middleware/RequirePhoneNumber.php` - Enforces phone requirement

### Views
- `resources/views/auth/complete-phone.blade.php` - Phone collection form

### Configuration
- `bootstrap/app.php` - Registers middleware
- `routes/web.php` - Adds routes and applies middleware

### Models
- `app/Models/User.php` - Added phone to fillable

## Deployment Steps

### Option 1: Using the Deployment Script (Recommended)

1. **Upload all files** to your live server (via FTP, Git, etc.)

2. **SSH into your server:**
   ```bash
   ssh user@your-server.com
   cd /path/to/made-hub
   ```

3. **Run the deployment script:**
   ```bash
   bash deploy-phone-feature.sh
   ```

### Option 2: Manual Deployment

1. **Upload all files** to your live server

2. **SSH into your server:**
   ```bash
   ssh user@your-server.com
   cd /path/to/made-hub
   ```

3. **Run these commands one by one:**
   ```bash
   # Run migration
   php artisan migrate --force
   
   # Clear all caches
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   
   # Optimize for production
   php artisan config:cache
   php artisan route:cache
   ```

### Option 3: If You Can't SSH (cPanel/Web Hosting)

1. **Upload all files via FTP/File Manager**

2. **Create a file called `run-migration.php` in your public folder:**
   ```php
   <?php
   // Temporary migration runner - DELETE THIS FILE AFTER USE!
   
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   
   echo "Running migration...\n";
   Artisan::call('migrate', ['--force' => true]);
   echo Artisan::output();
   
   echo "\nClearing caches...\n";
   Artisan::call('config:clear');
   Artisan::call('cache:clear');
   Artisan::call('route:clear');
   Artisan::call('view:clear');
   
   echo "\nOptimizing...\n";
   Artisan::call('config:cache');
   Artisan::call('route:cache');
   
   echo "\n✅ Done! DELETE THIS FILE NOW!\n";
   ```

3. **Visit:** `https://your-domain.com/run-migration.php`

4. **IMPORTANT:** Delete `run-migration.php` immediately after running!

## Verification

After deployment, test the feature:

1. Login with any user account
2. If they don't have a phone number, they should see the phone collection modal
3. Try entering an invalid phone (e.g., "abc123") - should show error
4. Enter a valid phone (e.g., "07810 023177") - should save and redirect to dashboard
5. Logout and login again - should NOT see the modal (phone already saved)

## Rollback (If Needed)

If something goes wrong, you can rollback:

```bash
php artisan migrate:rollback --step=1
php artisan config:clear
php artisan cache:clear
```

## Troubleshooting

### "no such column: phone" Error
- Make sure migration ran: `php artisan migrate --force`
- Clear config cache: `php artisan config:clear`
- Check database: Phone column should exist in users table

### Redirect Loop
- Clear route cache: `php artisan route:clear`
- Clear view cache: `php artisan view:clear`
- Check that complete-phone routes are NOT protected by require.phone middleware

### Phone Validation Not Working
- Clear view cache: `php artisan view:clear`
- Check browser console for JavaScript errors

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs
3. Verify all files were uploaded correctly
4. Ensure file permissions are correct (755 for directories, 644 for files)
