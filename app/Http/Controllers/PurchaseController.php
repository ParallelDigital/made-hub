<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\FitnessClass;
use App\Models\PackageCode;
use Illuminate\Http\Request;
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

    public function showCheckoutForm($class_id)
    {
        $class = FitnessClass::findOrFail($class_id);

        // Check if the class has already started
        $classStart = \Carbon\Carbon::parse($class->class_date->toDateString() . ' ' . $class->start_time);
        if ($classStart->isPast()) {
            return redirect()->route('welcome')->with('error', 'This class has already started and cannot be booked.');
        }

        return view('checkout.index', compact('class'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $packages = collect($this->index()->getData()['packages'] ?? []);
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
        // Generate a unique code and email it
        $code = strtoupper(bin2hex(random_bytes(4)));
        // Determine recipient email
        $recipientEmail = $request->user()->email ?? null;
        if (!$recipientEmail && $request->has('session_id')) {
            try {
                $client = new StripeClient(config('services.stripe.secret'));
                $session = $client->checkout->sessions->retrieve($request->get('session_id'));
                $recipientEmail = $session->customer_details->email ?? $session->customer_email ?? null;
            } catch (\Throwable $e) {
                \Log::warning('Unable to retrieve Stripe session for email: '.$e->getMessage());
            }
        }

        // Store code record
        PackageCode::create([
            'code' => $code,
            'package_type' => $type,
            'classes' => in_array($type, ['unlimited','membership']) ? null : ($type === 'package_10' ? 10 : 5),
            'email' => $recipientEmail,
            'expires_at' => now()->addMonth(),
        ]);

        // Send email
        try {
            if ($recipientEmail) {
                Mail::to($recipientEmail)
                    ->send(new \App\Mail\PackageCodeMail($code, $type));
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to send package code email: '.$e->getMessage());
        }

        return redirect()->route('purchase.index')->with('success', 'Purchase successful! Your code has been emailed to you.');
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
