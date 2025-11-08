<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompletePhoneController extends Controller
{
    /**
     * Show the phone number completion form
     */
    public function show()
    {
        $user = Auth::user();
        
        // If user already has a phone, redirect to dashboard
        if (!empty($user->phone)) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.complete-phone');
    }
    
    /**
     * Store the phone number
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validate phone number with regex
        // Accepts formats: +1234567890, 123-456-7890, (123) 456-7890, 123.456.7890, 1234567890
        $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/',
                'min:10',
                'max:20'
            ]
        ], [
            'phone.required' => 'Phone number is required to continue.',
            'phone.regex' => 'Please enter a valid phone number.',
            'phone.min' => 'Phone number must be at least 10 digits.',
        ]);
        
        // Update user's phone number
        $user->phone = $request->phone;
        $user->save();
        
        return redirect()->intended(route('dashboard'))
            ->with('success', 'Phone number added successfully!');
    }
}
