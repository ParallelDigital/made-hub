<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\FitnessClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Stripe\StripeClient;

class BookingController extends Controller
{
    public function bookWithCredits(Request $request, $classId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to book with credits.'], 401);
        }

        $user = Auth::user();
        
        $class = FitnessClass::findOrFail($classId);

        // Check if the class has already started
        $classStart = \Carbon\Carbon::parse($class->class_date->toDateString() . ' ' . $class->start_time);
        if ($classStart->isPast()) {
            return response()->json(['success' => false, 'message' => 'This class has already started.'], 400);
        }

        // Members-only classes: require active membership and do NOT deduct credits or payment
        if ($class->members_only) {
            if (!$user->hasActiveMembership()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This class is for members only. Become a member to attend.'
                ], 403);
            }

            // Check if class is full
            $currentBookings = Booking::where('fitness_class_id', $classId)->count();
            if ($currentBookings >= $class->max_spots) {
                return response()->json(['success' => false, 'message' => 'This class is fully booked.'], 400);
            }

            // Check if user already booked this class
            $existingBooking = Booking::where('user_id', $user->id)
                ->where('fitness_class_id', $classId)
                ->first();
            if ($existingBooking) {
                return response()->json(['success' => false, 'message' => 'You have already booked this class.'], 400);
            }

            // Create booking WITHOUT deducting credits
            $booking = Booking::create([
                'user_id' => $user->id,
                'fitness_class_id' => $classId,
                'status' => 'confirmed',
                'booked_at' => now(),
            ]);

            // Send confirmation email (best-effort)
            try {
                Mail::to($user->email)->send(new \App\Mail\BookingConfirmed($booking));
                \Log::info('Booking confirmation email sent successfully for booking ID: ' . $booking->id);
            } catch (\Exception $e) {
                \Log::error('Failed to send booking confirmation email', [
                    'user_id' => $user->id,
                    'booking_id' => $booking->id ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Class booked successfully. This class is free for members.',
                'redirect_url' => route('booking.confirmation', ['classId' => $classId]),
                'booking_id' => $booking->id ?? null,
            ]);
        }

        // If user has an active unlimited pass, allow booking without deducting credits
        if ($user->hasActiveUnlimitedPass()) {
            // Check if class is full
            $currentBookings = Booking::where('fitness_class_id', $classId)->count();
            if ($currentBookings >= $class->max_spots) {
                return response()->json(['success' => false, 'message' => 'This class is fully booked.'], 400);
            }

            // Check if user already booked this class
            $existingBooking = Booking::where('user_id', $user->id)
                ->where('fitness_class_id', $classId)
                ->first();
            if ($existingBooking) {
                return response()->json(['success' => false, 'message' => 'You have already booked this class.'], 400);
            }

            // Create booking WITHOUT deducting any credits
            $booking = Booking::create([
                'user_id' => $user->id,
                'fitness_class_id' => $classId,
                'status' => 'confirmed',
                'booked_at' => now(),
            ]);

            // Send confirmation email (best-effort)
            try {
                Mail::to($user->email)->send(new \App\Mail\BookingConfirmed($booking));
            } catch (\Exception $e) {
                \Log::error('Failed to send booking confirmation email for unlimited pass user', [
                    'user_id' => $user->id,
                    'booking_id' => $booking->id ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Class booked successfully using your unlimited pass! No credits deducted.',
                'redirect_url' => route('booking.confirmation', ['classId' => $classId]),
                'booking_id' => $booking->id ?? null,
            ]);
        }

        // Standard classes: Check credits (consider non-member credits with expiry)
        $availableCredits = $user->hasActiveMembership()
            ? $user->getAvailableCredits()
            : $user->getNonMemberAvailableCredits();
        if ($availableCredits < 1) {
            $message = $user->hasActiveMembership()
                ? 'You have used all your monthly credits. Please wait until next month or book with payment.'
                : 'Insufficient credits. Please purchase more credits or get a membership.';
            return response()->json(['success' => false, 'message' => $message], 400);
        }

        // Check if class is full
        $currentBookings = Booking::where('fitness_class_id', $classId)->count();
        if ($currentBookings >= $class->max_spots) {
            return response()->json(['success' => false, 'message' => 'This class is fully booked.'], 400);
        }

        // Check if user already booked this class
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('fitness_class_id', $classId)
            ->first();

        if ($existingBooking) {
            return response()->json(['success' => false, 'message' => 'You have already booked this class.'], 400);
        }

        // Create booking and deduct credit
        $booking = Booking::create([
            'user_id' => $user->id,
            'fitness_class_id' => $classId,
            'status' => 'confirmed',
            'booked_at' => now(),
        ]);

        // Use credit from user (handles both membership and regular credits)
        if (!$user->useCredit()) {
            // This shouldn't happen due to earlier check, but just in case
            $booking->delete();
            return response()->json(['success' => false, 'message' => 'Unable to deduct credit. Please try again.'], 400);
        }

        // Send confirmation email
        try {
            Mail::to($user->email)->send(new \App\Mail\BookingConfirmed($booking));
            \Log::info('Booking confirmation email sent successfully for booking ID: ' . $booking->id);
        } catch (\Exception $e) {
            \Log::error('Failed to send booking confirmation email', [
                'user_id' => $user->id,
                'booking_id' => $booking->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }

        $remainingCredits = $user->getAvailableCredits();
        $creditType = $user->hasActiveMembership() ? 'monthly credits' : 'credits';
        
        return response()->json([
            'success' => true, 
            'message' => "Class booked successfully with {$creditType}! You have {$remainingCredits} {$creditType} remaining. Confirmation email sent.",
            // Provide a redirect URL so the frontend can navigate to the confirmation page
            'redirect_url' => route('booking.confirmation', ['classId' => $classId]),
            'booking_id' => $booking->id ?? null,
        ]);
    }

    public function checkout($classId)
    {
        $class = FitnessClass::with('instructor')->findOrFail($classId);
        
        // Check if class is full
        $currentBookings = Booking::where('fitness_class_id', $classId)->count();
        if ($currentBookings >= $class->max_spots) {
            return redirect()->back()->with('error', 'This class is fully booked.');
        }

        return view('checkout.index', compact('class'));
    }

    public function processCheckout(Request $request, $classId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $class = FitnessClass::findOrFail($classId);

        // Check if class is still available
        $currentBookings = Booking::where('fitness_class_id', $classId)->count();
        if ($currentBookings >= $class->max_spots) {
            return redirect()->back()->with('error', 'This class is now fully booked.');
        }

        // Create Stripe Checkout Session
        $stripe = new StripeClient($this->stripeSecret());

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => $class->name,
                    ],
                    'unit_amount' => (int) round($class->price * 100),
                ],
                'quantity' => 1,
            ]],
            'customer_email' => $request->email,
            'metadata' => [
                'class_id' => (string) $classId,
                'name' => $request->name,
                'email' => $request->email,
            ],
            'success_url' => route('booking.success', ['classId' => $classId]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('booking.checkout', ['classId' => $classId]),
        ]);

        return redirect()->away($session->url);
    }

    public function confirmation($classId)
    {
        $class = FitnessClass::with('instructor')->findOrFail($classId);
        return view('booking.confirmation', compact('class'));
    }

    public function success(Request $request)
    {
        $classId = $request->query('classId');
        $sessionId = $request->query('session_id');
        
        \Log::info('Booking success method called', ['classId' => $classId, 'session_id' => $sessionId]);

        if (!$sessionId) {
            \Log::warning('Booking success: Missing session_id');
            return redirect()->route('welcome')->with('error', 'Missing session.');
        }

        if (!$classId) {
            \Log::warning('Booking success: Missing classId');
            return redirect()->route('welcome')->with('error', 'Missing class information.');
        }

        $stripe = new StripeClient($this->stripeSecret());
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            \Log::info('Stripe session retrieved', ['session_id' => $sessionId, 'payment_status' => $session->payment_status ?? 'unknown']);
        } catch (\Exception $e) {
            \Log::error('Stripe session retrieval failed', ['error' => $e->getMessage()]);
            return redirect()->route('welcome')->with('error', 'Unable to verify payment.');
        }

        if (($session->payment_status ?? null) !== 'paid') {
            \Log::warning('Payment not completed', ['payment_status' => $session->payment_status ?? 'unknown']);
            return redirect()->route('welcome')->with('error', 'Payment not completed.');
        }

        // Ensure class still exists and not overbooked
        $class = FitnessClass::findOrFail($classId);
        $currentBookings = Booking::where('fitness_class_id', $classId)->count();
        if ($currentBookings >= $class->max_spots) {
            \Log::warning('Class is fully booked', ['classId' => $classId, 'currentBookings' => $currentBookings, 'maxSpots' => $class->max_spots]);
            return redirect()->route('welcome')->with('error', 'This class is now fully booked.');
        }

        // Find or create user from session/customer_email
        $email = $session->customer_details->email ?? $session->customer_email ?? null;
        $name = $session->metadata->name ?? 'Guest';

        \Log::info('Processing guest booking', ['email' => $email, 'name' => $name]);

        if (!$email) {
            \Log::error('No email found in Stripe session');
            return redirect()->route('welcome')->with('error', 'No email found for payment.');
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt('temporary_password_' . time()),
            ]
        );

        \Log::info('User created/found', ['user_id' => $user->id, 'email' => $user->email]);

        // Avoid duplicate booking if user refreshes
        $existing = Booking::where('user_id', $user->id)
            ->where('fitness_class_id', $classId)
            ->first();
        if (!$existing) {
            $booking = Booking::create([
                'user_id' => $user->id,
                'fitness_class_id' => $classId,
                'stripe_session_id' => $session->id,
                'status' => 'confirmed',
                'booked_at' => now(),
            ]);

            // Send booking confirmation email
            try {
                Mail::to($user->email)->send(new \App\Mail\BookingConfirmed($booking));
                \Log::info('Booking confirmation email sent for new booking.', ['booking_id' => $booking->id]);
            } catch (\Exception $e) {
                // Log the error but don't fail the booking
                \Log::error('Failed to send booking confirmation email for new booking: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }

            \Log::info('Booking created', ['booking_id' => $booking->id, 'user_id' => $user->id, 'class_id' => $classId]);
        } else {
            $booking = $existing;
            // Update existing booking with Stripe session ID if not present
            if (!$existing->stripe_session_id) {
                $existing->update(['stripe_session_id' => $session->id]);
                \Log::info('Updated existing booking with Stripe session ID', ['booking_id' => $existing->id]);
            }
            \Log::info('Existing booking found', ['booking_id' => $booking->id]);
        }

        return redirect()->route('booking.confirmation', ['classId' => $classId])
            ->with('success', 'Payment successful! Your class has been booked.');
    }
    /**
     * Check-in endpoint (scannable QR target). Future: mark attendance.
     */
    public function checkin(Request $request, Booking $booking)
    {
        // The 'signed' middleware on the route ensures the URL hasn't been tampered with.
        return view('booking.checkin', compact('booking'));
    }

    /**
     * User check-in endpoint using user's unique QR code.
     */
    public function userCheckin(Request $request, User $user, string $qr_code)
    {
        // Verify the QR code matches the user's QR code
        if ($user->qr_code !== $qr_code) {
            abort(403, 'Invalid QR code');
        }

        // Get user's upcoming bookings for today
        $today = now()->toDateString();
        $upcomingBookings = $user->bookings()
            ->with('fitnessClass')
            ->whereHas('fitnessClass', function ($query) use ($today) {
                $query->where('class_date', '>=', $today);
            })
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.checkin', compact('user', 'upcomingBookings'));
    }

    /**
     * Cancel a booking with quarterly limits
     */
    public function cancel(Request $request, $bookingId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to cancel bookings.'], 401);
        }

        $user = Auth::user();
        $booking = Booking::with('fitnessClass')->findOrFail($bookingId);

        // Check if booking belongs to user
        if ($booking->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You can only cancel your own bookings.'], 403);
        }

        // Check if booking is already cancelled
        if ($booking->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'This booking is already cancelled.'], 400);
        }

        // Check if class has already started
        $classStart = \Carbon\Carbon::parse($booking->fitnessClass->class_date->toDateString() . ' ' . $booking->fitnessClass->start_time);
        if ($classStart->isPast()) {
            return response()->json(['success' => false, 'message' => 'You cannot cancel a class that has already started.'], 400);
        }

        // Check quarterly cancellation limit (2 per quarter)
        $cancellationsThisQuarter = $this->getCancellationsThisQuarter($user->id);
        if ($cancellationsThisQuarter >= 2) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum of 2 cancellations per quarter. You cannot cancel this booking.'
            ], 400);
        }

        // Cancel the booking
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        // TODO: Handle refunds if applicable (for paid bookings)

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully. You have ' . (1 - $cancellationsThisQuarter) . ' cancellation(s) remaining this quarter.',
            'remaining_cancellations' => 1 - $cancellationsThisQuarter
        ]);
    }

    /**
     * Get the number of cancellations for the current quarter
     */
    private function getCancellationsThisQuarter($userId)
    {
        $now = now();
        $quarter = ceil($now->month / 3);
        $year = $now->year;

        // Calculate quarter start and end dates
        $quarterStart = \Carbon\Carbon::create($year, (($quarter - 1) * 3) + 1, 1)->startOfDay();
        $quarterEnd = \Carbon\Carbon::create($year, $quarter * 3, 1)->endOfMonth()->endOfDay();

        return Booking::where('user_id', $userId)
            ->where('status', 'cancelled')
            ->whereBetween('cancelled_at', [$quarterStart, $quarterEnd])
            ->count();
    }

    /**
     * Resolve Stripe secret from config with env fallback.
     */
    private function stripeSecret(): string
    {
        $secret = config('services.stripe.secret');
        if (!$secret) {
            $secret = env('STRIPE_SECRET');
        }
        if (!$secret) {
            abort(500, 'Stripe secret not configured.');
        }
        return $secret;
    }
}
