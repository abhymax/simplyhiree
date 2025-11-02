<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('landing');
});
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

// Guest-only registration routes
Route::middleware('guest')->group(function () {
    Route::get('/register/partner', [RegisteredUserController::class, 'showPartnerRegistrationForm'])->name('register.partner');
    Route::post('/register/partner', [RegisteredUserController::class, 'registerPartner']);
    Route::get('/register/candidate', [RegisteredUserController::class, 'showCandidateRegistrationForm'])->name('register.candidate');
    Route::post('/register/candidate', [RegisteredUserController::class, 'registerCandidate']);
    Route::get('/register/client', [RegisteredUserController::class, 'showClientRegistrationForm'])->name('register.client');
    Route::post('/register/client', [RegisteredUserController::class, 'registerClient']);
});

// Standard Laravel authentication routes (login, logout, etc.)
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // --- MAIN DASHBOARD REDIRECTOR (STANDARDIZED) ---
    // This now uses hasRole() for ALL checks, ensuring consistency.
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('Superadmin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        } elseif ($user->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        } elseif ($user->hasRole('candidate')) {
            return redirect()->route('candidate.dashboard');
        }
        // Fallback for any user without a specific role dashboard
        return redirect('/');
    })->name('dashboard');

    // --- GENERIC AUTH ROUTES (e.g., Profile) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/apply/{job}', [JobController::class, 'apply'])->middleware('role:candidate')->name('jobs.apply');

    // --- ADMIN PANEL ROUTES ---
    Route::middleware('role:Superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/applications', [AdminController::class, 'listApplications'])->name('applications.index');
        // --- ADD THESE TWO NEW ROUTES ---
        Route::post('/applications/{application}/approve', [AdminController::class, 'approveApplication'])->name('applications.approve');
        Route::post('/applications/{application}/reject', [AdminController::class, 'rejectApplication'])->name('applications.reject');
        Route::get('/jobs/pending', [AdminController::class, 'pendingJobs'])->name('jobs.pending');
        Route::post('/jobs/{job}/approve', [AdminController::class, 'approveJob'])->name('jobs.approve');
        Route::post('/jobs/{job}/reject', [AdminController::class, 'rejectJob'])->name('jobs.reject');
        Route::get('/jobs/{job}/manage', [AdminController::class, 'manageJobExclusions'])->name('jobs.manage');
        Route::post('/jobs/{job}/exclusions', [AdminController::class, 'updateJobExclusions'])->name('jobs.exclusions.update');
    });

    // --- CLIENT (EMPLOYER) ROUTES ---
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
        Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
        // --- ADD THIS NEW ROUTE ---
        Route::get('/jobs/{job}/applicants', [ClientController::class, 'showApplicants'])->name('jobs.applicants');
        // --- ADD THESE NEW HIRING ROUTES ---

        // Route for the client to reject the applicant
        Route::post('/applications/{application}/reject', [ClientController::class, 'rejectApplicant'])->name('applications.reject');
        
        // Route to SHOW the form to schedule an interview
        Route::get('/applications/{application}/interview', [ClientController::class, 'showInterviewForm'])->name('applications.interview.show');
        
        // Route to SAVE the interview date
        Route::post('/applications/{application}/interview', [ClientController::class, 'scheduleInterview'])->name('applications.interview.schedule');
    });

    // --- PARTNER (AGENCY) ROUTES ---
    Route::middleware('role:partner')->prefix('partner')->name('partner.')->group(function () {
        Route::get('/dashboard', [PartnerController::class, 'index'])->name('dashboard');
        Route::get('/applications', [PartnerController::class, 'applications'])->name('applications');
        Route::get('/jobs', [PartnerController::class, 'jobs'])->name('jobs');
        // --- ADD THIS NEW ROUTE ---
    Route::get('/jobs/{job}/apply', [PartnerController::class, 'showApplyForm'])->name('jobs.showApplyForm');
    // --- ADD THIS NEW ROUTE ---
    Route::post('/jobs/{job}/submit', [PartnerController::class, 'submitApplication'])->name('jobs.submit');
        Route::get('/earnings', [PartnerController::class, 'earnings'])->name('earnings');
        Route::get('/candidates/create', [PartnerController::class, 'createCandidate'])->name('candidates.create');
        Route::post('/candidates', [PartnerController::class, 'storeCandidate'])->name('candidates.store');
        Route::get('/candidates', [PartnerController::class, 'listCandidates'])->name('candidates.index');
    });
    
    // --- CANDIDATE ROUTES ---
    Route::middleware('role:candidate')->prefix('candidate')->name('candidate.')->group(function () {
        Route::get('/dashboard', [CandidateController::class, 'index'])->name('dashboard');
        Route::get('/applications', [CandidateController::class, 'applications'])->name('applications');
    });
});

