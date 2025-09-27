<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class DebugUserAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:user-account {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug user account creation and password reset issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Debugging account for: {$email}");
        $this->line('');
        
        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->warn("User not found. Creating a test account...");
            
            $user = User::create([
                'name' => 'Test User',
                'email' => $email,
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'email_verified_at' => now(),
            ]);
            
            $this->info("User created with ID: {$user->id}");
        } else {
            $this->info("User found:");
            $this->line("  ID: {$user->id}");
            $this->line("  Name: {$user->name}");
            $this->line("  Email: {$user->email}");
            $this->line("  Email verified: " . ($user->email_verified_at ? 'Yes' : 'No'));
            $this->line("  Created: {$user->created_at}");
        }
        
        $this->line('');
        
        // Test password reset
        $this->info("Testing password reset...");
        
        try {
            $status = Password::sendResetLink(['email' => $email]);
            
            switch ($status) {
                case Password::RESET_LINK_SENT:
                    $this->info("✅ Password reset link sent successfully");
                    break;
                case Password::INVALID_USER:
                    $this->error("❌ Invalid user (this shouldn't happen)");
                    break;
                case Password::RESET_THROTTLED:
                    $this->warn("⚠️ Reset was throttled (too many recent attempts)");
                    break;
                default:
                    $this->error("❌ Unknown status: {$status}");
                    break;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
        
        $this->line('');
        $this->info("Debug complete. Check your email for the password reset link.");
        
        return 0;
    }
}
