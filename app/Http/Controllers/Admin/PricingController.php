<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Display a listing of the pricing tiers.
     */
    public function index(Request $request)
    {
        $query = PricingTier::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('active', true);
                    break;
                case 'inactive':
                    $query->where('active', false);
                    break;
                case 'valid':
                    $query->valid();
                    break;
            }
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $pricingTiers = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Get unique types for filter dropdown
        $types = PricingTier::distinct()->pluck('type')->filter()->sort();

        return view('admin.pricing.index', compact('pricingTiers', 'types'));
    }

    /**
     * Show the form for creating a new pricing tier.
     */
    public function create()
    {
        return view('admin.pricing.create');
    }

    /**
     * Store a newly created pricing tier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|gte:min_quantity',
            'active' => 'boolean',
        ]);

        // Calculate final price
        $finalPrice = $validated['base_price'];
        if (!empty($validated['discount_percentage'])) {
            $finalPrice = $validated['base_price'] * (1 - $validated['discount_percentage'] / 100);
        }
        $validated['final_price'] = $finalPrice;

        PricingTier::create($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Pricing tier created successfully.');
    }

    /**
     * Display the specified pricing tier.
     */
    public function show(PricingTier $pricing)
    {
        return view('admin.pricing.show', compact('pricing'));
    }

    /**
     * Show the form for editing the specified pricing tier.
     */
    public function edit(PricingTier $pricing)
    {
        return view('admin.pricing.edit', compact('pricing'));
    }

    /**
     * Update the specified pricing tier in storage.
     */
    public function update(Request $request, PricingTier $pricing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|gte:min_quantity',
            'active' => 'boolean',
        ]);

        // Calculate final price
        $finalPrice = $validated['base_price'];
        if (!empty($validated['discount_percentage'])) {
            $finalPrice = $validated['base_price'] * (1 - $validated['discount_percentage'] / 100);
        }
        $validated['final_price'] = $finalPrice;

        $pricing->update($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Pricing tier updated successfully.');
    }

    /**
     * Remove the specified pricing tier from storage.
     */
    public function destroy(PricingTier $pricing)
    {
        $pricing->delete();

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Pricing tier deleted successfully.');
    }
}
