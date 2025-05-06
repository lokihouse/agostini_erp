<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [ // Usando group() em vez de web(append:)
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // ShareErrorsFromSession::class, // Deixar o PanelProvider adicionar
            // VerifyCsrfToken::class,      // Deixar o PanelProvider adicionar
            SubstituteBindings::class, // Essencial para rotas com parâmetros
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
