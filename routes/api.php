<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasienController;
use App\Http\Controllers\Api\RekamMedisPasienController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('patients', PasienController::class);
    Route::apiResource('patients/{pasienId}/records', RekamMedisPasienController::class);
});