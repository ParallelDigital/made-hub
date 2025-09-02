<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $packages = [
            [
                'type' => 'first_timer',
                'name' => 'MADE NEWBIE 3 PACK',
                'price' => 40,
                'classes' => 3,
                'description' => 'New to the Red Room? Try your first three classes at our lowest price! Experience the thrill of the Best Workout...',
                'featured' => true
            ],
            [
                'type' => 'single',
                'name' => 'Manchester - 1 Class',
                'price' => 20,
                'classes' => 1,
                'description' => 'Manchester class credits are also valid in the following regions: Manchester and Liverpool. Class packages...'
            ],
            [
                'type' => 'package_5',
                'name' => 'Manchester - 5 Classes',
                'price' => 89,
                'classes' => 5,
                'description' => 'Manchester class credits are also valid in the following regions: Manchester and Liverpool. Class packages...'
            ],
            [
                'type' => 'package_10',
                'name' => 'Manchester - 10 Classes',
                'price' => 157,
                'classes' => 10,
                'description' => 'Manchester class credits are also valid in the following regions: Manchester and Liverpool. Class packages...'
            ]
        ];

        return view('purchase.index', compact('packages'));
    }
}
