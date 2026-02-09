<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizzerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check authentication - NO logout to keep students logged in during admin changes
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        
        // Get fresh user data from database every request
        $user = Auth::user()->fresh();
        
        // Only check role and approval - never logout, just block access
        if (!$user || !$user->isQuizzer() || !$user->is_approved) {
            // DO NOT logout - just deny access
            abort(403, 'Access denied. Please contact administrator.');
        }

        return $next($request);
    }
}