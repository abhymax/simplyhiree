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
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show'); // <-- ADD THIS LINE

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
        // --- ADD THIS NEW ROUTE FOR USER MANAGEMENT ---
        Route::get('/users', [AdminController::class, 'listUsers'])->name('users.index');
        // --- ADD THESE TWO NEW ROUTES ---
        Route::get('/clients', [AdminController::class, 'listClients'])->name('clients.index');
        // --- ADD THIS ROUTE TO SHOW THE EDIT FORM ---
        Route::get('/clients/{user}/edit', [AdminController::class, 'editClient'])->name('clients.edit');
        
        // --- ADD THIS ROUTE TO SAVE THE FORM ---
        Route::patch('/clients/{user}', [AdminController::class, 'updateClient'])->name('clients.update');
        Route::get('/partners', [AdminController::class, 'listPartners'])->name('partners.index');
        Route::get('/applications', [AdminController::class, 'listApplications'])->name('applications.index');
        // --- ADD THESE TWO NEW ROUTES ---
        Route::post('/applications/{application}/approve', [AdminController::class, 'approveApplication'])->name('applications.approve');
        Route::post('/applications/{application}/reject', [AdminController::class, 'rejectApplication'])->name('applications.reject');
        Route::get('/jobs/pending', [AdminController::class, 'pendingJobs'])->name('jobs.pending');
        Route::post('/jobs/{job}/approve', [AdminController::class, 'approveJob'])->name('jobs.approve');
        Route::post('/jobs/{job}/reject', [AdminController::class, 'rejectJob'])->name('jobs.reject');
        Route::get('/jobs/{job}/manage', [AdminController::class, 'manageJobExclusions'])->name('jobs.manage');
        Route::post('/jobs/{job}/exclusions', [AdminController::class, 'updateJobExclusions'])->name('jobs.exclusions.update');
        // --- *** ADD THIS NEW ROUTE *** ---
        Route::get('/billing', [AdminController::class, 'billingReport'])->name('billing.index');
        // --- *** ADD THIS NEW ROUTE FOR THE JOB REPORT *** ---
        Route::get('/reports/jobs', [AdminController::class, 'jobReport'])->name('reports.jobs');
        Route::patch('/applications/{application}/mark-paid', [AdminController::class, 'markAsPaid'])->name('applications.markPaid');
    });

    // --- CLIENT (EMPLOYER) ROUTES ---
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
        Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('/billing', [ClientController::class, 'billing'])->name('billing'); // <-- ADD THIS
        // --- ADD THIS NEW ROUTE ---
        Route::get('/jobs/{job}/applicants', [ClientController::class, 'showApplicants'])->name('jobs.applicants');
    
        
        // --- ADD THESE NEW HIRING ROUTES ---

        // Route for the client to reject the applicant
        Route::post('/applications/{application}/reject', [ClientController::class, 'rejectApplicant'])->name('applications.reject');
        
        // Route to SHOW the form to schedule an interview
        Route::get('/applications/{application}/interview', [ClientController::class, 'showInterviewForm'])->name('applications.interview.show');
        
        // Route to SAVE the interview date
        Route::post('/applications/{application}/interview', [ClientController::class, 'scheduleInterview'])->name('applications.interview.schedule');
        // --- *** ADD THESE NEW ROUTES *** ---
        
        // Routes for post-interview status
        Route::post('/applications/{application}/interview-appeared', [ClientController::class, 'markAsAppeared'])->name('applications.interview.appeared');
        Route::post('/applications/{application}/interview-noshow', [ClientController::class, 'markAsNoShow'])->name('applications.interview.noshow');

        // Routes for final selection
        Route::get('/applications/{application}/select', [ClientController::class, 'showSelectForm'])->name('applications.select.show');
        Route::post('/applications/{application}/select', [ClientController::class, 'storeSelection'])->name('applications.select.store');
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
        // --- ADD THESE NEW ROUTES ---
        Route::get('/candidates/{candidate}/edit', [PartnerController::class, 'editCandidate'])->name('candidates.edit');
        Route::patch('/candidates/{candidate}', [PartnerController::class, 'updateCandidate'])->name('candidates.update');
    });
    
    // --- CANDIDATE ROUTES ---
    Route::middleware('role:candidate')->prefix('candidate')->name('candidate.')->group(function () {
        Route::get('/dashboard', [CandidateController::class, 'index'])->name('dashboard');
        Route::get('/applications', [CandidateController::class, 'applications'])->name('applications');
        
        // --- ADD THESE NEW ROUTES ---
        Route::get('/profile/edit', [App\Http\Controllers\CandidateProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile/update', [App\Http\Controllers\CandidateProfileController::class, 'update'])->name('profile.update');
    });
});

