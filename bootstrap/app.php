<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Support\ActivityLog;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo('/admin');
        $middleware->web(append: [HandleInertiaRequests::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $exception, $request) {
            if ($request->is('admin*') && ($response->getStatusCode() >= 400 || $exception instanceof ValidationException || $exception instanceof AuthenticationException)) {
                try {
                    ActivityLog::record($request->user()?->id, 'request.rejected', 'Permintaan ditolak atau gagal', [], ['method' => $request->method(), 'route' => $request->route()?->getName(), 'status' => $exception instanceof ValidationException ? 422 : ($exception instanceof AuthenticationException ? 401 : $response->getStatusCode())], outcome: 'failed');
                } catch (Throwable $error) {
                    report($error);
                }
            }

            return $response;
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
