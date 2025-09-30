<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPass;
use App\Models\PackageCode;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DebugClassPassIssue extends Command
{
    protected $signature = 'debug:class-pass-issue {email}';
    protected $description = 'Debug class pass issues for a specific user';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Debugging class pass issue for: {$email}");
        
        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found in database.");
            
            // Check if there are any PackageCode records for this email
            $packageCodes = PackageCode::where('email', $email)->get();
            if ($packageCodes->count() > 0) {
                $this->info("Found package codes for this email:");
                foreach ($packageCodes as $code) {
                    $this->line("- Code: {$code->code}, Type: {$code->package_type}, Redeemed: {$code->redeemed_at}");
                }
            } else {
                $this->warn("No package codes found for this email either.");
            }
            
            // Create the user for testing
            $this->info("Creating user for testing...");
            $user = User::create([
                'name' => 'Test User for Class Pass',
                'email' => $email,
                'password' => bcrypt('temporary_password'),
                'email_verified_at' => now(),
            ]);
            $this->info("User created with ID: {$user->id}");
        } else {
            $this->info("User found: {$user->name} (ID: {$user->id})");
        }
        
        // Check user passes
        $passes = $user->passes()->get();
        $this->info("Current passes count: {$passes->count()}");
        
        foreach ($passes as $pass) {
            $status = $pass->expires_at && $pass->expires_at->isFuture() ? 'Active' : 'Expired';
            $this->line("- {$pass->pass_type}: {$pass->credits} credits, expires: {$pass->expires_at}, status: {$status}, source: {$pass->source}");
        }
        
        // Test the methods used by the admin view
        $this->info("\nTesting admin view logic:");
        $this->line("Has active unlimited pass: " . ($user->hasActiveUnlimitedPass() ? 'Yes' : 'No'));
        $this->line("Non-member available credits: " . $user->getNonMemberAvailableCredits());
        
        // Simulate giving them an unlimited pass
        $this->info("\nSimulating unlimited pass purchase...");
        try {
            $user->activateUnlimitedPass(now()->addMonth(), 'debug_test');
            $this->info("Unlimited pass activated successfully!");
            
            // Re-check
            $this->line("Has active unlimited pass now: " . ($user->hasActiveUnlimitedPass() ? 'Yes' : 'No'));
            
            // Check if they would show up in admin query
            $query = User::query()
                ->with(['passes' => function ($q) {
                    $q->orderBy('expires_at', 'desc');
                }])
                ->whereHas('passes');
            
            $adminUsers = $query->get();
            $foundInAdmin = $adminUsers->contains('id', $user->id);
            $this->line("Would show up in admin class passes view: " . ($foundInAdmin ? 'Yes' : 'No'));
            
        } catch (\Exception $e) {
            $this->error("Error activating unlimited pass: " . $e->getMessage());
        }
        
        return 0;
    }
}
