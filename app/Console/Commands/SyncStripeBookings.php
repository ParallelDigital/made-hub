<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\FitnessClass;
use App\Models\User;
use Illuminate\Console\Command;
use Stripe\StripeClient;
use Carbon\Carbon;

class SyncStripeBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:sync-stripe {--days=7 : Number of days to look back for Stripe sessions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Stripe checkout sessions with local bookings database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Stripe booking sync...');

        $days = (int) $this->option('days');
        $this->info("Looking back {$days} days for Stripe sessions");

        try {
            $stripe = new StripeClient($this->getStripeSecret());
            $this->info('Connected to Stripe successfully');
        } catch (\Exception $e) {
            $this->error('Failed to connect to Stripe: ' . $e->getMessage());
            return 1;
        }

        $syncedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        try {
            // Get checkout sessions from Stripe
            $sessions = $stripe->checkout->sessions->all([
                'limit' => 100,
                'created' => [
                    'gte' => Carbon::now()->subDays($days)->timestamp,
                ],
            ]);

            $this->info("Found " . count($sessions->data) . " Stripe checkout sessions");

            foreach ($sessions->data as $session) {
                try {
                    $result = $this->processStripeSession($session);
                    switch ($result) {
                        case 'synced':
                            $syncedCount++;
                            break;
                        case 'skipped':
                            $skippedCount++;
                            break;
                        case 'error':
                            $errorCount++;
                            break;
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing session {$session->id}: " . $e->getMessage());
                    $errorCount++;
                }
            }

        } catch (\Exception $e) {
            $this->error('Failed to retrieve Stripe sessions: ' . $e->getMessage());
            return 1;
        }

        $this->info('Stripe booking sync completed!');
        $this->info("Synced: {$syncedCount} | Skipped: {$skippedCount} | Errors: {$errorCount}");

        return 0;
    }

    /**
     * Process a single Stripe checkout session
     */
    private function processStripeSession($session): string
    {
        $this->info("Processing session: {$session->id}");

        // Only process paid sessions
        if ($session->payment_status !== 'paid') {
            $this->warn("Session {$session->id} not paid (status: {$session->payment_status})");
            return 'skipped';
        }

        // Check if we already have this booking
        $existingBooking = Booking::where('stripe_session_id', $session->id)->first();
        if ($existingBooking) {
            $this->warn("Session {$session->id} already has booking #{$existingBooking->id}");
            return 'skipped';
        }

        // Extract class ID from metadata or fallback to price-based detection
        $classId = $session->metadata->class_id ?? null;
        if (!$classId) {
            // Try alternative metadata keys
            $classId = $session->metadata->classId ?? $session->metadata->class ?? null;
        }

        // Fallback: Use price to determine class for existing bookings without metadata
        if (!$classId) {
            $amount = $session->amount_total / 100; // Convert from cents to pounds
            $classId = $this->getClassIdByPrice($amount);
            if ($classId) {
                $this->info("Using price-based class detection for session {$session->id}: £{$amount} -> Class {$classId}");
            }
        }

        if (!$classId) {
            $this->warn("No class_id found in session {$session->id} metadata and no matching class found by price");
            return 'error';
        }

        // Verify class exists
        $class = FitnessClass::find($classId);
        if (!$class) {
            $this->warn("Class {$classId} not found for session {$session->id}");
            return 'error';
        }

        $this->info("Class verified: {$class->name} (ID: {$class->id})");

        // Get customer email
        $email = $session->customer_details->email ?? $session->customer_email ?? null;
        $name = $session->metadata->name ?? $session->customer_details->name ?? 'Guest';

        $this->info("Customer info - Email: {$email}, Name: {$name}");

        if (!$email) {
            $this->warn("No email found for session {$session->id}");
            return 'error';
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt('temporary_password_' . time()),
            ]
        );

        $this->info("User processed - ID: {$user->id}, Email: {$user->email}");

        // Check for existing booking by Stripe session ID first
        $existingBySession = Booking::where('stripe_session_id', $session->id)->first();
        if ($existingBySession) {
            $this->warn("Session {$session->id} already has booking #{$existingBySession->id}");
            return 'skipped';
        }

        // Check for existing booking by user and class (prevent duplicates)
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('fitness_class_id', $classId)
            ->whereNull('stripe_session_id')
            ->first();

        if ($existingBooking) {
            // Update with Stripe session ID if not present
            $existingBooking->update(['stripe_session_id' => $session->id]);
            $this->info("Updated existing booking #{$existingBooking->id} with Stripe session ID");
            return 'skipped';
        }

        $this->info("No existing booking found, proceeding with creation");

        // Create booking even if class is full (for historical Stripe data)
        $currentBookings = Booking::where('fitness_class_id', $classId)->count();
        if ($currentBookings >= $class->max_spots) {
            $this->warn("Class {$classId} is full but creating booking anyway for Stripe session {$session->id}");
        }

        $this->info("Creating new booking for Stripe session...");

        // Determine booking date from metadata or use class date
        $selectedDate = $session->metadata->selected_date ?? null;
        if ($selectedDate) {
            $bookingDate = Carbon::parse($selectedDate)->format('Y-m-d');
            $this->info("Using selected_date from metadata: {$bookingDate}");
        } else {
            $bookingDate = Carbon::parse($class->class_date)->format('Y-m-d');
            $this->info("No selected_date in metadata, using class_date: {$bookingDate}");
        }

        // Create booking
        try {
            $booking = Booking::create([
                'user_id' => $user->id,
                'fitness_class_id' => $classId,
                'booking_date' => $bookingDate,
                'stripe_session_id' => $session->id,
                'status' => 'confirmed',
                'booked_at' => Carbon::createFromTimestamp($session->created),
            ]);

            $this->info("✅ Created booking #{$booking->id} for {$user->email} - {$class->name} on {$bookingDate}");

            return 'synced';
        } catch (\Exception $e) {
            $this->error("❌ Failed to create booking for session {$session->id}: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Get class ID by price (fallback for sessions without metadata)
     */
    private function getClassIdByPrice(float $amount): ?int
    {
        // Get all classes with the matching price
        $classes = FitnessClass::where('price', $amount)->get();

        if ($classes->count() === 1) {
            // Only one class with this price
            return $classes->first()->id;
        } elseif ($classes->count() > 1) {
            // Multiple classes with same price - use first one (can be made configurable)
            $this->warn("Multiple classes found with price £{$amount}, using first one");
            return $classes->first()->id;
        }

        // No class found with this price
        return null;
    }

    /**
     * Get Stripe secret key
     */
    private function getStripeSecret(): string
    {
        $secret = config('services.stripe.secret');
        if (!$secret) {
            $secret = env('STRIPE_SECRET');
        }
        if (!$secret) {
            throw new \Exception('Stripe secret not configured.');
        }
        return $secret;
    }
}
