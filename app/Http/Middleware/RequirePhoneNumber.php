<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePhoneNumber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check if user is not authenticated
        if (!$request->user()) {
            return $next($request);
        }

        // Skip check if already on the complete-phone route
        if ($request->routeIs('complete-phone.show') || $request->routeIs('complete-phone.store')) {
            return $next($request);
        }

        // Skip check for logout route
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // If user doesn't have a phone number, redirect to complete phone form
        if (empty($request->user()->phone)) {
            return redirect()->route('complete-phone.show');
        }

        return $next($request);
    }
}
