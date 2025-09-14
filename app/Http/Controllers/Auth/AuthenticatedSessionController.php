<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // If a safe redirect param is present, prefer it (used to open booking modal post-login)
        $redirectParam = $request->string('redirect')->toString();
        if ($redirectParam && str_starts_with($redirectParam, '/')) {
            return redirect($redirectParam);
        }

        // Get the intended URL and validate it
        $intendedUrl = $request->session()->get('url.intended');
        
        // If the intended URL contains book-with-credits, redirect to dashboard instead
        if ($intendedUrl && str_contains($intendedUrl, 'book-with-credits')) {
            $request->session()->forget('url.intended');
            $intendedUrl = null;
        }

        // Redirect based on user role
        if (auth()->user()->role === 'admin') {
            return $intendedUrl ? redirect($intendedUrl) : redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'instructor') {
            return $intendedUrl ? redirect($intendedUrl) : redirect()->route('instructor.dashboard');
        }

        return $intendedUrl ? redirect($intendedUrl) : redirect()->route('dashboard', [], false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
