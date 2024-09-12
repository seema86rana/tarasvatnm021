<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated and has an 'admin' role_id
        if (Auth::check() && Auth::user()->role_id ==2) {
            return $next($request);
        }

        // Redirect or return an error if not an admin
        return redirect()->route('dashboard.index')->with('error', 'You do not have permission to access this module.');
    }
}
