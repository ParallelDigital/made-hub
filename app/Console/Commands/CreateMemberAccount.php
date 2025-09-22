<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;

class CreateMemberAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:create-account {email : The email address for the new account} {--name= : The name for the account (optional)} {--send-reset : Send password reset email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually create a member account with proper role and membership setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?: 'Member';
        $sendReset = $this->option('send-reset');

        $this->info("Creating member account for: {$email}");

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->warn("User with email {$email} already exists (ID: {$existingUser->id})");
            return Command::FAILURE;
        }

        // Get default membership
        $membership = Membership::firstOrCreate(
            ['name' => 'MEMBERSHIP'],
            [
                'description' => 'Monthly membership with 5 class credits per month',
                'price' => 30.00,
                'duration_days' => 30,
                'class_credits' => 5,
                'unlimited' => false,
                'active' => true,
            ]
        );

        // Create the user account
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('temporary_password_' . time()),
            'role' => 'member',
            'membership_id' => $membership->id,
            'membership_start_date' => now()->toDateString(),
            'membership_end_date' => null, // open-ended membership
            'monthly_credits' => 5,
            'credits_last_refreshed' => now()->startOfMonth()->toDateString(),
        ]);

        $this->info("✅ Created user account:");
        $this->line("   ID: {$user->id}");
        $this->line("   Name: {$user->name}");
        $this->line("   Email: {$user->email}");
        $this->line("   Role: {$user->role}");
        $this->line("   Membership: {$membership->name}");
        $this->line("   Credits: {$user->monthly_credits}");

        // Send password reset if requested
        if ($sendReset) {
            try {
                $status = Password::sendResetLink(['email' => $email]);
                if ($status === Password::RESET_LINK_SENT) {
                    $this->info("📧 Password reset email sent to {$email}");
                } else {
                    $this->error("❌ Failed to send password reset email: {$status}");
                }
            } catch (\Throwable $e) {
                $this->error("❌ Error sending password reset: {$e->getMessage()}");
            }
        } else {
            $this->warn("⚠️  No password reset sent. User will need manual password setup.");
        }

        $this->info("🎉 Member account created successfully!");

        return Command::SUCCESS;
    }
}
