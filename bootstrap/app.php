<?php

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ValidationException $exception) {
            return ApiResponse::error('validation failed.', $exception->errors(), 422);
        });

        $exceptions->renderable(function (AuthenticationException $exception) {
            return ApiResponse::error('unauthenticated.', null, 401);
        });

        $exceptions->renderable(function (AuthorizationException $exception) {
            return ApiResponse::error('forbidden.', null, 403);
        });

        $exceptions->renderable(function (ModelNotFoundException $exception) {
            return ApiResponse::error('resource not found.', null, 404);
        });
    })->create();
