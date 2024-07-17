<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = Auth::user();

        // Ensure that the user is authenticated and has the specified role
        if (! $user || $user->role->name !== $role) {
            // Throw a 401 Unauthorized exception if the user is not authorized
            throw new HttpException(401);
        }

        return $next($request);
    }
}
