<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventSessionRegeneration
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if ($request->user() && $request->user()->role === 'quizzer') {
            $request->session()->put('last_activity', time());
        }
        
        return $response;
    }
}
