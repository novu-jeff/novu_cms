<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The attributes that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Redirect expired Committee Portal sessions to its own login page.
        if (
            $exception instanceof TokenMismatchException &&
            (
                $request->is('committee-portal') ||
                $request->is('committee-portal/*')
            )
        ) {
            return redirect('/committee-portal/login')
                ->with('error', 'Your session has expired. Please log in again.');
        }

        return parent::render($request, $exception);
    }
}
