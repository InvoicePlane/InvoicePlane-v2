<?php

use Filament\Facades\Filament;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ModelNotFoundException $e, $request) {
            Log::error('ModelNotFoundException caught: ' . $e->getMessage(), [
                'model' => $e->getModel(),
                'ids'   => $e->getIds(),
                'url'   => $request->fullUrl(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw as a NotFoundHttpException to let Laravel's default 404 handler take over
            throw new NotFoundHttpException('Resource not found.', $e);
        });

        // Handle authentication exceptions specifically for Filament
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            // Check if the request is coming from a Filament route
            if (str_starts_with($request->path(), '') || $request->is('admin*') || $request->is('company*')) {
                $panel = Filament::getCurrentPanel();

                if ($panel) {
                    // In Filament 4, the login route follows the pattern: 'filament.{panel}.auth.login'
                    $loginRoute = 'filament.' . $panel->getId() . '.auth.login';

                    // Check if the route exists before redirecting
                    if (\Illuminate\Support\Facades\Route::has($loginRoute)) {
                        return redirect()->guest(route($loginRoute));
                    }
                }
            }

            // Fallback to default behavior for non-Filament routes
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });
    })->create();
