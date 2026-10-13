<?php

use App\Adapters\Inbound\Http\Controllers\CategoriesController;
use App\Adapters\Inbound\Http\Controllers\HealthController;
use App\Adapters\Inbound\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Públicas (CA-01.3)
Route::get('/health', HealthController::class);
Route::post('/auth/login', LoginController::class);

// Cualquier usuario autenticado
Route::middleware('auth.token')->group(function () {
    Route::get('/categories', CategoriesController::class);
});
