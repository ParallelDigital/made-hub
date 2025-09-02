<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pricingTiers = PricingTier::orderBy('created_at', 'desc')->get();
        return view('admin.pricing.index', compact('pricingTiers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pricing.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:class,membership,package',
            'base_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        // Calculate final price
        $discountPercentage = $validated['discount_percentage'] ?? 0;
        $validated['final_price'] = $validated['base_price'] * (1 - $discountPercentage / 100);
        $validated['active'] = $request->has('active');

        PricingTier::create($validated);

        return redirect()->route('admin.pricing.index')
                        ->with('success', 'Pricing tier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingTier $pricing)
    {
        return view('admin.pricing.show', compact('pricing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingTier $pricing)
    {
        return view('admin.pricing.edit', compact('pricing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingTier $pricing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:class,membership,package',
            'base_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        // Calculate final price
        $discountPercentage = $validated['discount_percentage'] ?? 0;
        $validated['final_price'] = $validated['base_price'] * (1 - $discountPercentage / 100);
        $validated['active'] = $request->has('active');

        $pricing->update($validated);

        return redirect()->route('admin.pricing.index')
                        ->with('success', 'Pricing tier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingTier $pricing)
    {
        $pricing->delete();

        return redirect()->route('admin.pricing.index')
                        ->with('success', 'Pricing tier deleted successfully.');
    }
}
