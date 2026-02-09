<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check authentication - NO logout to keep admins logged in
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        
        // Get fresh user data from database every request
        $user = Auth::user()->fresh();
        
        // Only check role - never logout, just block access
        if (!$user || !$user->isAdmin()) {
            // DO NOT logout - just deny access
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}