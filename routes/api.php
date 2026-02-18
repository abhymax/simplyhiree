<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartnerJobController;
use App\Http\Controllers\Api\PartnerApplicationController;
use App\Http\Controllers\Api\PartnerCandidateController;
use App\Http\Controllers\Api\PartnerEarningController;
use App\Http\Controllers\Api\PartnerProfileController;
use App\Http\Controllers\Api\ClientJobController;
use App\Http\Controllers\Api\ClientApplicantController;
use App\Http\Controllers\Api\ClientBillingController;
use App\Http\Controllers\Api\ClientDashboardController;
use App\Http\Controllers\Api\ClientProfileApiController;

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
    Route::get('/client/job-form-data', [ClientJobController::class, 'formData']);
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index']);
    Route::get('/client/jobs', [ClientJobController::class, 'index']);
    Route::post('/client/jobs', [ClientJobController::class, 'store']);
    Route::get('/client/applicants', [ClientApplicantController::class, 'index']);
    Route::post('/client/applications/{application}/reject', [ClientApplicantController::class, 'reject']);
    Route::post('/client/applications/{application}/interview', [ClientApplicantController::class, 'scheduleInterview']);
    Route::post('/client/applications/{application}/appeared', [ClientApplicantController::class, 'markAppeared']);
    Route::post('/client/applications/{application}/no-show', [ClientApplicantController::class, 'markNoShow']);
    Route::post('/client/applications/{application}/select', [ClientApplicantController::class, 'selectCandidate']);
    Route::get('/client/billing', [ClientBillingController::class, 'index']);
    Route::get('/client/profile', [ClientProfileApiController::class, 'show']);
    Route::post('/client/profile', [ClientProfileApiController::class, 'update']);
    Route::put('/client/profile', [ClientProfileApiController::class, 'update']);
});
