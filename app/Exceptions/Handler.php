<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function render($request, Throwable $exception)
    {
         // Handle unauthenticated access
        if ($exception instanceof AuthenticationException) {
            // if ($request->expectsJson()) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Please login again.',
                ], 401);
            }
        }

        if ($exception instanceof ModelNotFoundException) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found.',
                ], 404);
            }
        }

        return parent::render($request, $exception);
    }
}
