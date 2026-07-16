<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login')->with('error', 'Admin access required.');
        }

        return $next($request);
    }
}