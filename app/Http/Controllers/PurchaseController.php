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
}
