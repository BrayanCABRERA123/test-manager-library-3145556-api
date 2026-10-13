<?php

use App\Adapters\Inbound\Http\Controllers\HealthController;
use App\Adapters\Inbound\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::post('/auth/login', LoginController::class);
