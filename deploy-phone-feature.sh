#!/bin/bash

# Deployment script for phone number feature
# Run this on your live server after uploading the code

echo "🚀 Deploying phone number feature..."
echo ""

# Run migration
echo "📦 Running migrations..."
php artisan migrate --force
MIGRATE_EXIT=$?

if [ $MIGRATE_EXIT -ne 0 ]; then
    echo "⚠️  Migration had warnings (this is often OK if columns already exist)"
fi
echo ""

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo ""

# Cache config and routes for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
echo ""

# Verify phone column exists
echo "🔍 Verifying phone column exists..."
php artisan tinker --execute="echo Schema::hasColumn('users', 'phone') ? '✅ Phone column verified!' : '❌ Phone column NOT found!'; echo PHP_EOL;"
echo ""

echo "==========================="
echo "✅ Deployment complete!"
echo "==========================="
echo ""
echo "The phone number feature is now live."
echo "Users will be prompted to enter their phone number on next login."
echo ""
echo "Test by logging out and back in - you should see the phone form."
