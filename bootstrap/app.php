<?php

use App\Http\Middleware\CheckAccountStatus;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(dirname(__DIR__))
    ->withRouting(function () {
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));
        require base_path('routes/console.php');
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'status.check' => CheckAccountStatus::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
