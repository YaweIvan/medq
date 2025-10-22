<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QuizzerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isQuizzer() || !auth()->user()->is_approved) {
            abort(403, 'Access denied or account not approved.');
        }

        return $next($request);
    }
}