<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login'); // Redirect to login if not authenticated
        }

        // Check if the authenticated user has the required role
        $user = Auth::user();
        foreach ($roles as $role) {
            if ($user->{'is' . ucfirst($role)}()) {
                return $next($request); // User has the required role, allow access
            }
        }

        // notyf error message
        

        // If the user does not have the required role, redirect back or to a specific route
        return redirect()->route('dashboard')->withErrors(['error' => 'You do not have permission to access this page.']);
    }
}
