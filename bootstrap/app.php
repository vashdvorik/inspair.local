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
        // За прокси/CDN сервера: берём схему и хост из X-Forwarded-* (иначе ссылки получаются http / со старым хостом)
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'game.access' => \App\Http\Middleware\GameAccess::class,
            'game.on'     => \App\Http\Middleware\GameEnabled::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'telegram/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
