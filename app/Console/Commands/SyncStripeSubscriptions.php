<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class SyncStripeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:sync-stripe-subscriptions {--create-missing : Create user accounts for Stripe subscriptions that don\'t exist locally}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Stripe subscription data with local user accounts, ensuring all subscribers have proper accounts';

    /**
     * Execute the console command.
     */
     public function handle()
     {
         $this->info('🔄 Syncing Stripe subscriptions with local user accounts...');

         try {
             $secret = config('services.stripe.secret');
             if (!$secret) {
                 $this->error('❌ Stripe secret key not configured');
                 return Command::FAILURE;
             }

             $stripe = new \Stripe\StripeClient($secret);
             $this->info('📡 Connected to Stripe API');

             // Fetch all active subscriptions from Stripe
             $allSubs = collect();
             $params = ['limit' => 100, 'expand' => ['data.customer'], 'status' => 'all'];
             do {
                 $resp = $stripe->subscriptions->all($params);
                 $data = collect($resp->data ?? []);
                 $allSubs = $allSubs->merge($data);
                 if (($resp->has_more ?? false) && $data->isNotEmpty()) {
                     $params['starting_after'] = $data->last()->id;
                 } else {
                     break;
                 }
             } while (true);

             $this->info("Found {$allSubs->count()} subscriptions in Stripe");

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

             $processed = 0;
             $created = 0;
             $updated = 0;
             $skipped = 0;

             foreach ($allSubs as $subscription) {
                 $customer = $subscription->customer;
                 if (!is_object($customer) || empty($customer->email)) {
                     $this->warn("⚠️  Skipping subscription {$subscription->id} - no customer email");
                     $skipped++;
                     continue;
                 }

                 $email = $customer->email;
                 $name = $customer->name ?? 'Member';
                 $status = $subscription->status;

                 // Find or create user account
                 $user = User::where('email', $email)->first();

                 if (!$user) {
                     if ($this->option('create-missing')) {
                         // Create new user account
                         $user = User::create([
                             'name' => $name,
                             'email' => $email,
                             'password' => bcrypt('temporary_password_' . time()),
                             'role' => 'member',
                             'stripe_customer_id' => $customer->id,
                             'stripe_subscription_id' => $subscription->id,
                             'subscription_status' => $status,
                             'membership_id' => $membership->id,
                             'membership_start_date' => now()->toDateString(),
                             'membership_end_date' => null, // open-ended for active subscriptions
                             'monthly_credits' => 5,
                             'credits_last_refreshed' => now()->startOfMonth()->toDateString(),
                         ]);

                         // Send password reset email
                         try {
                             Password::sendResetLink(['email' => $email]);
                             $this->line("✅ Created user account for {$email} and sent password reset");
                         } catch (\Throwable $e) {
                             $this->error("❌ Created user for {$email} but failed to send password reset: {$e->getMessage()}");
                             Log::error('Failed to send password reset for new Stripe subscriber', [
                                 'user_id' => $user->id,
                                 'email' => $email,
                                 'error' => $e->getMessage()
                             ]);
                         }

                         $created++;
                     } else {
                         $this->warn("⚠️  User {$email} not found locally, use --create-missing to create account");
                         $skipped++;
                         continue;
                     }
                 } else {
                     // Update existing user with subscription data
                     $needsUpdate = false;

                     if ($user->role !== 'member') {
                         $user->role = 'member';
                         $needsUpdate = true;
                     }

                     if (!$user->stripe_customer_id) {
                         $user->stripe_customer_id = $customer->id;
                         $needsUpdate = true;
                     }

                     if (!$user->stripe_subscription_id) {
                         $user->stripe_subscription_id = $subscription->id;
                         $needsUpdate = true;
                     }

                     if ($user->subscription_status !== $status) {
                         $user->subscription_status = $status;
                         $needsUpdate = true;
                     }

                     if (!$user->membership_id) {
                         $user->membership_id = $membership->id;
                         $user->membership_start_date = $user->membership_start_date ?: now()->toDateString();
                         $user->membership_end_date = null;
                         $user->monthly_credits = 5;
                         $user->credits_last_refreshed = now()->startOfMonth()->toDateString();
                         $needsUpdate = true;
                     }

                     if ($needsUpdate) {
                         $user->save();
                         $this->line("🔄 Updated user {$email} with subscription data");
                         $updated++;
                     } else {
                         $this->line("✅ User {$email} already properly configured");
                     }
                 }

                 $processed++;
             }

             $this->info("\n🎉 Sync completed!");
             $this->info("📊 Processed: {$processed} subscriptions");
             $this->info("👤 Created: {$created} new user accounts");
             $this->info("🔄 Updated: {$updated} existing accounts");
             $this->info("⚠️  Skipped: {$skipped} subscriptions");

             if ($created > 0) {
                 $this->info("📧 Password reset emails sent to {$created} new users");
             }

             return Command::SUCCESS;

         } catch (\Throwable $e) {
             $this->error("❌ Sync failed: {$e->getMessage()}");
             Log::error('Stripe subscription sync failed', [
                 'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString()
             ]);
             return Command::FAILURE;
         }
     }
}
