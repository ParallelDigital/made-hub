<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClassPassConfirmed;
use Stripe\Stripe;
use Stripe\StripeClient;
use Carbon\Carbon;

class SyncStripeClassPasses extends Command
{
    protected $signature = 'stripe:sync-class-passes {--days=30 : How many days back to check} {--email= : Specific email to sync}';
    protected $description = 'Sync class pass purchases from Stripe that may have been missed by webhooks';

    public function handle()
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $client = new StripeClient(config('services.stripe.secret'));
        } catch (\Exception $e) {
            $this->error('Failed to initialize Stripe client: ' . $e->getMessage());
            return 1;
        }

        $days = $this->option('days');
        $specificEmail = $this->option('email');
        
        $this->info("Syncing class pass purchases from the last {$days} days...");
        
        try {
            // Get completed checkout sessions for one-time payments (class passes)
            $sessions = $client->checkout->sessions->all([
                'limit' => 100,
                'created' => ['gte' => strtotime("-{$days} days")],
                'status' => 'complete',
            ]);

            $processed = 0;
            $created = 0;
            $errors = 0;

            foreach ($sessions->data as $session) {
                // Only process payment mode sessions (not subscriptions)
                if ($session->mode !== 'payment') {
                    continue;
                }

                $email = $session->customer_details->email ?? $session->customer_email ?? null;
                if (!$email) {
                    continue;
                }

                // If specific email provided, only process that one
                if ($specificEmail && strtolower($email) !== strtolower($specificEmail)) {
                    continue;
                }

                $packageType = $session->metadata->package_type ?? null;
                if (!$packageType || !in_array($packageType, ['package_5', 'package_10', 'unlimited'])) {
                    continue;
                }

                $this->line("Processing session {$session->id} for {$email} - {$packageType}");

                try {
                    // Find or create user
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $session->metadata->name ?? 'Guest',
                            'password' => bcrypt('Made2025!'),
                            'role' => 'subscriber',
                            'email_verified_at' => now(),
                        ]
                    );

                    $isNewAccount = $user->wasRecentlyCreated;
                    $password = $isNewAccount ? 'Made2025!' : null;
                    $isMember = $user->hasActiveMembership();

                    // Check if they already have this type of pass from this time period
                    $sessionDate = Carbon::createFromTimestamp($session->created);
                    $existingPass = $user->passes()
                        ->where('source', 'stripe_purchase')
                        ->where('created_at', '>=', $sessionDate->subHours(1))
                        ->where('created_at', '<=', $sessionDate->addHours(1))
                        ->first();

                    if ($existingPass) {
                        $this->line("  → Pass already exists, skipping");
                        continue;
                    }

                    // Allocate the pass
                    $expiresAt = Carbon::createFromTimestamp($session->created)->addMonth();
                    
                    switch ($packageType) {
                        case 'package_5':
                            $user->allocateCreditsWithExpiry(5, $expiresAt, 'stripe_purchase');
                            $this->info("  → Allocated 5 class pass");
                            // Send confirmation email
                            try {
                                Mail::to($user->email)->send(new ClassPassConfirmed($user, 'credits', 5, $expiresAt, 'Stripe Purchase', $isNewAccount, $password, $isMember));
                            } catch (\Throwable $e) { $this->warn("  → Failed to send pass email: " . $e->getMessage()); }
                            break;
                        case 'package_10':
                            $user->allocateCreditsWithExpiry(10, $expiresAt, 'stripe_purchase');
                            $this->info("  → Allocated 10 class pass");
                            try {
                                Mail::to($user->email)->send(new ClassPassConfirmed($user, 'credits', 10, $expiresAt, 'Stripe Purchase', $isNewAccount, $password, $isMember));
                            } catch (\Throwable $e) { $this->warn("  → Failed to send pass email: " . $e->getMessage()); }
                            break;
                        case 'unlimited':
                            $user->activateUnlimitedPass($expiresAt, 'stripe_purchase');
                            $this->info("  → Allocated unlimited pass");
                            try {
                                Mail::to($user->email)->send(new ClassPassConfirmed($user, 'unlimited', null, $expiresAt, 'Stripe Purchase', $isNewAccount, $password, $isMember));
                            } catch (\Throwable $e) { $this->warn("  → Failed to send pass email: " . $e->getMessage()); }
                            break;
                    }

                    $created++;

                    // Log if this was a new account
                    if ($isNewAccount) {
                        $this->line("  → New account created with password: Made2025!");
                    }

                } catch (\Exception $e) {
                    $this->error("  → Error processing session: " . $e->getMessage());
                    $errors++;
                }

                $processed++;
            }

            $this->info("\nSync completed:");
            $this->line("- Sessions processed: {$processed}");
            $this->line("- Passes created: {$created}");
            $this->line("- Errors: {$errors}");

            if ($specificEmail) {
                $user = User::where('email', $specificEmail)->first();
                if ($user) {
                    $passes = $user->passes()->get();
                    $this->info("\nCurrent passes for {$specificEmail}:");
                    foreach ($passes as $pass) {
                        $status = $pass->expires_at && $pass->expires_at->isFuture() ? 'Active' : 'Expired';
                        $this->line("- {$pass->pass_type}: {$pass->credits} credits, expires: {$pass->expires_at}, status: {$status}");
                    }
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Error syncing Stripe sessions: ' . $e->getMessage());
            return 1;
        }
    }
}
