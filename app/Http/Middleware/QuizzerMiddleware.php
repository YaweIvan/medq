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
        
        // Only check role and approval - never logout, redirect to dashboard with message
        if (!$user || !$user->isQuizzer() || !$user->is_approved) {
            // DO NOT logout - redirect to quizzer dashboard with message
            return redirect()->route('quizzer.dashboard')->with('warning', 'Your access has been updated. Please check your assigned quizzes.');
        }

        return $next($request);
    }
}