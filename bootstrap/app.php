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
    ->withMiddleware(function (Middleware $middleware): void {

        // --- 1. Register the Single Device Check Globally ---
        $middleware->append(\App\Http\Middleware\ForceSingleDevice::class);
        $middleware->append(\App\Http\Middleware\LogUserActivity::class); // Log every click

        // --- ADD THIS BLOCK ---
        $middleware->validateCsrfTokens(except: [
            'logout', // Exclude the logout route from CSRF checks
        ]);
        
       // REGISTER THE ALIAS HERE
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'register.open' => \App\Http\Middleware\EnsureRegisterOpen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
