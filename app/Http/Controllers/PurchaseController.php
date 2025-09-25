<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\FitnessClass;
use App\Models\PackageCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class PurchaseController extends Controller
{
    public function index()
    {
        $packages = $this->getPackages();
        return view('purchase.index', compact('packages'));
    }

    public function showCheckoutForm(Request $request, $class_id)
    {
        $class = FitnessClass::findOrFail($class_id);

        // Check if the class has already started
        $classStart = \Carbon\Carbon::parse($class->class_date->toDateString() . ' ' . $class->start_time);
        if ($classStart->isPast()) {
            return redirect()->route('welcome')->with('error', 'This class has already started and cannot be booked.');
        }

        $user = $request->user();

        // Block checkout for members-only classes; guide user appropriately
        if ($class->members_only) {
            if (!$user || !$user->hasActiveMembership()) {
                return redirect()->route('welcome')->with('error', 'This class is for members only. Please become a member to attend.');
            }
            // Members should book for free via the normal booking flow
            return redirect()->route('welcome', ['openBooking' => 1, 'classId' => $class->id, 'price' => 0]);
        }

        $hasMembership = $user ? $user->hasActiveMembership() : false;
        $availableCredits = 0;
        if ($user) {
            $availableCredits = $hasMembership ? $user->getAvailableCredits() : $user->getNonMemberAvailableCredits();
        }

        $autoOpenCredits = $request->boolean('useCredits');

        return view('checkout.index', compact('class', 'availableCredits', 'autoOpenCredits'));
    }

    // New: package checkout
    public function showPackageCheckout(Request $request, string $type)
    {
        $packages = collect($this->getPackages());
        $package = $packages->firstWhere('type', $type);
        abort_unless($package, 404);
        return view('purchase.package-checkout', ['package' => (object)$package]);
    }

    public function processPackageCheckout(Request $request, string $type)
    {
        // Validate input; require password for guests:
        // - If email already exists, require password (login) without confirmation.
        // - If new email, require strong password with confirmation (account creation).
        $existingUser = User::where('email', $request->input('email'))->first();

        if ($request->user()) {
            // Logged-in users: no password needed
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);
        } elseif ($existingUser) {
            // Guest using an existing account: must provide password to login
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => ['required','string'],
            ]);

            // Attempt login
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return back()->withInput()->with('error', 'This email is already registered. Please enter the correct password to continue, or use a different email.');
            }

            $request->session()->regenerate();
        } else {
            // Guest creating a new account: no password required at checkout.
            // We'll create the account after successful payment and email a password setup link.
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);
        }

        $packages = collect($this->getPackages());
        $package = $packages->firstWhere('type', $type);
        abort_unless($package, 404);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $isMembership = ($type === 'membership');
            $lineItem = [
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => $package['name'],
                    ],
                    'unit_amount' => (int) round($package['price'] * 100),
                ],
                'quantity' => 1,
            ];

            if ($isMembership) {
                // Recurring monthly subscription
                $lineItem['price_data']['recurring'] = ['interval' => 'month'];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [$lineItem],
                'mode' => $isMembership ? 'subscription' : 'payment',
                'success_url' => route('purchase.package.success', ['type' => $type]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.index'),
                'customer_email' => $request->email,
                'metadata' => [
                    'package_type' => $type,
                    'name' => $request->name,
                    'email' => $request->email,
                ],
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return back()->with('error', 'There was an error processing your payment. ' . $e->getMessage());
        }
    }

    public function packageSuccess(Request $request, string $type)
    {
        // Determine purchaser email
        $recipientEmail = $request->user()->email ?? null;
        $session = null;
        if (!$recipientEmail && $request->has('session_id')) {
            try {
                $client = new StripeClient(config('services.stripe.secret'));
                $session = $client->checkout->sessions->retrieve($request->get('session_id'));
                $recipientEmail = $session->customer_details->email ?? $session->customer_email ?? null;
            } catch (\Throwable $e) {
                \Log::warning('Unable to retrieve Stripe session for email: '.$e->getMessage());
            }
        }

        if (!$recipientEmail) {
            return redirect()->route('purchase.index')->with('error', 'Unable to identify purchaser email for allocation.');
        }

        // Find or create the user for this email
        $user = User::firstOrCreate(
            ['email' => $recipientEmail],
            [
                'name' => ($session && ($session->metadata->name ?? null)) ? $session->metadata->name : 'Guest',
                // Create a secure random password for new users; they can set their own later
                'password' => Hash::make(bin2hex(random_bytes(16))),
            ]
        );

        // Ensure the purchaser is logged in for immediate access to credits/passes
        try {
            Auth::login($user);
        } catch (\Throwable $e) {
            \Log::warning('Auto login after package purchase failed: '.$e->getMessage());
        }

        // If the account was just created, email them a password setup link
        if ($user->wasRecentlyCreated) {
            try {
                Password::sendResetLink(['email' => $user->email]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to send password setup link: '.$e->getMessage());
            }
        }

        // Determine allocation by type
        $expiresAt = now()->addMonth();
        $allocated = false;
        $allocatedMessage = '';
        try {
            if ($type === 'package_5') {
                $user->allocateCreditsWithExpiry(5, $expiresAt);
                $allocated = true;
                $allocatedMessage = '5 credits added (valid for 1 month).';
                // Notify user
                try {
                    Mail::to($user->email)->send(new \App\Mail\CreditsAllocated($user, 5, 'credits', (int)($user->credits ?? 0), 'Valid for 1 month'));
                } catch (\Throwable $e) { \Log::warning('Credits email failed: '.$e->getMessage()); }
            } elseif ($type === 'package_10') {
                $user->allocateCreditsWithExpiry(10, $expiresAt);
                $allocated = true;
                $allocatedMessage = '10 credits added (valid for 1 month).';
                try {
                    Mail::to($user->email)->send(new \App\Mail\CreditsAllocated($user, 10, 'credits', (int)($user->credits ?? 0), 'Valid for 1 month'));
                } catch (\Throwable $e) { \Log::warning('Credits email failed: '.$e->getMessage()); }
            } elseif ($type === 'unlimited') {
                $user->activateUnlimitedPass($expiresAt);
                $allocated = true;
                $allocatedMessage = 'Unlimited pass activated for 1 month.';
                // Optional: email notification could be added here
            } elseif ($type === 'membership') {
                // For membership subscription, allocation is handled by Stripe webhooks/subscription logic.
                $allocatedMessage = 'Membership purchase successful.';
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to allocate package to user', ['type' => $type, 'user_id' => $user->id, 'error' => $e->getMessage()]);
            return redirect()->route('purchase.index')->with('error', 'Purchase completed, but allocation failed. Please contact support.');
        }

        // Optional: Store a code record for audit (not emailed)
        try {
            PackageCode::create([
                'code' => strtoupper(bin2hex(random_bytes(4))),
                'package_type' => $type,
                'classes' => in_array($type, ['unlimited','membership']) ? null : ($type === 'package_10' ? 10 : 5),
                'email' => $recipientEmail,
                'expires_at' => $expiresAt,
                'redeemed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to store package code audit record: '.$e->getMessage());
        }

        return redirect()->route('purchase.index')->with('success', 'Purchase successful! ' . $allocatedMessage . ' You can now book classes.');
    }

    private function getPackages(): array
    {
        return [
            [
                'type' => 'membership',
                'name' => 'MEMBERSHIP',
                'price' => 30.00,
                'classes' => null,
                'billing' => 'per month',
            ],
            [
                'type' => 'package_5',
                'name' => '5 CLASSES',
                'price' => 32.50,
                'classes' => 5,
                'validity' => 'VALID FOR 1 MONTH',
            ],
            [
                'type' => 'package_10',
                'name' => '10 CLASSES',
                'price' => 50.00,
                'classes' => 10,
                'validity' => 'VALID FOR 1 MONTH',
            ],
            [
                'type' => 'unlimited',
                'name' => 'UNLIMITED',
                'price' => 90.00,
                'classes' => null,
                'validity' => 'VALID FOR 1 MONTH',
            ],
        ];
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'class_id' => 'required|exists:fitness_classes,id',
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)->where('status', 'active')->first();
        $class = FitnessClass::findOrFail($request->class_id);

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or inactive coupon code.']);
        }

        $originalPrice = $class->price;
        $discount = 0;

        if ($coupon->type === 'fixed') {
            $discount = $coupon->value;
        } elseif ($coupon->type === 'percentage') {
            $discount = ($originalPrice * $coupon->value) / 100;
        }

        $newTotal = max(0, $originalPrice - $discount);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount_amount' => $discount,
            'new_total' => $newTotal,
        ]);
    }

    public function processCheckout(Request $request, $class_id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'coupon_code' => 'nullable|string',
        ]);

        $class = FitnessClass::findOrFail($class_id);

        // Check if the class has already started
        $classStart = \Carbon\Carbon::parse($class->class_date->toDateString() . ' ' . $class->start_time);
        if ($classStart->isPast()) {
            return back()->with('error', 'This class has already started and cannot be booked.');
        }

        $price = $class->price;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->where('status', 'active')->first();
            if ($coupon) {
                if ($coupon->type === 'fixed') {
                    $price -= $coupon->value;
                } elseif ($coupon->type === 'percentage') {
                    $price -= ($price * $coupon->value) / 100;
                }
                $price = max(0, $price);
            }
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[ 
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => [
                            'name' => $class->name,
                        ],
                        'unit_amount' => $price * 100, // Amount in pence
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('booking.success') . '?classId=' . $class_id . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.show', $class->id),
                'customer_email' => $request->email,
                'metadata' => [
                    'class_id' => $class_id,
                    'name' => $request->name,
                ],
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return back()->with('error', 'There was an error processing your payment. ' . $e->getMessage());
        }
    }

    public function showSuccessPage()
    {
        return view('checkout.success');
    }
}
