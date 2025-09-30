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

        // Check if the class has already started (use Europe/London timezone)
        $tz = 'Europe/London';
        $classStart = \Carbon\Carbon::parse($class->class_date->format('Y-m-d') . ' ' . $class->start_time, $tz);
        if ($classStart->lessThan(\Carbon\Carbon::now($tz))) {
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
        // Validate input according to checkout mode (guest vs account)
        $mode = $request->input('checkout_mode', $request->user() ? 'account' : 'guest');
        $existingUser = User::where('email', $request->input('email'))->first();

        if ($request->user()) {
            // Logged-in users: no password needed
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'coupon_code' => 'nullable|string',
            ]);
        } elseif ($mode === 'account') {
            // Sign in and checkout
            if (!$existingUser) {
                return back()->withInput()->with('error', 'No account found for this email. Choose Guest checkout to buy credits for this email, or use a different email.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => ['required','string'],
                'coupon_code' => 'nullable|string',
            ]);

            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return back()->withInput()->with('error', 'Invalid password for this email. You can choose Guest checkout to buy credits for this account without signing in.');
            }

            $request->session()->regenerate();
        } else {
            // Guest checkout (no sign-in) — credits will be allocated to the account for this email
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'coupon_code' => 'nullable|string',
            ]);
        }

        $packages = collect($this->getPackages());
        $package = $packages->firstWhere('type', $type);
        abort_unless($package, 404);

        // Calculate price with coupon discount
        $price = $package['price'];
        $discountAmount = 0;
        $couponCode = null;
        
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)->where('status', 'active')->first();
            if ($coupon) {
                $couponCode = $coupon->code;
                if ($coupon->type === 'fixed') {
                    $discountAmount = $coupon->value;
                } elseif ($coupon->type === 'percentage') {
                    $discountAmount = ($price * $coupon->value) / 100;
                }
                $price = max(0, $price - $discountAmount);
            } else {
                return back()->withInput()->with('error', 'Invalid or inactive coupon code.');
            }
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $isMembership = ($type === 'membership');
            $lineItem = [
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => $package['name'],
                    ],
                    'unit_amount' => (int) round($price * 100),
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
                    'checkout_mode' => $mode,
                    'coupon_code' => $couponCode,
                    'discount_amount' => $discountAmount,
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

        // Determine checkout mode (guest vs account) from session metadata or auth context
        $checkoutMode = 'guest';
        if ($request->user()) {
            $checkoutMode = 'account';
        } elseif ($session && isset($session->metadata) && ($session->metadata->checkout_mode ?? null)) {
            $checkoutMode = $session->metadata->checkout_mode;
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
                'email_verified_at' => now(), // Consider the email verified since they made a purchase
            ]
        );

        // Login only if they checked out as account (signed in); keep guest otherwise (security)
        if ($checkoutMode === 'account' || $request->user()) {
            try {
                Auth::login($user);
            } catch (\Throwable $e) {
                \Log::warning('Auto login after package purchase failed: '.$e->getMessage());
            }
        }

        // If the account was just created, email them a password setup link
        if ($user->wasRecentlyCreated) {
            try {
                $status = Password::sendResetLink(['email' => $user->email]);
                
                if ($status === Password::RESET_LINK_SENT) {
                    \Log::info('Password setup link sent successfully to: ' . $user->email);
                } else {
                    \Log::warning('Failed to send password setup link to: ' . $user->email . ', Status: ' . $status);
                }
            } catch (\Throwable $e) {
                \Log::error('Exception sending password setup link to: ' . $user->email . ', Error: ' . $e->getMessage());
                
                // Try to trigger the password reset more explicitly
                try {
                    $token = app('auth.password.broker')->createToken($user);
                    \Log::info('Manual password reset token created for: ' . $user->email);
                    
                    // You could send a custom email here if needed
                    // Mail::to($user)->send(new CustomPasswordSetupMail($token));
                    
                } catch (\Throwable $e2) {
                    \Log::error('Failed to create manual password reset token: ' . $e2->getMessage());
                }
            }
        }

        // Determine allocation by type
        $expiresAt = now()->addMonth();
        $allocated = false;
        $allocatedMessage = '';
        try {
            if ($type === 'package_5') {
                $user->allocateCreditsWithExpiry(5, $expiresAt, 'package_purchase');
                $allocated = true;
                $allocatedMessage = '5 credits added (valid for 1 month).';
                // Notify user
                try {
                    Mail::to($user->email)->send(new \App\Mail\CreditsAllocated($user, 5, 'credits', $user->getNonMemberAvailableCredits(), 'Valid for 1 month', 'Package Purchase'));
                } catch (\Throwable $e) { \Log::warning('Credits email failed: '.$e->getMessage()); }
            } elseif ($type === 'package_10') {
                $user->allocateCreditsWithExpiry(10, $expiresAt, 'package_purchase');
                $allocated = true;
                $allocatedMessage = '10 credits added (valid for 1 month).';
                try {
                    Mail::to($user->email)->send(new \App\Mail\CreditsAllocated($user, 10, 'credits', $user->getNonMemberAvailableCredits(), 'Valid for 1 month', 'Package Purchase'));
                } catch (\Throwable $e) { \Log::warning('Credits email failed: '.$e->getMessage()); }
            } elseif ($type === 'unlimited') {
                $user->activateUnlimitedPass($expiresAt, 'package_purchase');
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

        // Get package info for confirmation page
        $packages = collect($this->getPackages());
        $package = $packages->firstWhere('type', $type);
        
        return view('purchase.confirmation', compact('user', 'type', 'package', 'allocatedMessage'))
            ->with('message', 'Purchase successful! ' . $allocatedMessage);
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
        // Validate based on whether it's a class or package discount
        if ($request->has('class_id')) {
            // Individual class discount
            $request->validate([
                'coupon_code' => 'required|string',
                'class_id' => 'required|exists:fitness_classes,id',
            ]);
            
            $class = FitnessClass::findOrFail($request->class_id);
            $originalPrice = $class->price;
        } else {
            // Package discount
            $request->validate([
                'coupon_code' => 'required|string',
                'package_type' => 'required|string',
                'original_price' => 'required|numeric',
            ]);
            
            $originalPrice = $request->original_price;
        }

        $coupon = Coupon::where('code', $request->coupon_code)->where('status', 'active')->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or inactive coupon code.']);
        }

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

        // Check if the class has already started (use Europe/London timezone)
        $tz = 'Europe/London';
        $classStart = \Carbon\Carbon::parse($class->class_date->format('Y-m-d') . ' ' . $class->start_time, $tz);
        if ($classStart->lessThan(\Carbon\Carbon::now($tz))) {
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

    public function showConfirmation()
    {
        return view('purchase.confirmation', [
            'type' => 'package',
            'package' => ['name' => 'Class Pass', 'price' => 0],
            'allocatedMessage' => 'Your purchase has been processed.',
            'user' => auth()->user()
        ]);
    }
}
