<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartnerJobController;
use App\Http\Controllers\Api\PartnerApplicationController;
use App\Http\Controllers\Api\PartnerCandidateController;
use App\Http\Controllers\Api\PartnerEarningController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/partner/jobs', [PartnerJobController::class, 'index']);
    Route::get('/partner/applications', [PartnerApplicationController::class, 'index']);
    Route::get('/partner/candidates', [PartnerCandidateController::class, 'index']);
    Route::get('/partner/earnings', [PartnerEarningController::class, 'index']);
});
