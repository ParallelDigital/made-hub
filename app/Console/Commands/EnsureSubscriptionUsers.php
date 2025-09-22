<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class EnsureSubscriptionUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:ensure-subscription-users {--create-missing : Create users for subscriptions that don\'t have user accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all subscription holders have proper user accounts with login access';

    /**
     * Execute the console command.
     */
     public function handle()
     {
         $this->info('🔍 Ensuring all subscription holders have proper user accounts...');

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

         // Find users with Stripe subscriptions
         $subscriptionUsers = User::whereNotNull('stripe_subscription_id')->get();
         $this->info("Found {$subscriptionUsers->count()} users with Stripe subscriptions");

        $processed = 0;
        $fixed = 0;
        $created = 0;

        foreach ($subscriptionUsers as $user) {
            $needsFix = false;
            $issues = [];

            // Check if user has membership
            if (!$user->membership_id) {
                $user->membership_id = $membership->id;
                $needsFix = true;
                $issues[] = 'missing membership';
            }

            // Check if user has membership start date
            if (!$user->membership_start_date) {
                $user->membership_start_date = now()->toDateString();
                $needsFix = true;
                $issues[] = 'missing start date';
            }

            // Check if membership is active (not ended)
            if ($user->membership_end_date && $user->membership_end_date < now()) {
                // If subscription is active but membership ended, extend it
                if ($user->subscription_status === 'active') {
                    $user->membership_end_date = null; // Make it open-ended
                    $needsFix = true;
                    $issues[] = 'extended expired membership for active subscription';
                }
            }

            // Check if user has temporary password
            if (str_starts_with($user->password, '$2y$10$temporary_password_')) {
                // Send password reset email
                try {
                    $status = Password::sendResetLink(['email' => $user->email]);
                    if ($status === Password::RESET_LINK_SENT) {
                        $this->line("📧 Password reset sent to {$user->email}");
                    } else {
                        $this->error("❌ Failed to send password reset to {$user->email}: {$status}");
                    }
                } catch (\Throwable $e) {
                    $this->error("❌ Error sending password reset to {$user->email}: {$e->getMessage()}");
                    Log::error('Failed to send password reset for subscription user', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
                $needsFix = true;
                $issues[] = 'temporary password reset sent';
            }

            // Check if user needs monthly credits refresh
            if (!$user->monthly_credits || $user->monthly_credits < 5) {
                $user->monthly_credits = 5;
                $user->credits_last_refreshed = now()->startOfMonth()->toDateString();
                $needsFix = true;
                $issues[] = 'refreshed monthly credits';
            }

            if ($needsFix) {
                $user->save();
                $fixed++;
                $this->line("✅ Fixed user {$user->email}: " . implode(', ', $issues));
            } else {
                $this->line("✅ User {$user->email} is properly configured");
            }

            $processed++;
        }

        // Optional: Check for subscriptions that don't have user accounts (if --create-missing flag is used)
        if ($this->option('create-missing')) {
            $this->info('\n🔍 Checking for subscriptions without user accounts...');
            // Note: This would require Stripe API integration to get subscription data
            // For now, we'll skip this as our webhook system should prevent this scenario
            $this->info('Note: Subscription creation without user accounts should be handled by webhooks');
        }

        $this->info("\n🎉 Processed {$processed} subscription users");
        $this->info("🔧 Fixed {$fixed} users with account issues");
        $this->info("📧 Password reset emails sent where needed");

         return Command::SUCCESS;
     }
}
