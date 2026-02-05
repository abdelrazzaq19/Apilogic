<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'status' => 'Error',
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], 422);
                }
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status' => 'Error',
                        'message' => 'Unauthenticated',
                        'errors' => [],
                    ], 401);
                }

                if ($e instanceof AuthorizationException) {
                    return response()->json([
                        'status' => 'Error',
                        'message' => 'Unauthorized',
                        'errors' => [],
                    ], 403);
                }
                return response()->json([
                    'status' => 'Error',
                    'message' => $e->getMessage() ?: 'internal server error',
                    'errors' => [],
                ], $e->getCode() ?: 500,);
            }
        });
    })->create();

    //36m 56d
