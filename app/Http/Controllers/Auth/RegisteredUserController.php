<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            // At least 8 characters, one uppercase, one lowercase, one number
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'qr_code' => 'QR' . strtoupper(substr(md5(uniqid()), 0, 8)),
        ]);

        event(new Registered($user));

        // Explicitly send verification email (in addition to Registered event)
        try {
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send verification email on registration', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Keep user logged in but take them to the verification prompt
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
