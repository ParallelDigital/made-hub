#!/bin/bash

# Deployment script for phone number feature
# Run this on your live server after uploading the code

echo "🚀 Deploying phone number feature..."

# Run migration
echo "📦 Running migration..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache config and routes for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache

echo "✅ Deployment complete!"
echo ""
echo "The phone number feature is now live."
echo "Users will be prompted to enter their phone number on next login."
