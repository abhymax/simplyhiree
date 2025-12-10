<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubAdminController; // <-- Added this
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PartnerProfileController;
use App\Http\Controllers\CandidateProfileController;
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
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show'); 

// Guest-only registration routes
Route::middleware('guest')->group(function () {
    Route::get('/register/partner', [RegisteredUserController::class, 'showPartnerRegistrationForm'])->name('register.partner');
    Route::post('/register/partner', [RegisteredUserController::class, 'registerPartner']);
    Route::get('/register/candidate', [RegisteredUserController::class, 'showCandidateRegistrationForm'])->name('register.candidate');
    Route::post('/register/candidate', [RegisteredUserController::class, 'registerCandidate']);
    Route::get('/register/client', [RegisteredUserController::class, 'showClientRegistrationForm'])->name('register.client');
    Route::post('/register/client', [RegisteredUserController::class, 'registerClient']);
});

// Standard Laravel authentication routes
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'status.check'])->group(function () {
    
    // --- MAIN DASHBOARD REDIRECTOR ---
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Superadmin AND Manager go to the Admin Dashboard
        if ($user->hasRole('Superadmin') || $user->hasRole('Manager')) {
            return redirect()->route('admin.dashboard');
        } 
        elseif ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        } 
        elseif ($user->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        } 
        elseif ($user->hasRole('candidate')) {
            return redirect()->route('candidate.dashboard');
        }
        return redirect('/');
    })->name('dashboard');

    // --- GENERIC AUTH ROUTES ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/apply/{job}', [JobController::class, 'apply'])->middleware('role:candidate')->name('jobs.apply');


    // ==========================================
    //       ADMIN PANEL ROUTES (Shared)
    // ==========================================
    // Accessible by both Superadmin AND Manager
    Route::middleware(['role:Superadmin|Manager'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // --- IMPERSONATION (Manager/Admin Feature) ---
        Route::get('/impersonate/stop', [SubAdminController::class, 'stopImpersonating'])->name('impersonate.stop');
        Route::get('/impersonate/{user}', [SubAdminController::class, 'impersonate'])->name('impersonate.start');


        // --- SUB-ADMIN MANAGEMENT (Superadmin Only) ---
        Route::middleware(['role:Superadmin'])->group(function() {
            Route::resource('sub_admins', SubAdminController::class);
            
            // General User Management (System-wide)
            Route::get('/users', [AdminController::class, 'listUsers'])->name('users.index');
            Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status.update');
            Route::patch('/users/{user}/credentials', [AdminController::class, 'updateUserCredentials'])->name('users.credentials.update');
            
            // Job Approvals/Creation (Defaulting to Superadmin only for safety)
            Route::get('/jobs/create', [AdminController::class, 'createJob'])->name('jobs.create');
            Route::post('/jobs', [AdminController::class, 'storeJob'])->name('jobs.store');
            Route::post('/jobs/{job}/approve', [AdminController::class, 'approveJob'])->name('jobs.approve');
            Route::post('/jobs/{job}/reject', [AdminController::class, 'rejectJob'])->name('jobs.reject');
            Route::get('/jobs/{job}/manage', [AdminController::class, 'manageJobExclusions'])->name('jobs.manage');
            Route::post('/jobs/{job}/exclusions', [AdminController::class, 'updateJobExclusions'])->name('jobs.exclusions.update');
        });


        // --- CLIENT MANAGEMENT (Permission: manage_clients) ---
        Route::middleware(['can:manage_clients'])->group(function() {
            Route::get('/clients', [AdminController::class, 'listClients'])->name('clients.index');
            Route::get('/clients/create', [AdminController::class, 'createClient'])->name('clients.create');
            Route::post('/clients', [AdminController::class, 'storeClient'])->name('clients.store');
            Route::get('/clients/{user}/edit', [AdminController::class, 'editClient'])->name('clients.edit');
            Route::patch('/clients/{user}', [AdminController::class, 'updateClient'])->name('clients.update');
        });


        // --- PARTNER DATA (Permission: view_partner_data) ---
        Route::middleware(['can:view_partner_data'])->group(function() {
            Route::get('/partners', [AdminController::class, 'listPartners'])->name('partners.index');
            Route::get('/partners/create', [AdminController::class, 'createPartner'])->name('partners.create');
            Route::post('/partners', [AdminController::class, 'storePartner'])->name('partners.store');
            Route::get('/partners/{user}', [AdminController::class, 'showPartner'])->name('partners.show');
        });


        // --- APPLICATION DATA (Permission: view_application_data) ---
        Route::middleware(['can:view_application_data'])->group(function() {
            Route::get('/applications', [AdminController::class, 'listApplications'])->name('applications.index');
            // Allow Managers to Approve/Reject? 
            // If they can view data, they might need to process it. Adding actions here:
            Route::post('/applications/{application}/approve', [AdminController::class, 'approveApplication'])->name('applications.approve');
            Route::post('/applications/{application}/reject', [AdminController::class, 'rejectApplication'])->name('applications.reject');
        });


        // --- PENDING JOBS (Permission: view_pending_jobs) ---
        Route::middleware(['can:view_pending_jobs'])->group(function() {
            Route::get('/jobs/pending', [AdminController::class, 'pendingJobs'])->name('jobs.pending');
        });


        // --- BILLING DATA (Permission: view_billing_data) ---
        Route::middleware(['can:view_billing_data'])->group(function() {
            Route::get('/billing', [AdminController::class, 'billingReport'])->name('billing.index');
            Route::patch('/applications/{application}/mark-paid', [AdminController::class, 'markAsPaid'])->name('applications.markPaid');
            Route::get('/reports/jobs', [AdminController::class, 'jobReport'])->name('reports.jobs');
        });

    });


    // --- CLIENT (EMPLOYER) ROUTES ---
    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
        Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('/billing', [ClientController::class, 'billing'])->name('billing');
        Route::get('/jobs/{job}/applicants', [ClientController::class, 'showApplicants'])->name('jobs.applicants');
    
        // Hiring Workflow
        Route::post('/applications/{application}/reject', [ClientController::class, 'rejectApplicant'])->name('applications.reject');
        Route::get('/applications/{application}/interview', [ClientController::class, 'showInterviewForm'])->name('applications.interview.show');
        Route::post('/applications/{application}/interview', [ClientController::class, 'scheduleInterview'])->name('applications.interview.schedule');
        Route::post('/applications/{application}/interview-appeared', [ClientController::class, 'markAsAppeared'])->name('applications.interview.appeared');
        Route::post('/applications/{application}/interview-noshow', [ClientController::class, 'markAsNoShow'])->name('applications.interview.noshow');
        Route::get('/applications/{application}/select', [ClientController::class, 'showSelectForm'])->name('applications.select.show');
        Route::post('/applications/{application}/select', [ClientController::class, 'storeSelection'])->name('applications.select.store');
    });


    // --- PARTNER (AGENCY) ROUTES ---
    Route::middleware(['role:partner'])->prefix('partner')->name('partner.')->group(function () {
        Route::get('/dashboard', [PartnerController::class, 'index'])->name('dashboard');
        Route::get('/applications', [PartnerController::class, 'applications'])->name('applications');
        Route::get('/earnings', [PartnerController::class, 'earnings'])->name('earnings');
        
        // Jobs
        Route::get('/jobs', [PartnerController::class, 'jobs'])->name('jobs');
        Route::get('/jobs/{job}', [PartnerController::class, 'showJob'])->name('jobs.show'); 
        Route::get('/jobs/{job}/apply', [PartnerController::class, 'showApplyForm'])->name('jobs.showApplyForm');
        Route::post('/jobs/{job}/submit', [PartnerController::class, 'submitApplication'])->name('jobs.submit');

        // Candidate Management (Mobile First Workflow)
        Route::get('/candidates/check', [PartnerController::class, 'checkCandidateMobile'])->name('candidates.check'); 
        Route::post('/candidates/check', [PartnerController::class, 'verifyCandidateMobile'])->name('candidates.verify'); 
        Route::get('/candidates/create', [PartnerController::class, 'createCandidate'])->name('candidates.create'); 
        Route::post('/candidates', [PartnerController::class, 'storeCandidate'])->name('candidates.store'); 
        
        Route::get('/candidates', [PartnerController::class, 'listCandidates'])->name('candidates.index');
        Route::get('/candidates/{candidate}/edit', [PartnerController::class, 'editCandidate'])->name('candidates.edit');
        Route::patch('/candidates/{candidate}', [PartnerController::class, 'updateCandidate'])->name('candidates.update');
        
        // Profile
        Route::get('/profile/business', [PartnerProfileController::class, 'edit'])->name('profile.business');
        Route::patch('/profile/business', [PartnerProfileController::class, 'update'])->name('profile.update');
    });
    

    // --- CANDIDATE ROUTES ---
    Route::middleware('role:candidate')->prefix('candidate')->name('candidate.')->group(function () {
        Route::get('/dashboard', [CandidateController::class, 'index'])->name('dashboard');
        Route::get('/applications', [CandidateController::class, 'applications'])->name('applications');
        
        // Profile
        Route::get('/profile/edit', [CandidateProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile/update', [CandidateProfileController::class, 'update'])->name('profile.update');
    });
});