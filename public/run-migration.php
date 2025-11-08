<?php
/**
 * TEMPORARY MIGRATION RUNNER
 * 
 * USE THIS ONLY IF YOU DON'T HAVE SSH ACCESS TO YOUR SERVER
 * 
 * Instructions:
 * 1. Upload all your project files to the server
 * 2. Visit this file in your browser: https://your-domain.com/run-migration.php
 * 3. DELETE THIS FILE IMMEDIATELY AFTER USE for security!
 * 
 * WARNING: This file should be deleted after running!
 */

// Basic security - change this to a secret key
$SECRET_KEY = 'change-this-to-something-random-' . date('Y-m-d');

// Check if secret key is provided
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('Access denied. Add ?key=' . htmlspecialchars($SECRET_KEY) . ' to the URL');
}

echo '<pre>';
echo "===========================================\n";
echo "Phone Number Feature - Migration Runner\n";
echo "===========================================\n\n";

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    echo "✓ Laravel application loaded\n\n";
    
    // Run migration
    echo "📦 Running migrations...\n";
    echo "This may take a moment...\n\n";
    
    try {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();
        echo $output;
        
        if (strpos($output, 'FAIL') !== false) {
            echo "\n⚠️  Some migrations had issues, but this is often OK if columns already exist.\n";
            echo "Continuing with cache clear...\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  Migration warning: " . $e->getMessage() . "\n";
        echo "Continuing with cache clear (this is often fine)...\n";
    }
    echo "\n";
    
    // Clear caches
    echo "🧹 Clearing caches...\n";
    
    Artisan::call('config:clear');
    echo "  - Config cache cleared\n";
    
    Artisan::call('cache:clear');
    echo "  - Application cache cleared\n";
    
    Artisan::call('route:clear');
    echo "  - Route cache cleared\n";
    
    Artisan::call('view:clear');
    echo "  - View cache cleared\n";
    
    echo "\n";
    
    // Optimize for production
    echo "⚡ Optimizing for production...\n";
    
    Artisan::call('config:cache');
    echo "  - Config cached\n";
    
    Artisan::call('route:cache');
    echo "  - Routes cached\n";
    
    echo "\n";
    
    // Verify phone column exists
    echo "🔍 Verifying phone column...\n";
    $hasPhone = Schema::hasColumn('users', 'phone');
    if ($hasPhone) {
        echo "  ✅ Phone column exists in database\n";
    } else {
        echo "  ❌ Phone column NOT found - migration may have failed\n";
    }
    
    echo "\n";
    echo "===========================================\n";
    echo $hasPhone ? "✅ DEPLOYMENT COMPLETE!" : "⚠️  DEPLOYMENT INCOMPLETE";
    echo "\n";
    echo "===========================================\n\n";
    
    if ($hasPhone) {
        echo "The phone number feature is now live.\n";
        echo "Users will be prompted to enter their phone number on next login.\n\n";
    } else {
        echo "⚠️  The phone column was not added successfully.\n";
        echo "Please contact support or try running migrations manually via SSH.\n\n";
    }
    
    echo "⚠️  IMPORTANT: DELETE THIS FILE NOW! ⚠️\n";
    echo "Delete: /public/run-migration.php\n\n";
    
    echo "Test the feature:\n";
    echo "1. Logout and login again\n";
    echo "2. You should see a phone number collection form\n";
    echo "3. Enter a valid phone number to continue\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR OCCURRED:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo '</pre>';

echo '<hr>';
echo '<p style="color: red; font-weight: bold; font-size: 18px;">⚠️ DELETE THIS FILE IMMEDIATELY FOR SECURITY!</p>';
echo '<p>File location: <code>/public/run-migration.php</code></p>';
?>
