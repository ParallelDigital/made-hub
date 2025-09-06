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
    public function bookWithCredits($classId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to book with credits.'], 401);
        }

        $user = Auth::user();
        $class = FitnessClass::findOrFail($classId);

        // Check if user has enough credits (assuming 1 credit per class)
        if ($user->credits < 1) {
            return response()->json(['success' => false, 'message' => 'Insufficient credits. Please purchase more credits.'], 400);
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
            // 'booking_type' => 'credit', // omit: column may not exist in current DB
            'status' => 'confirmed',
            'booked_at' => now(),
        ]);

        // Deduct credit from user
        $user->decrement('credits');

        // Send confirmation email
        $qrUrl = URL::signedRoute('booking.checkin', ['booking' => $booking->id]);
        try {
            Mail::to($user->email)->send(new BookingConfirmed($booking, $qrUrl));
        } catch (\Throwable $e) {
            \Log::warning('Booking confirmation email failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Class booked successfully with credits! Confirmation email sent.']);
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

        // Generate a signed check-in URL and email the QR to the user
        $qrUrl = URL::signedRoute('booking.checkin', ['booking' => $booking->id]);
        try {
            Mail::to($email)->send(new BookingConfirmed($booking, $qrUrl));
            \Log::info('Confirmation email sent', ['email' => $email]);
        } catch (\Throwable $e) {
            \Log::warning('Booking confirmation email failed: ' . $e->getMessage());
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
