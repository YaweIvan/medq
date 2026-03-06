<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'quizzer' => \App\Http\Middleware\QuizzerMiddleware::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\PreventSessionRegeneration::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Catch CSRF token mismatch (419) — redirect silently to login
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login');
        });
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                return redirect()->route('login');
            }
        });
    })->create();