<?php

/**
 * Script to update all member passwords to Made2025!
 * Run this from the command line: php database/update_member_passwords.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "===========================================\n";
echo "Updating Member Passwords\n";
echo "===========================================\n\n";

// Get all users
$users = User::orderBy('name')->get();

echo "Total users in database: " . $users->count() . "\n\n";

$newPassword = 'Made2025!';
$hashedPassword = Hash::make($newPassword);

$updatedCount = 0;
$skippedAdmins = 0;
$skippedInstructors = 0;

foreach ($users as $user) {
    // Skip admin and instructor accounts
    if (in_array($user->role, ['admin', 'administrator'])) {
        echo "⏭️  Skipping admin: {$user->name} ({$user->email})\n";
        $skippedAdmins++;
        continue;
    }
    
    if ($user->role === 'instructor') {
        echo "⏭️  Skipping instructor: {$user->name} ({$user->email})\n";
        $skippedInstructors++;
        continue;
    }
    
    // Update regular user/member password
    $user->password = $hashedPassword;
    $user->save();
    
    $memberStatus = $user->membership_start_date ? '✅ Member' : '👤 User';
    echo "✓ Updated: {$user->name} ({$user->email}) - {$memberStatus}\n";
    $updatedCount++;
}

echo "\n===========================================\n";
echo "Summary:\n";
echo "===========================================\n";
echo "✅ Updated: {$updatedCount} users\n";
echo "⏭️  Skipped admins: {$skippedAdmins}\n";
echo "⏭️  Skipped instructors: {$skippedInstructors}\n";
echo "\nNew password for all members: {$newPassword}\n";
echo "===========================================\n";
