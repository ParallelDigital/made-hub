<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\FitnessClass;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    public function index()
    {
        $packages = [
            [
                'type' => 'first_timer',
                'name' => 'MADE MEMBERSHIP',
                'price' => 40,
                'classes' => 3,
                'description' => 'New here? Try your first three classes at our lowest price! Experience the thrill of the workout and see what MADE is all about.',
                'featured' => true
            ],
            [
                'type' => 'single',
                'name' => '1 Class',
                'price' => 20,
                'classes' => 1,
                'description' => 'Single class credit. Valid at participating locations. Terms apply.'
            ],
            [
                'type' => 'package_5',
                'name' => '5 Classes',
                'price' => 89,
                'classes' => 5,
                'description' => 'Pack of 5 class credits. Flexible usage. Terms apply.'
            ],
            [
                'type' => 'package_10',
                'name' => '10 Classes',
                'price' => 157,
                'classes' => 10,
                'description' => 'Pack of 10 class credits. Best value for regulars. Terms apply.'
            ]
        ];

        return view('purchase.index', compact('packages'));
    }

    public function showCheckoutForm($class_id)
    {
        $class = FitnessClass::findOrFail($class_id);
        return view('checkout.index', compact('class'));
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
