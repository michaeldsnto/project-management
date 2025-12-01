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
        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'project.access' => \App\Http\Middleware\CheckProjectAccess::class,
            'task.access' => \App\Http\Middleware\CheckTaskAccess::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'not.client' => \App\Http\Middleware\PreventClientAccess::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
        ]);

        // Apply middleware to all authenticated routes
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();