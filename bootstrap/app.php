
<?php

use App\Http\Middleware\AddCorsHeaders;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(AddCorsHeaders::class);

        // Redirect unauthenticated users to the appropriate login page.
        $middleware->redirectGuestsTo(function (Request $request) {
            if (
                $request->is('committee-portal') ||
                $request->is('committee-portal/*')
            ) {
                return '/committee-portal/login';
            }

            return '/login';
        });

        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'role.check' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Handle expired sessions and CSRF token mismatches.
        $exceptions->render(function (
            TokenMismatchException $exception,
            Request $request
        ) {
            if (
                $request->is('committee-portal') ||
                $request->is('committee-portal/*')
            ) {
                return redirect('/committee-portal/login')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
        });

    })
    ->create();
