<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// логин без токена
Route::post('/auth/login', [AuthController::class, 'login']);

// всё ниже — только с Bearer-токеном
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('transactions', TransactionController::class);
});
