<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserCredits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is not authenticated or is an admin/instructor
        if (!Auth::check() || Auth::user()->role !== 'member') {
            return $next($request);
        }

        $user = Auth::user();
        
        // Check if user has enough credits
        if ($user->credits < 1) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have enough credits to book this class. Please purchase more credits or wait until next month.',
                    'redirect' => route('profile.credits')
                ], 403);
            }
            
            return redirect()
                ->route('profile.credits')
                ->with('error', 'You do not have enough credits to book this class. Please purchase more credits or wait until next month.');
        }
        
        return $next($request);
    }
}
