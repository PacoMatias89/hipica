<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
<<<<<<< HEAD
        // $middleware->register('horses', HorsesMiddleware::class); // Registra tu middleware aquí

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
=======
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
