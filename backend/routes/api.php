<?php

use App\Domains\Auth\Http\Controllers\AuthController;
use App\Domains\Health\Http\Controllers\MedicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/medications', [MedicationController::class, 'store']);
    Route::get('/medications/{medication}/status', [MedicationController::class, 'show']);
});
