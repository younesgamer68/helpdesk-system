<?php

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        RedirectIfAuthenticated::redirectUsing(fn() => route('verification.notice'));
    })->withMiddleware(function (Middleware $middleware): void {
    RedirectIfAuthenticated::redirectUsing(fn() => route('verification.notice'));

    // Register middleware aliases
    $middleware->alias([
        'company.access' => \App\Http\Middleware\EnsureUserBelongsToCompany::class,
    ]);

    // Append web middleware
    $middleware->web(append: [
        \App\Http\Middleware\IdentifyCompanyFromSubdomain::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
