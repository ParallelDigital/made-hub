<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Import models
use App\Models\Booking;
use App\Models\FitnessClass;
use App\Models\User;
use Stripe\StripeClient;
use Carbon\Carbon;

echo "Starting Stripe booking sync...\n";

try {
    // Initialize Stripe
    $stripe = new StripeClient(config('services.stripe.secret'));
    echo "✅ Connected to Stripe\n";

    // Get all checkout sessions
    $sessions = $stripe->checkout->sessions->all(['limit' => 100]);
    echo "Found " . count($sessions->data) . " Stripe sessions\n";

    $created = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($sessions->data as $session) {
        try {
            // Only process paid sessions
            if ($session->payment_status !== 'paid') {
                echo "⏭️  Skipping unpaid session: {$session->id}\n";
                $skipped++;
                continue;
            }

            // Check if booking already exists
            $existing = Booking::where('stripe_session_id', $session->id)->first();
            if ($existing) {
                echo "⏭️  Booking already exists for session: {$session->id}\n";
                $skipped++;
                continue;
            }

            // Get customer info
            $email = $session->customer_details->email ?? $session->customer_email ?? null;
            $name = $session->metadata->name ?? $session->customer_details->name ?? 'Stripe Customer';

            if (!$email) {
                echo "❌ No email found for session: {$session->id}\n";
                $errors++;
                continue;
            }

            // Create or find user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('temporary_password_' . time()),
                ]
            );

            // Use class ID 5 (BUILT BY BARBELL) for all bookings
            $classId = 5;
            $class = FitnessClass::find($classId);
            
            if (!$class) {
                echo "❌ Class {$classId} not found\n";
                $errors++;
                continue;
            }

            // Determine booking date from metadata or use class date
            $selectedDate = $session->metadata->selected_date ?? null;
            if ($selectedDate) {
                $bookingDate = Carbon::parse($selectedDate)->format('Y-m-d');
            } else {
                $bookingDate = Carbon::parse($class->class_date)->format('Y-m-d');
            }

            // Create booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'fitness_class_id' => $classId,
                'booking_date' => $bookingDate,
                'stripe_session_id' => $session->id,
                'status' => 'confirmed',
                'booked_at' => Carbon::createFromTimestamp($session->created),
            ]);

            echo "✅ Created booking #{$booking->id} for {$email} - {$class->name}\n";
            $created++;

        } catch (Exception $e) {
            echo "❌ Error processing session {$session->id}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    echo "\n=== SYNC COMPLETE ===\n";
    echo "✅ Created: {$created}\n";
    echo "⏭️  Skipped: {$skipped}\n";
    echo "❌ Errors: {$errors}\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
