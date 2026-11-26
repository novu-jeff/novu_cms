<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when unauthenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Committee Portal session expired.
        if ($request->is('committee-portal')
            || $request->is('committee-portal/*')) {

            return route('committee-portal.login');
        }

        // Preserve the existing behavior for other modules.
        return $request->expectsJson() ? null : route('login');
    }
}