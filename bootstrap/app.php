<?php

use App\Adapters\Inbound\Http\Controllers\HealthController;
use App\Adapters\Inbound\Http\ErrorResponse;
use App\Adapters\Inbound\Http\Middleware\AuthenticateToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            // T-01 pide GET /health en la raíz; también existe bajo /api/v1/health (base del contrato).
            Route::middleware('api')->get('/health', HealthController::class);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['auth.token' => AuthenticateToken::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);
        $exceptions->render(fn (Throwable $e) => ErrorResponse::from($e));
    })->create();
