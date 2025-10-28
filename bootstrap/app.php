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
        // THE FIX IS HERE: We are putting the 'role' alias back.
        // This tells Laravel that whenever it sees ->middleware('role:...'),
        // it should use the RoleMiddleware class.
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role'     => \App\Http\Middleware\RoleMiddleware::class, // <-- THIS IS THE CORRECTED LINE
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

