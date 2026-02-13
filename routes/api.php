<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartnerJobController;
use App\Http\Controllers\Api\PartnerApplicationController;
use App\Http\Controllers\Api\PartnerCandidateController;
use App\Http\Controllers\Api\PartnerEarningController;
use App\Http\Controllers\Api\PartnerProfileController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/partner/jobs', [PartnerJobController::class, 'index']);
    Route::get('/partner/jobs/{job}', [PartnerJobController::class, 'show']);
    Route::post('/partner/jobs/{job}/apply', [PartnerJobController::class, 'apply']);
    Route::get('/partner/applications', [PartnerApplicationController::class, 'index']);
    Route::get('/partner/candidates', [PartnerCandidateController::class, 'index']);
    Route::post('/partner/candidates', [PartnerCandidateController::class, 'store']);
    Route::get('/partner/candidates/{candidate}', [PartnerCandidateController::class, 'show']);
    Route::put('/partner/candidates/{candidate}', [PartnerCandidateController::class, 'update']);
    Route::get('/partner/earnings', [PartnerEarningController::class, 'index']);
    Route::get('/partner/profile', [PartnerProfileController::class, 'show']);
    Route::put('/partner/profile', [PartnerProfileController::class, 'update']);
});
