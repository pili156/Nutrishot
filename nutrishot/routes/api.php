<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/register/create-password', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile', [AuthController::class, 'saveProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn (Request $request) => $request->user());
});
